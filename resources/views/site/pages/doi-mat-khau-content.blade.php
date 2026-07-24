@php
    $guideJson = json_decode($node->content_json ?? '{}', true) ?: [];
    $guideTitle = $guideJson['title'] ?? 'HƯỚNG DẪN ĐỔI MẬT KHẨU TÀI KHOẢN THƯ VIỆN';
    $guideCondTitle = $guideJson['condition_title'] ?? 'Khuyến nghị bảo mật:';
    $guideCondDesc = $guideJson['condition_desc'] ?? 'Bạn đọc nên thay đổi mật khẩu định kỳ để bảo vệ tài khoản cá nhân của mình trên hệ thống Thư viện điện tử. Mật khẩu mới nên có độ dài tối thiểu 8 ký tự.';
    $guideStepsTitle = $guideJson['steps_title'] ?? 'Các bước thực hiện đổi mật khẩu:';
    $guideSteps = $guideJson['steps'] ?? [];
    $guideVideoTitle = $guideJson['video_title'] ?? 'Bạn đọc vui lòng xem video hướng dẫn thao tác dưới đây:';
    $guideVideoUrl = $guideJson['video_url'] ?? '';

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

    <!-- Security Recommendation Card -->
    @if(!empty($guideCondDesc))
        <div class="bg-card border border-border rounded-md p-4 shadow-sm">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 bg-vttu-red/10 rounded flex items-center justify-center text-vttu-red flex-shrink-0">
                    <i data-lucide="key" class="w-4 h-4"></i>
                </div>
                <div class="space-y-2 flex-1">
                    <div class="space-y-1">
                        <h4 class="font-bold text-foreground text-sm">{{ $guideCondTitle }}</h4>
                        <p class="text-xs text-muted-foreground leading-relaxed">
                            {{ $guideCondDesc }}
                        </p>
                    </div>
                    <div class="pt-1">
                        <a href="/my-profile?tab=password" class="inline-flex items-center justify-center px-3 py-1.5 bg-vttu-red text-white hover:bg-vttu-dark text-xs font-bold rounded shadow-sm transition-all gap-1.5 active:scale-[0.98]">
                            <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                            <span>Đến trang Đổi mật khẩu ngay</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Step Instructions Section -->
    @if(!empty($guideSteps))
        <div class="space-y-3 pt-3 border-t border-border">
            <h4 class="font-bold text-foreground text-sm flex items-center gap-2">
                <i data-lucide="info" class="w-4 h-4 text-vttu-red"></i> {{ $guideStepsTitle }}
            </h4>
            
            <div class="grid gap-2 text-xs">
                @foreach($guideSteps as $idx => $step)
                    <div class="flex gap-3 p-3 bg-card border border-border rounded-md shadow-sm">
                        <div class="w-6 h-6 bg-vttu-red/10 text-vttu-red rounded flex items-center justify-center font-bold flex-shrink-0">
                            {{ $step['step_number'] ?? ($idx + 1) }}
                        </div>
                        <div class="space-y-0.5 flex-1 min-w-0">
                            <h5 class="font-bold text-foreground">{{ $step['title'] ?? ('Bước ' . ($idx + 1)) }}</h5>
                            <div class="text-muted-foreground leading-relaxed prose dark:prose-invert max-w-none">
                                {!! $step['content'] ?? '' !!}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

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
                        style="position: absolute; width: 100%; height: 100%; top: 0; left: 0; border: none; padding: 0;" 
                        src="{{ $guideVideoUrl }}" 
                        allowfullscreen="allowfullscreen" 
                        allow="fullscreen">
                    </iframe>
                @endif
            </div>
        </div>
    @endif
</div>
