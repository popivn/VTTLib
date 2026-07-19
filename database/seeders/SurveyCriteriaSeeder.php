<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SurveyCriterion;

class SurveyCriteriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultCriteria = [
            [
                'code' => 'service',
                'name' => 'Chất lượng Dịch vụ Thư viện',
                'description' => 'Đánh giá chung về tốc độ và chất lượng phục vụ của các dịch vụ thư viện',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'code' => 'resource',
                'name' => 'Nguồn Tài nguyên & Học liệu',
                'description' => 'Đánh giá mức độ phong phú và cập nhật của sách, tài liệu số và cơ sở dữ liệu',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'code' => 'facility',
                'name' => 'Cơ sở vật chất & Không gian đọc',
                'description' => 'Đánh giá không gian, ánh sáng, máy tính, mạng wifi và trang thiết bị',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'code' => 'staff',
                'name' => 'Thái độ phục vụ của Cán bộ',
                'description' => 'Đánh giá sự nhiệt tình, chuyên nghiệp và thân thiện của nhân viên thư viện',
                'sort_order' => 4,
                'is_active' => true,
            ],
        ];

        foreach ($defaultCriteria as $item) {
            SurveyCriterion::updateOrCreate(
                ['code' => $item['code']],
                $item
            );
        }

        if (isset($this->command)) {
            $this->command->info('Đã nạp danh mục Tiêu chí khảo sát thành công!');
        }
    }
}
