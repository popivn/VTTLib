@php
    $guideJson = json_decode($node->content_json ?? '{}', true) ?: [];
    $guideTitle = $guideJson['title'] ?? 'HƯỚNG DẪN CÀI ĐẶT VÀ SỬ DỤNG THƯ VIỆN ĐIỆN TỬ TRÊN ĐIỆN THOẠI';
    $guideCondTitle = $guideJson['condition_title'] ?? 'Yêu cầu thiết bị:';
    $guideCondDesc = $guideJson['condition_desc'] ?? 'Thiết bị di động chạy hệ điều hành iOS (App Store) hoặc Android (Google Play Store).';
    $guideVideoTitle = $guideJson['video_title'] ?? 'Video hướng dẫn cài đặt app:';
    $uploadedVideoUrl = $guideJson['uploaded_video_url'] ?? '';
    $embedVideoUrl = $guideJson['embed_video_url'] ?? ($guideJson['video_url'] ?? '');
    $videoSource = $guideJson['video_source'] ?? (!empty($uploadedVideoUrl) ? 'file' : (!empty($embedVideoUrl) ? 'url' : 'none'));

    $guideVideoUrl = ($videoSource === 'none') ? '' : (($videoSource === 'file') ? $uploadedVideoUrl : $embedVideoUrl);

    if (str_contains($guideVideoUrl, 'youtube.com/watch?v=')) {
        $guideVideoUrl = str_replace('youtube.com/watch?v=', 'youtube.com/embed/', $guideVideoUrl);
    } elseif (str_contains($guideVideoUrl, 'youtu.be/')) {
        $guideVideoUrl = str_replace('youtu.be/', 'youtube.com/embed/', $guideVideoUrl);
    }

    $isLocalFile = str_contains($guideVideoUrl, '/storage/') || str_ends_with($guideVideoUrl, '.mp4') || str_ends_with($guideVideoUrl, '.webm') || str_ends_with($guideVideoUrl, '.mov');
@endphp

<div class="space-y-4 animate-fade-in">
    <!-- Section Header -->
    <div class="border-b border-border pb-3">
        <h2 class="text-xl md:text-2xl font-black text-foreground tracking-tight leading-tight uppercase">
            {{ $guideTitle }}
        </h2>
        <div class="w-16 h-1 bg-vttu-red mt-2 rounded"></div>
    </div>

    <!-- Intro Card (Grid Layout) -->
    <div class="grid md:grid-cols-12 gap-3 items-center bg-card rounded-md p-4 border border-border shadow-sm">
        <div class="md:col-span-10 space-y-2">
            <p class="text-foreground leading-relaxed text-sm font-medium">
                {{ $guideCondDesc ?: 'App Thư viện cho phép bạn đọc tra cứu tài liệu và đọc sách điện tử (tài liệu số hóa) trong Thư viện mọi lúc - mọi nơi. Truy cập nhanh và xem ngay trên di động bằng công cụ xem tài liệu trực tuyến hỗ trợ hầu hết các định dạng tài liệu.' }}
            </p>
            <p class="text-muted-foreground text-xs leading-relaxed">
                Đồng thời, có nhiều công cụ tiện ích giúp bạn đọc linh hoạt đọc tài liệu: Xoay ngang, dọc thiết bị; phóng to, thu nhỏ nội dung tài liệu; ghi chú thông tin (note) ngay trên tài liệu.
            </p>
        </div>
        <div class="md:col-span-2 flex justify-center">
            <div class="relative w-12 h-12 bg-vttu-red/10 rounded flex items-center justify-center text-vttu-red shadow-inner">
                <i data-lucide="smartphone" class="w-6 h-6"></i>
                <div class="absolute -top-1 -right-1 px-1 py-0.5 bg-vttu-yellow rounded-sm flex items-center justify-center text-vttu-dark font-bold text-[8px] animate-bounce shadow">
                    New
                </div>
            </div>
        </div>
    </div>

    <!-- Video Section (If uploaded or embedded) -->
    @if(!empty($guideVideoUrl))
        <div class="space-y-2 pt-3 border-t border-border">
            <h4 class="font-bold text-foreground text-sm flex items-center gap-2">
                <i data-lucide="play-circle" class="w-4 h-4 text-vttu-red"></i> {{ $guideVideoTitle }}
            </h4>
            <div class="relative w-full aspect-video rounded-md overflow-hidden border border-border shadow-sm bg-black">
                @if($isLocalFile)
                    <video src="{{ $guideVideoUrl }}" controls class="w-full h-full object-contain"></video>
                @else
                    <iframe 
                        loading="lazy" 
                        style="position: absolute; width: 100%; height: 100%; top: 0; left: 0; border: none; padding: 0; margin: 0;" 
                        src="{{ $guideVideoUrl }}" 
                        allowfullscreen="allowfullscreen" 
                        allow="fullscreen">
                    </iframe>
                @endif
            </div>
        </div>
    @endif

    <!-- Feature Grid -->
    <div class="grid md:grid-cols-2 gap-3">
        <!-- Feature 1 -->
        <div class="bg-card border border-border rounded-md p-4 shadow-sm hover:bg-muted/50 transition-colors relative overflow-hidden group">
            <div class="absolute top-0 left-0 w-1 h-full bg-vttu-red"></div>
            <div class="flex items-start gap-3 pl-1">
                <div class="w-8 h-8 bg-vttu-red/10 rounded flex items-center justify-center text-vttu-red flex-shrink-0">
                    <i data-lucide="book-open" class="w-4 h-4"></i>
                </div>
                <div>
                    <h4 class="font-bold text-foreground text-sm mb-1">Đọc Sách Linh Hoạt</h4>
                    <ul class="text-xs text-muted-foreground space-y-1 list-disc list-inside">
                        <li>Hỗ trợ xoay ngang/dọc màn hình thiết bị</li>
                        <li>Phóng to, thu nhỏ nội dung linh hoạt</li>
                        <li>Tạo ghi chú trực tiếp ngay trên tài liệu</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Feature 2 -->
        <div class="bg-card border border-border rounded-md p-4 shadow-sm hover:bg-muted/50 transition-colors relative overflow-hidden group">
            <div class="absolute top-0 left-0 w-1 h-full bg-vttu-red"></div>
            <div class="flex items-start gap-3 pl-1">
                <div class="w-8 h-8 bg-vttu-red/10 rounded flex items-center justify-center text-vttu-red flex-shrink-0">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </div>
                <div>
                    <h4 class="font-bold text-foreground text-sm mb-1">Tra Cứu Thông Minh</h4>
                    <ul class="text-xs text-muted-foreground space-y-1 list-disc list-inside">
                        <li>Tìm kiếm giáo trình, sách chuyên ngành nhanh chóng</li>
                        <li>Quản lý lịch sử mượn trả tài liệu</li>
                        <li>Nhận thông báo tự động từ Thư viện</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
