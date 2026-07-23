<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sidebar;

class OerSidebarSeeder extends Seeder
{
    /**
     * Run the database seeds for Admin OER Sidebar management item.
     */
    public function run(): void
    {
        $parent = Sidebar::where('name', 'like', '%Quản lý nội dung%')->first();

        if ($parent) {
            Sidebar::updateOrCreate(
                ['route' => 'admin.oer.index'],
                [
                    'name' => 'Tài nguyên giáo dục mở',
                    'icon' => 'fas fa-book-open',
                    'parent_id' => $parent->id,
                    'order' => 6,
                    'is_active' => true,
                ]
            );
        }
    }
}
