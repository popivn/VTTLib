<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PatronSurveySidebarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Tìm hoặc tạo menu cha "Quản lý nội dung"
        $parentMenu = DB::table('sidebars')
            ->where('name', 'Content Management')
            ->orWhere('name_vi', 'Quản lý nội dung')
            ->first();

        if (!$parentMenu) {
            $parentMenuId = DB::table('sidebars')->insertGetId([
                'name' => 'Content Management',
                'name_vi' => 'Quản lý nội dung',
                'name_en' => 'Content Management',
                'route_name' => null,
                'icon' => 'fas fa-newspaper',
                'order' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $parentMenuId = $parentMenu->id;
        }

        // 2. Kiểm tra xem mục sidebar "Khảo sát ý kiến" đã tồn tại chưa
        $existingMenu = DB::table('sidebars')
            ->where('route_name', 'admin.patron-surveys.index')
            ->first();

        if (!$existingMenu) {
            $sidebarId = DB::table('sidebars')->insertGetId([
                'parent_id' => $parentMenuId,
                'name' => 'Patron Surveys',
                'name_vi' => 'Khảo sát ý kiến',
                'name_en' => 'Patron Surveys',
                'route_name' => 'admin.patron-surveys.index',
                'icon' => 'fas fa-poll',
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
                    'name_vi' => 'Khảo sát ý kiến',
                    'name_en' => 'Patron Surveys',
                    'route_name' => 'admin.patron-surveys.index',
                    'icon' => 'fas fa-poll',
                    'updated_at' => now(),
                ]);
        }

        // 3. Phân quyền cho các vai trò quản trị (admin, root)
        $roles = DB::table('roles')
            ->whereIn('name', ['admin', 'root'])
            ->get();

        foreach ($roles as $role) {
            $exists = DB::table('role_sidebars')
                ->where('role_id', $role->id)
                ->where('sidebar_id', $sidebarId)
                ->exists();

            if (!$exists) {
                DB::table('role_sidebars')->insert([
                    'role_id' => $role->id,
                    'sidebar_id' => $sidebarId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 4. Phân quyền trực tiếp cho gán vai trò người dùng (user_role_sidebars)
        foreach ($roles as $role) {
            $roleUsers = DB::table('role_user')
                ->where('role_id', $role->id)
                ->get();

            foreach ($roleUsers as $roleUser) {
                $exists = DB::table('user_role_sidebars')
                    ->where('role_user_id', $roleUser->id)
                    ->where('sidebar_id', $sidebarId)
                    ->exists();

                if (!$exists) {
                    DB::table('user_role_sidebars')->insert([
                        'role_user_id' => $roleUser->id,
                        'sidebar_id' => $sidebarId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        if (isset($this->command)) {
            $this->command->info('Đã thêm sidebar Khảo sát ý kiến thành công!');
        }
    }
}
