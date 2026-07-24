<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SiteNode;

class UserGuideDataSeeder extends Seeder
{
    /**
     * Run the database seeds for User Guide Node Data.
     */
    public function run(): void
    {
        $helpRoot = SiteNode::where('node_code', 'huong-dan')->first();

        if (!$helpRoot) {
            return;
        }

        $guides = [
            'dang-nhap-tai-khoan' => [
                'title' => 'HƯỚNG DẪN ĐĂNG NHẬP TÀI KHOẢN THƯ VIỆN ĐIỆN TỬ',
                'condition_title' => 'Điều kiện để đăng nhập thành công:',
                'condition_desc' => 'Bạn đọc là học sinh, sinh viên, cán bộ, giảng viên, nhân viên (CBGV) đang học tập và làm việc tại Trường Đại học Võ Trường Toản.',
                'steps_title' => 'Các bước đăng nhập tài khoản:',
                'steps' => [
                    [
                        'step_number' => 1,
                        'title' => 'Truy cập cổng thông tin Thư viện',
                        'content' => 'Mở trình duyệt và truy cập vào cổng thông tin Thư viện điện tử VTTU.'
                    ],
                    [
                        'step_number' => 2,
                        'title' => 'Nhấn nút Đăng nhập',
                        'content' => 'Nhấp vào nút <strong>Đăng nhập</strong> ở góc trên bên phải màn hình.'
                    ],
                    [
                        'step_number' => 3,
                        'title' => 'Nhập thông tin tài khoản',
                        'content' => 'Nhập Mã bạn đọc (Mã SV / Mã CBGV) và Mật khẩu được cấp.'
                    ]
                ],
                'video_title' => 'Bạn đọc vui lòng xem video hướng dẫn dưới đây:',
                'video_source' => 'url',
                'embed_video_url' => 'https://www.canva.com/design/DAGAyk7W3b8/OtC9Ngs-WllWWEM06hRnwA/watch?embed',
                'video_url' => 'https://www.canva.com/design/DAGAyk7W3b8/OtC9Ngs-WllWWEM06hRnwA/watch?embed',
            ],
            'tra-cuu-tai-lieu-giay' => [
                'title' => 'HƯỚNG DẪN TRA CỨU TÀI LIỆU IN / GIẤY (OPAC)',
                'condition_title' => 'Phạm vi tra cứu:',
                'condition_desc' => 'Hệ thống tra cứu trực tuyến toàn bộ sách, báo, tạp chí, luận văn lưu trữ tại thư viện trường.',
                'steps_title' => 'Các bước tra cứu sách giấy:',
                'steps' => [
                    [
                        'step_number' => 1,
                        'title' => 'Truy cập cổng tra cứu OPAC',
                        'content' => 'Vào mục <a href="/opac">Tra cứu OPAC</a> trên thanh trình đơn chính của Cổng thông tin Thư viện.'
                    ],
                    [
                        'step_number' => 2,
                        'title' => 'Nhập từ khóa tìm kiếm',
                        'content' => 'Nhập <strong>Tên sách</strong>, <strong>Tên tác giả</strong>, hoặc <strong>Chủ đề</strong> của cuốn sách cần tìm vào ô tìm kiếm và nhấn Tìm kiếm.'
                    ],
                    [
                        'step_number' => 3,
                        'title' => 'Xác định Vị trí kệ sách',
                        'content' => 'Bấm vào chi tiết cuốn sách, xem thông tin <strong>Vị trí lưu trữ</strong> (ví dụ: Phòng mượn giáo trình) và ghi lại <strong>Ký hiệu xếp giá</strong> ghi trên gáy sách.'
                    ],
                    [
                        'step_number' => 4,
                        'title' => 'Tìm sách trên kệ tại Thư viện',
                        'content' => 'Đến khu vực kệ tương ứng tại Thư viện và tìm cuốn sách theo đúng ký hiệu xếp giá đã ghi.'
                    ]
                ],
                'video_title' => 'Video hướng dẫn tra cứu OPAC:',
                'video_source' => 'url',
                'embed_video_url' => '',
                'video_url' => '',
            ],
            'doi-mat-khau' => [
                'title' => 'HƯỚNG DẪN ĐỔI MẬT KHẨU TÀI KHOẢN',
                'condition_title' => 'Lưu ý bảo mật:',
                'condition_desc' => 'Khuyên dùng mật khẩu mạnh bao gồm chữ hoa, chữ thường, số và ký tự đặc biệt.',
                'steps_title' => 'Các bước thực hiện đổi mật khẩu:',
                'steps' => [
                    [
                        'step_number' => 1,
                        'title' => 'Đăng nhập tài khoản',
                        'content' => 'Đăng nhập vào hệ thống Thư viện điện tử bằng mã bạn đọc và mật khẩu hiện tại của bạn.'
                    ],
                    [
                        'step_number' => 2,
                        'title' => 'Truy cập trang đổi mật khẩu',
                        'content' => 'Nhấp chọn mục "Đổi mật khẩu" tại trang Hồ sơ cá nhân (hoặc nhấp trực tiếp vào <a href="/my-profile?tab=password">nút liên kết nhanh</a>).'
                    ],
                    [
                        'step_number' => 3,
                        'title' => 'Nhập thông tin mật khẩu mới',
                        'content' => 'Nhập đầy đủ mật khẩu hiện tại, mật khẩu mới mong muốn và gõ lại mật khẩu mới vào ô xác nhận.'
                    ],
                    [
                        'step_number' => 4,
                        'title' => 'Xác nhận thay đổi',
                        'content' => 'Bấm nút "Cập nhật mật khẩu". Hệ thống sẽ kiểm tra và thông báo đổi mật khẩu thành công ngay lập tức.'
                    ]
                ],
                'video_title' => 'Video hướng dẫn thao tác:',
                'video_source' => 'url',
                'embed_video_url' => '',
                'video_url' => '',
            ],
        ];

        foreach ($guides as $code => $data) {
            $node = SiteNode::where('node_code', $code)->first();
            if ($node) {
                $node->update([
                    'content_json' => json_encode($data, JSON_UNESCAPED_UNICODE)
                ]);
            }
        }
    }
}
