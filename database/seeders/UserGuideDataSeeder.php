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
            'tra-cuu-tai-lieu-so' => [
                'title' => 'HƯỚNG DẪN TRA CỨU TÀI LIỆU SỐ & HỌC LIỆU TRỰC TUYẾN',
                'condition_title' => 'Học liệu và tài liệu số hóa trực tuyến:',
                'condition_desc' => 'Bạn đọc có thể dễ dàng truy cập và đọc trực tuyến hàng nghìn đầu sách số, giáo trình điện tử, tài nguyên y khoa và báo cáo khoa học mọi lúc, mọi nơi.',
                'steps_title' => 'Các bước tra cứu và sử dụng tài liệu số:',
                'steps' => [
                    [
                        'step_number' => 1,
                        'title' => 'Truy cập kho Tài nguyên số',
                        'content' => 'Vào mục <a href="/tai-lieu-so">Tài nguyên số</a> trên thanh trình đơn chính để mở kho tài nguyên số hóa của trường.'
                    ],
                    [
                        'step_number' => 2,
                        'title' => 'Tìm kiếm và Lọc tài liệu',
                        'content' => 'Sử dụng thanh công cụ tìm kiếm, phân loại theo chuyên mục (như Y khoa, Sản khoa...) hoặc sắp xếp theo danh mục (Mới nhất, Xem nhiều nhất, Tải nhiều nhất).'
                    ],
                    [
                        'step_number' => 3,
                        'title' => 'Mở giao diện Đọc trực tuyến',
                        'content' => 'Bấm vào tài liệu mong muốn, hệ thống sẽ mở trình đọc tài liệu số tích hợp, hỗ trợ chế độ lật trang, phóng to thu nhỏ và ghi chú trực tuyến.'
                    ],
                    [
                        'step_number' => 4,
                        'title' => 'Tải tài liệu (nếu được phép)',
                        'content' => 'Với những tài liệu cho phép tải về, bạn đọc có thể nhấn nút "Tải xuống" để lưu tài liệu định dạng PDF về thiết bị của mình.'
                    ]
                ],
                'video_title' => 'Video hướng dẫn khai thác tài liệu số:',
                'video_source' => 'url',
                'embed_video_url' => '',
                'video_url' => '',
            ],
            'muon-truoc-gia-han' => [
                'title' => 'HƯỚNG DẪN MƯỢN TRƯỚC VÀ GIA HẠN TÀI LIỆU',
                'condition_title' => 'Thông tin dịch vụ mượn trước & gia hạn:',
                'condition_desc' => 'Trong quá trình học tập và nghiên cứu tại trường, bạn đọc có thể đặt mượn tài liệu, theo dõi thông tin quá hạn sách, gia hạn sách trực tuyến... thông qua tài khoản thư viện cá nhân.',
                'steps_title' => '1. Đăng ký mượn trước tài liệu:',
                'steps' => [
                    [
                        'step_number' => 1,
                        'title' => 'Tra cứu tài liệu',
                        'content' => 'Bạn đọc đăng nhập tài khoản thư viện và thực hiện tra cứu tài liệu cần đăng ký mượn trước tại trang <a href="/opac">Tra cứu OPAC</a>.'
                    ],
                    [
                        'step_number' => 2,
                        'title' => 'Chọn đăng ký mượn',
                        'content' => 'Tại màn hình chi tiết của cuốn sách mong muốn, nhấp vào nút <strong>Đăng ký mượn ngay</strong>.'
                    ],
                    [
                        'step_number' => 3,
                        'title' => 'Xác nhận đăng ký',
                        'content' => 'Màn hình hiển thị kết quả đăng ký mượn trước tài liệu của bạn đọc, nhấn nút <strong>OK</strong> để xác nhận đăng ký.'
                    ],
                    [
                        'step_number' => 4,
                        'title' => 'Kiểm tra trạng thái xử lý',
                        'content' => 'Bạn đọc truy cập vào <a href="/my-profile?tab=history">Hồ sơ cá nhân</a> để xem danh sách sách đã đăng ký mượn và tình trạng xử lý (Đang chờ duyệt, Từ chối, Sẵn sàng nhận sách).'
                    ]
                ],
                'section2_title' => '2. Gia hạn tài liệu trực tuyến:',
                'section2_steps' => [
                    [
                        'step_number' => 1,
                        'title' => 'Đăng nhập & Vào hồ sơ cá nhân',
                        'content' => 'Bạn đọc đăng nhập tài khoản thư viện, nhấp chọn <a href="/my-profile?tab=info">Hồ sơ cá nhân</a> (Thông tin độc giả).'
                    ],
                    [
                        'step_number' => 2,
                        'title' => 'Chọn tab Sách đang mượn',
                        'content' => 'Chọn tab <a href="/my-profile?tab=history">Lịch sử mượn sách</a>, hệ thống hiển thị tất cả các tài liệu bạn đọc đang mượn, ngày mượn, hạn trả và số lần đã gia hạn.'
                    ],
                    [
                        'step_number' => 3,
                        'title' => 'Yêu cầu gia hạn',
                        'content' => 'Chọn nút <strong>Gia hạn</strong> bên cạnh tài liệu tương ứng để thực hiện gia hạn thời gian mượn trực tuyến.'
                    ]
                ],
                'video_title' => 'Video hướng dẫn thao tác mượn trước & gia hạn:',
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
