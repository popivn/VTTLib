<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DigitalFolder;

class DigitalFolderSeeder extends Seeder
{
    /**
     * Run the database seeds to order and rename digital folders.
     */
    public function run(): void
    {
        // 1. Root folders order
        $rootFolders = [
            [
                'folder_code' => 'BG',
                'folder_name' => '1. Bài giảng VTTU',
                'sort_order' => 1,
            ],
            [
                'folder_code' => '772',
                'folder_name' => '2. Y học - Sức khỏe',
                'sort_order' => 2,
            ],
            [
                'folder_code' => '774',
                'folder_name' => '3. Kinh tế - Luật',
                'sort_order' => 3,
            ],
            [
                'folder_code' => '775',
                'folder_name' => '4. Ngoại ngữ - Tin học',
                'sort_order' => 4,
            ],
            [
                'folder_code' => '5-CHINH-TRI-XA-HOI',
                'folder_name' => '5. Chính trị - Xã hội',
                'sort_order' => 5,
            ],
            [
                'folder_code' => 'KLTN',
                'folder_name' => '6. Khóa luận tốt nghiệp',
                'sort_order' => 6,
            ],
            [
                'folder_code' => 'TAI-LIEU-CHUYEN-NGANH',
                'folder_name' => '7. Tài liệu chuyên ngành',
                'sort_order' => 7,
            ],
            [
                'folder_code' => 'TMKT',
                'folder_name' => '8. Folder Test',
                'sort_order' => 8,
            ],
        ];

        foreach ($rootFolders as $data) {
            DigitalFolder::where('folder_code', $data['folder_code'])
                ->orWhere('folder_name', 'like', '%' . str_replace(['1. ', '2. ', '3. ', '4. ', '5. ', '6. ', '7. ', '8. '], '', $data['folder_name']) . '%')
                ->where(function($q) {
                    $q->whereNull('parent_id')->orWhere('parent_id', 0);
                })
                ->update([
                    'folder_name' => $data['folder_name'],
                    'sort_order' => $data['sort_order'],
                ]);
        }

        // 2. Child folders under Khóa luận tốt nghiệp (parent_id = 790 / KLTN)
        $parentKLTN = DigitalFolder::where('folder_code', 'KLTN')
            ->orWhere('folder_name', 'like', '%Khóa luận tốt nghiệp%')
            ->first();

        if ($parentKLTN) {
            $children = [
                ['folder_code' => 'KY', 'folder_name' => '6.1. Khoa Y', 'sort_order' => 1],
                ['folder_code' => 'KD', 'folder_name' => '6.2. Khoa Dược', 'sort_order' => 2],
                ['folder_code' => 'KKTL', 'folder_name' => '6.3. Khoa Kinh tế - Luật', 'sort_order' => 3],
                ['folder_code' => 'klcntt', 'folder_name' => '6.4. Công nghệ thông tin', 'sort_order' => 4],
            ];

            foreach ($children as $c) {
                DigitalFolder::where('folder_code', $c['folder_code'])
                    ->update([
                        'parent_id' => $parentKLTN->id,
                        'folder_name' => $c['folder_name'],
                        'sort_order' => $c['sort_order'],
                    ]);
            }
        }
    }
}
