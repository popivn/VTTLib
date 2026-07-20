<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CurriculumMajor;

class CurriculumMajorsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $majors = [
            [
                'title' => 'RĂNG - HÀM - MẶT',
                'image' => '/storage/pages/POST-YK-1-1-480x360.jpg',
                'description' => 'Ngành Bác sĩ Răng – Hàm – Mặt (RHM) được đánh giá là ngành học có nhiều triển vọng nghề nghiệp trong tương lai và cơ hội làm việc trong ngành này vô cùng rộng mở. Đào tạo Bác sĩ RHM là đào tạo những người có y đức; có kiến thức và kỹ năng nghề nghiệp cơ bản về y học và nha khoa, để xác định, đề xuất và tham gia giải quyết các vấn đề trong dự phòng, chẩn đoán và điều trị các bệnh răng hàm mặt cho cá nhân và cộng đồng; có khả năng nghiên cứu khoa học và tự học nâng cao trình độ, đáp ứng nhu cầu bảo vệ, chăm sóc sức khoẻ răng miệng cho nhân dân.',
                'link_url' => 'https://www.canva.com/design/DAGLR01FImY/YzxRUiJ0EmvhTQxLGZHEIQ/view',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Y KHOA',
                'image' => '/storage/pages/NGANHYKhoa-480x360.jpg',
                'description' => 'Ngành Y khoa là ngành học đào tạo bác sĩ đa khoa, tập trung vào kỹ năng khám, chẩn đoán, điều trị và hướng dẫn dự phòng các bệnh lý phổ biến tại bệnh viện và cộng đồng, hướng dẫn phục hồi sức khỏe và kê thuốc cho bệnh nhân. Đây là ngành học mũi nhọn của Nhà trường, nơi hội tụ đội ngũ giảng viên là các y, bác sĩ, chuyên gia đầu ngành, dày dặn kinh nghiệm.',
                'link_url' => 'https://www.canva.com/design/DAGLR01FImY/YzxRUiJ0EmvhTQxLGZHEIQ/view',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'DƯỢC HỌC',
                'image' => '/storage/pages/NGANHDuocHoc-480x360.jpg',
                'description' => 'Ngành Dược học là ngành học đào tạo Dược sĩ Đại học có phẩm chất đạo đức tốt, có kiến thức khoa học cơ bản và y dược học cơ sở vững chắc, có kiến thức chuyên môn về Dược học để thực hiện các hoạt động chuyên môn Dược trong lĩnh vực quản lý, sản xuất, kinh doanh, kiểm nghiệm và cung ứng thuốc.',
                'link_url' => 'https://vttu.edu.vn/duoc-hoc/',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'title' => 'ĐIỀU DƯỠNG',
                'image' => '/storage/pages/Chon_KeToan-480x360.jpg',
                'description' => 'Ngành Điều dưỡng đào tạo Cử nhân Điều dưỡng có kiến thức y học cơ bản, kỹ năng chăm sóc người bệnh chuyên nghiệp, có y đức và tinh thần trách nhiệm cao trong công tác chăm sóc, bảo vệ và nâng cao sức khỏe nhân dân tại các cơ sở y tế.',
                'link_url' => 'https://vttu.edu.vn/dieu-duong/',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'title' => 'KỸ THUẬT XÉT NGHIỆM Y HỌC',
                'image' => '/storage/pages/Chon_TCNH-2.jpg',
                'description' => 'Ngành Kỹ thuật Xét nghiệm Y học đào tạo cử nhân xét nghiệm có khả năng thực hiện các kỹ thuật xét nghiệm y học hiện đại (Huyết học, Sinh hóa, Vi sinh, Giải phẫu bệnh...), phục vụ hiệu quả cho công tác chẩn đoán và điều trị bệnh.',
                'link_url' => 'https://vttu.edu.vn/xet-nghiem-y-hoc/',
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'title' => 'CÔNG NGHỆ THÔNG TIN',
                'image' => '/storage/pages/CNTT-KS-480x360.jpg',
                'description' => 'Ngành Công nghệ thông tin đào tạo Cử nhân CNTT đáp ứng nhu cầu nhân lực chuyển đổi số, làm chủ các kỹ năng phát triển phần mềm, quản trị mạng, hệ thống thông tin và ứng dụng trí tuệ nhân tạo trong thực tiễn.',
                'link_url' => 'https://vttu.edu.vn/cong-nghe-thong-tin/',
                'sort_order' => 6,
                'is_active' => true,
            ],
            [
                'title' => 'QUẢN TRỊ KINH DOANH',
                'image' => '/storage/pages/ChonQTKD-3-480x360.jpg',
                'description' => 'Ngành Quản trị kinh doanh đào tạo cử nhân quản trị có tư duy chiến lược, năng lực điều hành doanh nghiệp, hoạch định dự án, marketing và quản trị nguồn nhân lực trong nền kinh tế thị trường hội nhập.',
                'link_url' => 'https://vttu.edu.vn/quan-tri-kinh-doanh/',
                'sort_order' => 7,
                'is_active' => true,
            ],
        ];

        foreach ($majors as $m) {
            CurriculumMajor::updateOrCreate(
                ['title' => $m['title']],
                $m
            );
        }
    }
}
