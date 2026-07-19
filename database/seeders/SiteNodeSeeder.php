<?php

namespace Database\Seeders;

use App\Models\SiteNode;
use Illuminate\Database\Seeder;

class SiteNodeSeeder extends Seeder
{
    public function run(): void
    {
        // ==========================================
        // CẤU TRÚC CỔNG THÔNG TIN THƯ VIỆN VTTU
        // Theo tài liệu mô tả chính thức
        // ==========================================

        // ── 1. TRANG CHỦ ──
        SiteNode::updateOrCreate(
            ['node_code' => 'home'],
            [
                'node_name' => 'Trang chủ',
                'display_name' => 'Trang chủ',
                'description' => 'Trang đầu tiên khi truy cập Cổng thông tin Thư viện VTTU.',
                'masterpage' => 'home',
                'icon' => 'fas fa-home',
                'display_type' => 'menu',
                'language' => 'vi',
                'sort_order' => 1,
                'is_active' => true,
                'access_type' => 'public',
                'allow_guest' => true,
            ]
        );

        // ── 2. GIỚI THIỆU ──
        $aboutRoot = SiteNode::updateOrCreate(
            ['node_code' => 'gioi-thieu'],
            [
                'node_name' => 'Giới thiệu',
                'display_name' => 'Giới thiệu',
                'description' => 'Tổng quan về Trường/Thư viện và định hướng phát triển.',
                'masterpage' => 'about',
                'icon' => 'fas fa-info-circle',
                'display_type' => 'menu',
                'language' => 'vi',
                'sort_order' => 2,
                'is_active' => true,
                'access_type' => 'public',
                'allow_guest' => true,
                'content' => implode("\n", [
                    '<div class="space-y-6">',
                    '<p class="text-lg leading-relaxed text-slate-700"><strong>Trường Đại học Võ Trường Toản</strong> trực thuộc Bộ Giáo dục và Đào tạo. Nhiệm vụ chính của Nhà trường là đào tạo nguồn nhân lực chất lượng cao có trí tuệ, bản lĩnh, sáng tạo cho vùng đồng bằng sông Cửu Long nói riêng và cả nước nói chung. Trường Đại học Võ Trường Toản hướng đến xây dựng mẫu hình trường Đại học hiện đại, mang tầm vóc quốc tế về quy mô và chất lượng.</p>',
                    '<p class="text-lg leading-relaxed text-slate-700"><strong>Thư viện Đại học Võ Trường Toản</strong> trực thuộc Trường Đại học Võ Trường Toản được thành lập và chính thức đi vào hoạt động theo Quyết định số 114/QĐ-ĐHQT-TC ngày 5 tháng 10 năm 2009 của Hiệu trưởng Trường Đại học Võ Trường Toản. Cho đến nay Thư viện trường đã có một bề dày hơn <strong>15 năm</strong> phục vụ cho nhu cầu nghiên cứu, giảng dạy, học tập, tham khảo của giảng viên, nhân viên và sinh viên của Nhà trường.</p>',
                    '<p class="text-lg leading-relaxed text-slate-700">Thư viện đang tiếp tục đẩy nhanh tốc độ phát triển đáp ứng nhu cầu ngày càng cao và đa dạng của người dùng, đang áp dụng các hình thức dịch vụ tiên tiến, hiện đại nhằm tạo điều kiện thuận lợi nhất cho người dùng tiếp cận tài liệu để học tập, nghiên cứu.</p>',
                    '</div>'
                ]),
            ]
        );

        SiteNode::updateOrCreate(
            ['node_code' => 'gioi-thieu-chung'],
            [
                'parent_id' => $aboutRoot->id,
                'node_name' => 'Giới thiệu chung',
                'display_name' => 'Giới thiệu chung',
                'description' => 'Tổng quan về Trường/Thư viện và định hướng phát triển.',
                'masterpage' => 'gioi-thieu-chung',
                'icon' => 'fas fa-circle-info',
                'display_type' => 'page',
                'language' => 'vi',
                'sort_order' => 1,
                'is_active' => true,
                'access_type' => 'public',
                'allow_guest' => true,
                'content' => $aboutRoot->content,
            ]
        );

        SiteNode::updateOrCreate(
            ['node_code' => 'chuc-nang-nhiem-vu'],
            [
                'parent_id' => $aboutRoot->id,
                'node_name' => 'Chức năng nhiệm vụ',
                'display_name' => 'Chức năng nhiệm vụ',
                'description' => 'Vai trò và nhiệm vụ của Thư viện trong Nhà trường.',
                'masterpage' => 'chuc-nang-nhiem-vu',
                'icon' => 'fas fa-bullseye',
                'display_type' => 'page',
                'language' => 'vi',
                'sort_order' => 2,
                'is_active' => true,
                'access_type' => 'public',
                'allow_guest' => true,
                'content' => implode("\n", [
                    '<h3 class="text-2xl font-black text-slate-900 mb-6">CHỨC NĂNG</h3>',
                    '<p class="bg-slate-50 p-6 rounded-2xl border-l-4 border-blue-600 text-slate-700 italic mb-10">Thư viện Đại học Võ Trường Toản có chức năng phục vụ hoạt động giảng dạy, học tập, đào tạo, nghiên cứu khoa học, triển khai ứng dụng tiến bộ khoa học công nghệ và quản lý của nhà trường thông qua việc sử dụng, khai thác các loại tài liệu có trong Thư viện.</p>',
                    '<h3 class="text-2xl font-black text-slate-900 mb-6 uppercase">Nhiệm vụ</h3>',
                    '<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-12">',
                    '<div class="p-6 bg-white rounded-2xl border border-slate-100 shadow-sm h-full"><div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center text-blue-600 mb-4"><i class="fas fa-plus"></i></div><h4 class="font-bold mb-2">Công tác bổ sung phát triển nguồn học liệu</h4><p class="text-sm text-slate-500">Xây dựng và phát triển kho tài liệu đa dạng phù hợp với chương trình đào tạo.</p></div>',
                    '<div class="p-6 bg-white rounded-2xl border border-slate-100 shadow-sm h-full"><div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center text-indigo-600 mb-4"><i class="fas fa-archive"></i></div><h4 class="font-bold mb-2">Công tác xử lý, lưu trữ, quản lý, phục vụ tài liệu</h4><p class="text-sm text-slate-500">Phân loại, bảo quản và phục vụ bạn đọc một cách khoa học nhất.</p></div>',
                    '<div class="p-6 bg-white rounded-2xl border border-slate-100 shadow-sm h-full"><div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center text-emerald-600 mb-4"><i class="fas fa-tasks"></i></div><h4 class="font-bold mb-2">Công tác khác</h4><p class="text-sm text-slate-500">Lập kế hoạch mua sắm, kiểm kê và tổ chức các hoạt động thư viện.</p></div>',
                    '</div>',
                    '<ul class="space-y-4 text-slate-700">',
                    '<li><i class="fas fa-check text-blue-500 mr-2"></i>Xây dựng quy hoạch, kế hoạch hoạt động dài hạn và ngắn hạn của Thư viện.</li>',
                    '<li><i class="fas fa-check text-blue-500 mr-2"></i>Bổ sung phát triển nguồn tư liệu đáp ứng nhu cầu giảng dạy, học tập, nghiên cứu khoa học.</li>',
                    '<li><i class="fas fa-check text-blue-500 mr-2"></i>Tổ chức phục vụ, hướng dẫn khai thác, tìm kiếm, sử dụng hiệu quả nguồn tin.</li>',
                    '</ul>'
                ]),
            ]
        );

        SiteNode::updateOrCreate(
            ['node_code' => 'noi-quy-thu-vien'],
            [
                'parent_id' => $aboutRoot->id,
                'node_name' => 'Nội quy Thư viện',
                'display_name' => 'Nội quy Thư viện',
                'description' => 'Quy định sử dụng không gian và tài nguyên Thư viện.',
                'masterpage' => 'noi-quy-thu-vien',
                'icon' => 'fas fa-scale-balanced',
                'display_type' => 'page',
                'language' => 'vi',
                'sort_order' => 3,
                'is_active' => true,
                'access_type' => 'public',
                'allow_guest' => true,
                'content' => implode("\n", [
                    '<div class="bg-amber-50 border-l-4 border-amber-400 p-6 mb-10 rounded-r-2xl"><h4 class="font-bold text-amber-900 mb-2">Chương I: NHỮNG QUY ĐỊNH CHUNG</h4><p class="text-amber-800 text-sm italic">Điều 1 & 2: Đối tượng phục vụ là cán bộ, giảng viên, sinh viên và bạn đọc có thẻ hợp lệ.</p></div>',
                    '<div class="space-y-12">',
                    '<div><h4 class="text-xl font-black text-slate-900 mb-6 flex items-center gap-2"><span class="w-8 h-8 bg-slate-900 text-white rounded-full flex items-center justify-center text-xs">03</span> Quy định về thẻ Thư viện</h4>',
                    '<ul class="grid grid-cols-1 md:grid-cols-2 gap-4">',
                    '<li class="bg-white p-4 rounded-xl border border-slate-100 flex items-start gap-3 shadow-sm"><i class="fas fa-id-card text-blue-500 mt-1"></i><span class="text-sm">Thẻ cán bộ/sinh viên được dùng làm thẻ thư viện.</span></li>',
                    '<li class="bg-white p-4 rounded-xl border border-slate-100 flex items-start gap-3 shadow-sm"><i class="fas fa-user-shield text-blue-500 mt-1"></i><span class="text-sm">Bạn đọc phải xuất trình thẻ khi vào thư viện.</span></li>',
                    '<li class="bg-white p-4 rounded-xl border border-slate-100 flex items-start gap-3 shadow-sm"><i class="fas fa-ban text-red-500 mt-1"></i><span class="text-sm">Không được cho mượn hoặc dùng thẻ của người khác.</span></li>',
                    '<li class="bg-white p-4 rounded-xl border border-slate-100 flex items-start gap-3 shadow-sm"><i class="fas fa-exclamation-triangle text-amber-500 mt-1"></i><span class="text-sm">Mất thẻ phải báo ngay cho CB Thư viện để khóa.</span></li>',
                    '</ul></div>',
                    '<div class="overflow-x-auto mt-12 bg-white rounded-3xl border border-slate-100 shadow-xl">',
                    '<table class="w-full text-left border-collapse">',
                    '<thead class="bg-slate-900 text-white"><tr><th class="p-6">Điều / Khoản</th><th class="p-6">Hành vi vi phạm</th><th class="p-6">Hình thức xử lý</th></tr></thead>',
                    '<tbody class="divide-y divide-slate-100">',
                    '<tr><td class="p-6 font-bold">Điều 4.2</td><td class="p-6 text-sm">Trả sách trễ hạn</td><td class="p-6"><span class="bg-red-50 text-red-600 px-3 py-1 rounded-full text-xs font-bold">10.000đ/quyển/ngày</span></td></tr>',
                    '<tr><td class="p-6 font-bold">Điều 4.2</td><td class="p-6 text-sm">Làm mất tài liệu</td><td class="p-6 text-sm">Mua trả lại sách gốc hoặc Đền (Giá bìa x 3) + Phí xử lý</td></tr>',
                    '<tr><td class="p-6 font-bold">Điều 7.2</td><td class="p-6 text-sm">Sử dụng điện thoại gây ồn</td><td class="p-6 text-sm">Lần 2: Phạt 20k. Lần 3: Phạt 50k & ngưng phục vụ 03 tháng</td></tr>',
                    '</tbody></table></div>',
                    '</div>'
                ]),
            ]
        );

        SiteNode::updateOrCreate(
            ['node_code' => 'thoi-gian-phuc-vu'],
            [
                'parent_id' => $aboutRoot->id,
                'node_name' => 'Thời gian phục vụ',
                'display_name' => 'Thời gian phục vụ',
                'description' => 'Lịch mở cửa và thời gian phục vụ bạn đọc.',
                'masterpage' => 'thoi-gian-phuc-vu',
                'icon' => 'fas fa-clock',
                'display_type' => 'page',
                'language' => 'vi',
                'sort_order' => 4,
                'is_active' => true,
                'access_type' => 'public',
                'allow_guest' => true,
                'content' => implode("\n", [
                    '<div class="flex flex-col md:flex-row items-center gap-12 bg-slate-50 p-10 rounded-[3rem] border border-slate-200">',
                    '<div class="flex-shrink-0 w-full md:w-1/2 rounded-3xl overflow-hidden shadow-2xl transition-transform hover:scale-105 duration-500">',
                    '<img src="/assets/images/thoi-gian-phuc-vu.png" onerror="this.src=\'https://img.freepik.com/free-vector/modern-office-open-hours-sign-concept_23-2148545161.jpg\'" alt="Thời gian phục vụ" class="w-full h-full object-cover">',
                    '</div>',
                    '<div class="w-full md:w-1/2 space-y-8">',
                    '<div><h3 class="text-3xl font-black text-slate-900 mb-2">GIỜ MỞ CỬA</h3><div class="w-20 h-1 bg-blue-600 rounded-full"></div></div>',
                    '<div class="space-y-6">',
                    '<div class="flex items-center gap-6 p-6 bg-white rounded-2xl shadow-sm border border-slate-100"><div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 text-2xl"><i class="fas fa-calendar-alt"></i></div><div><div class="text-sm font-black text-slate-400 uppercase tracking-widest">Thứ Hai - Thứ Bảy</div><div class="text-2xl font-black text-slate-900">08:00 - 17:00</div></div></div>',
                    '<div class="flex items-center gap-6 p-6 bg-red-50 rounded-2xl shadow-sm border border-red-100"><div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center text-red-600 text-2xl"><i class="fas fa-times-circle"></i></div><div><div class="text-sm font-black text-red-400 uppercase tracking-widest">Chủ nhật & Ngày Lễ</div><div class="text-2xl font-black text-red-600 uppercase">Không hoạt động</div></div></div>',
                    '</div>',
                    '<p class="text-sm text-slate-400 italic font-medium">* Lưu ý: Lịch phục vụ có thể thay đổi tùy theo kế hoạch đào tạo của Nhà trường.</p>',
                    '</div>',
                    '</div>'
                ]),
            ]
        );

        SiteNode::updateOrCreate(
            ['node_code' => 'ban-do-website-thu-vien'],
            [
                'parent_id' => $aboutRoot->id,
                'node_name' => 'Bản đồ Website Thư viện',
                'display_name' => 'Bản đồ Website Thư viện',
                'description' => 'Sơ đồ điều hướng các khu vực chức năng của website.',
                'masterpage' => 'ban-do-website',
                'icon' => 'fas fa-sitemap',
                'display_type' => 'page',
                'language' => 'vi',
                'sort_order' => 5,
                'is_active' => true,
                'access_type' => 'public',
                'allow_guest' => true,
                'content' => implode("\n", [
                    '<div class="p-12 bg-slate-900 rounded-[3rem] text-white overflow-hidden relative">',
                    '<div class="absolute top-0 right-0 w-64 h-64 bg-blue-500/10 blur-[100px] rounded-full"></div>',
                    '<h3 class="text-3xl font-black mb-12 text-center">SƠ ĐỒ ĐIỀU HƯỚNG</h3>',
                    '<div class="grid grid-cols-2 md:grid-cols-3 gap-8 relative z-10 text-center">',
                    '<div class="p-6 bg-white/5 rounded-2xl border border-white/10 backdrop-blur hover:bg-white/10 transition-all"><i class="fas fa-home text-blue-400 text-2xl mb-4"></i><div class="font-bold">Trang chủ</div></div>',
                    '<div class="p-6 bg-white/5 rounded-2xl border border-white/10 backdrop-blur hover:bg-white/10 transition-all"><i class="fas fa-info-circle text-indigo-400 text-2xl mb-4"></i><div class="font-bold">Giới thiệu</div></div>',
                    '<div class="p-6 bg-white/5 rounded-2xl border border-white/10 backdrop-blur hover:bg-white/10 transition-all"><i class="fas fa-concierge-bell text-emerald-400 text-2xl mb-4"></i><div class="font-bold">Dịch vụ</div></div>',
                    '<div class="p-6 bg-white/5 rounded-2xl border border-white/10 backdrop-blur hover:bg-white/10 transition-all"><i class="fas fa-book-reader text-amber-400 text-2xl mb-4"></i><div class="font-bold">Tài liệu số</div></div>',
                    '<div class="p-6 bg-white/5 rounded-2xl border border-white/10 backdrop-blur hover:bg-white/10 transition-all"><i class="fas fa-newspaper text-cyan-400 text-2xl mb-4"></i><div class="font-bold">Tin tức</div></div>',
                    '<div class="p-6 bg-white/5 rounded-2xl border border-white/10 backdrop-blur hover:bg-white/10 transition-all"><i class="fas fa-envelope text-rose-400 text-2xl mb-4"></i><div class="font-bold">Liên hệ</div></div>',
                    '</div>',
                    '</div>'
                ]),
            ]
        );

        // ── 3. HƯỚNG DẪN ──
        $helpRoot = SiteNode::updateOrCreate(
            ['node_code' => 'huong-dan'],
            [
                'node_name' => 'Hướng dẫn',
                'display_name' => 'Hướng dẫn sử dụng',
                'description' => 'Cẩm nang hướng dẫn sử dụng các dịch vụ và tiện ích tại Thư viện số VTTU.',
                'masterpage' => 'help',
                'icon' => 'fas fa-book-open-reader',
                'display_type' => 'menu',
                'language' => 'vi',
                'sort_order' => 3,
                'is_active' => true,
                'access_type' => 'public',
                'allow_guest' => true,
                'content' => '<p class="text-lg text-slate-600 font-medium text-center">Chào mừng bạn đến với trung tâm hỗ trợ Thư viện số VTTU. Vui lòng chọn danh mục hướng dẫn bên dưới để bắt đầu khai thác tài nguyên thư viện một cách hiệu quả nhất.</p>',
            ]
        );

        // Tạo 8 node con
        $subPages = [
            ['cam-nang-hdsd', 'Cẩm nang HDSD Thư viện', 'fas fa-book'],
            ['tai-app-mobile', 'Tải ứng dụng trên điện thoại', 'fas fa-mobile-screen'],
            ['dang-nhap-tai-khoan', 'Đăng nhập tài khoản', 'fas fa-sign-in-alt'],
            ['doi-mat-khau', 'Đổi mật khẩu tài khoản', 'fas fa-key'],
            ['tra-cuu-tai-lieu-giay', 'Tra cứu tài liệu giấy', 'fas fa-book-journal-whills'],
            ['tra-cuu-tai-lieu-so', 'Tra cứu tài liệu số', 'fas fa-file-pdf'],
            ['muon-truoc-gia-han', 'Mượn trước - Gia hạn', 'fas fa-calendar-check'],
            ['de-nghi-bo-sung', 'Đề nghị bổ sung tài liệu', 'fas fa-plus-circle'],
            ['khao-sat-y-kien', 'Khảo sát ý kiến bạn đọc', 'fas fa-poll'],
        ];

        foreach ($subPages as $index => $page) {
            SiteNode::updateOrCreate(
                ['node_code' => $page[0]],
                [
                    'parent_id' => $helpRoot->id,
                    'node_name' => $page[1],
                    'display_name' => $page[1],
                    'description' => 'Hướng dẫn chi tiết về ' . $page[1],
                    'masterpage' => 'about',
                    'icon' => $page[2],
                    'display_type' => 'page',
                    'language' => 'vi',
                    'sort_order' => $index + 1,
                    'is_active' => true,
                    'access_type' => 'public',
                    'allow_guest' => true,
                    'content' => '<div class="p-12 text-center bg-slate-50 rounded-[3rem] border-2 border-dashed border-slate-200"><i class="' . $page[2] . ' text-5xl text-slate-300 mb-4"></i><h3 class="text-xl font-bold text-slate-400">Nội dung đang được cập nhật...</h3></div>',
                ]
            );
        }
        // ── 4. TÀI NGUYÊN ──
        $resourceRoot = SiteNode::updateOrCreate(
            ['node_code' => 'tai-nguyen'],
            [
                'node_name' => 'Tài nguyên',
                'display_name' => 'Tài nguyên',
                'description' => 'Tài liệu giấy, tài liệu số, cơ sở dữ liệu, tài nguyên giáo dục mở.',
                'masterpage' => 'about',
                'icon' => 'fas fa-layer-group',
                'display_type' => 'menu',
                'language' => 'vi',
                'sort_order' => 4,
                'is_active' => true,
                'access_type' => 'public',
                'allow_guest' => true,
                'content' => '<p class="text-lg text-slate-600 font-medium text-center">Khám phá kho tài nguyên phong phú của Thư viện Đại học Võ Trường Toản.</p>',
            ]
        );

        $resourceChildren = [
            ['tai-lieu-giay', 'Tài liệu giấy', 'fas fa-book', 'Kho sách in, giáo trình, tài liệu tham khảo tại Thư viện.', 'about'],
            ['tai-lieu-so', 'Tài liệu số', 'fas fa-file-pdf', 'Sách điện tử, giáo trình số, báo cáo nghiên cứu.', 'digital-resources'],
            ['co-so-du-lieu', 'Cơ sở dữ liệu', 'fas fa-database', 'CSDL do nhà trường mua quyền truy cập và CSDL mở.', 'about'],
            ['tai-nguyen-giao-duc-mo', 'Tài nguyên giáo dục mở', 'fas fa-globe', 'Nguồn tài nguyên giáo dục mở (OER) phục vụ học tập và nghiên cứu.', 'oer'],
        ];

        foreach ($resourceChildren as $index => $child) {
            SiteNode::updateOrCreate(
                ['node_code' => $child[0]],
                [
                    'parent_id' => $resourceRoot->id,
                    'node_name' => $child[1],
                    'display_name' => $child[1],
                    'description' => $child[3],
                    'masterpage' => $child[4],
                    'icon' => $child[2],
                    'display_type' => 'page',
                    'language' => 'vi',
                    'sort_order' => $index + 1,
                    'is_active' => true,
                    'access_type' => 'public',
                    'allow_guest' => true,
                    'content' => '<div class="p-12 text-center bg-slate-50 rounded-[3rem] border-2 border-dashed border-slate-200"><i class="' . $child[2] . ' text-5xl text-slate-300 mb-4"></i><h3 class="text-xl font-bold text-slate-400">Nội dung đang được cập nhật...</h3></div>',
                ]
            );
        }

        // ── 5. TIN TỨC ──
        $newsRoot = SiteNode::updateOrCreate(
            ['node_code' => 'tin-tuc'],
            [
                'node_name' => 'Tin tức',
                'display_name' => 'Tin tức',
                'description' => 'Cập nhật thông báo, tin tức, sự kiện và hoạt động mới nhất của Thư viện.',
                'masterpage' => 'about',
                'icon' => 'fas fa-newspaper',
                'display_type' => 'menu',
                'language' => 'vi',
                'sort_order' => 5,
                'is_active' => true,
                'access_type' => 'public',
                'allow_guest' => true,
                'content' => '<p class="text-lg text-slate-600 font-medium text-center">Thông báo và tin tức mới nhất từ Thư viện Đại học Võ Trường Toản. 2</p>',
            ]
        );

        $newsChildren = [
            ['thong-bao', 'Thông báo', 'fas fa-bullhorn', 'tin-tuc/chuyen-muc/thong-bao'],
            ['tin-tuc-su-kien', 'Tin tức sự kiện', 'fas fa-calendar-alt', 'tin-tuc/chuyen-muc/tin-tuc-su-kien'],
            ['video', 'Video', 'fas fa-video', 'tin-tuc/chuyen-muc/video'],
            ['gioi-thieu-sach', 'Giới thiệu sách', 'fas fa-book-open', 'tin-tuc/chuyen-muc/gioi-thieu-sach'],
        ];

        foreach ($newsChildren as $index => $child) {
            SiteNode::updateOrCreate(
                ['node_code' => $child[0]],
                [
                    'parent_id' => $newsRoot->id,
                    'node_name' => $child[1],
                    'display_name' => $child[1],
                    'description' => 'Danh mục ' . $child[1],
                    'masterpage' => 'about',
                    'icon' => $child[2],
                    'redirect_to' => $child[3],
                    'display_type' => 'page',
                    'language' => 'vi',
                    'sort_order' => $index + 1,
                    'is_active' => true,
                    'access_type' => 'public',
                    'allow_guest' => true,
                    'content' => '<div class="p-4 text-center bg-card border border-border rounded"><i class="' . $child[2] . ' text-2xl text-muted-foreground mb-2"></i><h3 class="text-sm font-bold text-foreground">Nội dung đang được cập nhật...</h3></div>',
                ]
            );
        }

        // ── 6. TRA CỨU OPAC (Liên kết ngoài) ──
        SiteNode::updateOrCreate(
            ['node_code' => 'tra-cuu-opac'],
            [
                'node_name' => 'Tra cứu OPAC',
                'display_name' => 'Tra cứu OPAC',
                'description' => 'Hệ thống tra cứu trực tuyến tìm kiếm tài liệu có trong Thư viện.',
                'url' => 'http://opac.vttu.edu.vn',
                'icon' => 'fas fa-search',
                'display_type' => 'menu',
                'target' => '_blank',
                'language' => 'vi',
                'sort_order' => 6,
                'is_active' => true,
                'access_type' => 'public',
                'allow_guest' => true,
            ]
        );

        // ── SIDEBAR (Thanh bên phải trang chủ) ──
        $sidebarItems = [
            ['sb-tai-lieu-dien-tu', 'Tài liệu điện tử', 'fas fa-tablet-alt', 'http://opac.vttu.edu.vn'],
            ['sb-co-so-du-lieu', 'Cơ sở dữ liệu', 'fas fa-database', '/page/co-so-du-lieu'],
            ['sb-hoc-lieu-vttu', 'Học liệu VTTU', 'fas fa-graduation-cap', 'http://hoclieu.vttu.edu.vn/'],
            ['sb-video-bai-giang', 'Video bài giảng', 'fas fa-video', 'http://bgtt.vttu.edu.vn/BaiGiangTrucTuyen/Pages/FrmDangNhap.jsp'],
            ['sb-de-nghi-bo-sung', 'Đề nghị bổ sung tài liệu', 'fas fa-plus-circle', '/page/de-nghi-bo-sung'],
            ['sb-khao-sat', 'Khảo sát ý kiến bạn đọc', 'fas fa-poll', '#'],
        ];

        foreach ($sidebarItems as $index => $item) {
            SiteNode::updateOrCreate(
                ['node_code' => $item[0]],
                [
                    'node_name' => $item[1],
                    'display_name' => $item[1],
                    'url' => $item[3],
                    'icon' => $item[2],
                    'display_type' => 'sidebar',
                    'target' => str_starts_with($item[3], 'http') ? '_blank' : '',
                    'language' => 'vi',
                    'sort_order' => $index + 1,
                    'is_active' => true,
                    'access_type' => 'public',
                    'allow_guest' => true,
                ]
            );
        }

        // ── FOOTER (Thông tin liên hệ) ──
        SiteNode::updateOrCreate(
            ['node_code' => 'footer-lien-he'],
            [
                'node_name' => 'Thông tin liên hệ',
                'display_name' => 'Thông tin liên hệ',
                'description' => 'Thư viện Trường Đại học Võ Trường Toản',
                'icon' => 'fas fa-map-marker-alt',
                'display_type' => 'footer',
                'language' => 'vi',
                'sort_order' => 1,
                'is_active' => true,
                'access_type' => 'public',
                'allow_guest' => true,
                'content' => implode("\n", [
                    '<p><i class="fas fa-map-marker-alt mr-2 text-blue-400"></i>Quốc Lộ 1A, xã Tân Phú Thạnh, huyện Châu Thành A, tỉnh Hậu Giang</p>',
                    '<p><i class="fas fa-phone mr-2 text-blue-400"></i>02933504345</p>',
                    '<p><i class="fas fa-envelope mr-2 text-blue-400"></i>Mailthuvien@vttu.edu.vn</p>',
                ]),
            ]
        );

        // Vô hiệu hóa các node cũ không còn trong sơ đồ mới
        SiteNode::whereIn('node_code', ['services', 'contact', 'digital-library'])
            ->update(['is_active' => false]);
    }
}
