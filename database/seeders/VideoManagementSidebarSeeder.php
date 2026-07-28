<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VideoManagementSidebarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Tìm hoặc tạo menu cha "Quản lý nội dung" (Content Management)
        $parentMenu = DB::table('sidebars')
            ->whereNull('parent_id')
            ->where(function($q) {
                $q->where('name', 'Content Management')
                  ->orWhere('name_vi', 'Quản lý nội dung');
            })
            ->first();

        if (!$parentMenu) {
            $parentMenuId = DB::table('sidebars')->insertGetId([
                'parent_id' => null,
                'name' => 'Content Management',
                'name_vi' => 'Quản lý nội dung',
                'name_en' => 'Content Management',
                'route_name' => null,
                'icon' => '<i class="fas fa-edit"></i>',
                'order' => 50,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $parentMenuId = $parentMenu->id;
        }

        // 2. Tạo hoặc cập nhật menu con "Quản lý Video"
        $existingMenu = DB::table('sidebars')
            ->where('route_name', 'admin.videos.index')
            ->first();

        if (!$existingMenu) {
            $sidebarId = DB::table('sidebars')->insertGetId([
                'parent_id' => $parentMenuId,
                'name' => 'Video Management',
                'name_vi' => 'Quản lý Video',
                'name_en' => 'Video Management',
                'route_name' => 'admin.videos.index',
                'icon' => '<i class="fas fa-video"></i>',
                'order' => 25,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $sidebarId = $existingMenu->id;
            DB::table('sidebars')
                ->where('id', $sidebarId)
                ->update([
                    'parent_id' => $parentMenuId,
                    'name' => 'Video Management',
                    'name_vi' => 'Quản lý Video',
                    'name_en' => 'Video Management',
                    'icon' => '<i class="fas fa-video"></i>',
                    'order' => 25,
                    'updated_at' => now(),
                ]);
        }

        // 3. Gán quyền cho các vai trò admin và root
        $roles = DB::table('roles')->whereIn('name', ['admin', 'root'])->get();
        
        foreach ($roles as $role) {
            // Cập nhật role template (role_sidebars)
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

            // Cập nhật quyền cho user thực tế (user_role_sidebars)
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

            // Đảm bảo parent menu cũng được gán quyền
            $parentRoleSidebarExists = DB::table('role_sidebars')
                ->where('role_id', $role->id)
                ->where('sidebar_id', $parentMenuId)
                ->exists();
            
            if (!$parentRoleSidebarExists) {
                DB::table('role_sidebars')->insert([
                    'role_id' => $role->id,
                    'sidebar_id' => $parentMenuId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Gán parent menu cho user thực tế
            foreach ($roleUsers as $ru) {
                $parentUserSidebarExists = DB::table('user_role_sidebars')
                    ->where('role_user_id', $ru->id)
                    ->where('sidebar_id', $parentMenuId)
                    ->exists();
                
                if (!$parentUserSidebarExists) {
                    DB::table('user_role_sidebars')->insert([
                        'role_user_id' => $ru->id,
                        'sidebar_id' => $parentMenuId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        $this->command->info('Video Management sidebar created/updated successfully.');
    }
}