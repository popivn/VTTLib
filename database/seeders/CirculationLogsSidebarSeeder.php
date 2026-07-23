<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CirculationLogsSidebarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Parent: Circulation (ID = 15)
        $parent = DB::table('sidebars')->where('name', 'Circulation')->first();
        $parentId = $parent ? $parent->id : 15;

        // Check if sidebar entry already exists
        $existing = DB::table('sidebars')->where('route_name', 'admin.circulation.logs')->first();

        if (!$existing) {
            $sidebarId = DB::table('sidebars')->insertGetId([
                'parent_id' => $parentId,
                'name' => 'Circulation Activity Logs',
                'name_vi' => 'Nhật ký lưu thông',
                'name_en' => 'Circulation Activity Logs',
                'route_name' => 'admin.circulation.logs',
                'icon' => '<i class="fas fa-history"></i>',
                'order' => 10,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $sidebarId = $existing->id;
            DB::table('sidebars')->where('id', $sidebarId)->update([
                'parent_id' => $parentId,
                'name_vi' => 'Nhật ký lưu thông',
                'name_en' => 'Circulation Activity Logs',
                'icon' => '<i class="fas fa-history"></i>',
                'order' => 10,
                'updated_at' => now(),
            ]);
        }

        // Grant access to admin and root roles
        $roles = DB::table('roles')->whereIn('name', ['admin', 'root'])->get();
        
        foreach ($roles as $role) {
            // 1. Update role template (role_sidebars)
            $roleSidebarExists = DB::table('role_sidebars')
                ->where('role_id', $role->id)
                ->where('sidebar_id', $sidebarId)
                ->exists();
            
            if (!$roleSidebarExists) {
                DB::table('role_sidebars')->insert([
                    'role_id' => $role->id,
                    'sidebar_id' => $sidebarId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // 2. Update actual user assignments (user_role_sidebars)
            $roleUsers = DB::table('role_user')->where('role_id', $role->id)->get();
            foreach ($roleUsers as $ru) {
                $userSidebarExists = DB::table('user_role_sidebars')
                    ->where('role_user_id', $ru->id)
                    ->where('sidebar_id', $sidebarId)
                    ->exists();
                
                if (!$userSidebarExists) {
                    DB::table('user_role_sidebars')->insert([
                        'role_user_id' => $ru->id,
                        'sidebar_id' => $sidebarId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
