@php
    $guideJson = json_decode($node->content_json ?? '{}', true) ?: [];
    $guideTitle = $guideJson['title'] ?? 'HƯỚNG DẪN MƯỢN TRƯỚC VÀ GIA HẠN TÀI LIỆU';
    $guideCondTitle = $guideJson['condition_title'] ?? 'Thông tin dịch vụ mượn trước & gia hạn:';
    $guideCondDesc = $guideJson['condition_desc'] ?? 'Trong quá trình học tập và nghiên cứu tại trường, bạn đọc có thể đặt mượn tài liệu, theo dõi thông tin quá hạn sách, gia hạn sách trực tuyến... thông qua tài khoản thư viện cá nhân.';
    $guideStepsTitle = $guideJson['steps_title'] ?? '1. Đăng ký mượn trước tài liệu';
    $guideSteps = $guideJson['steps'] ?? [];
    $section2Title = $guideJson['section2_title'] ?? '2. Gia hạn tài liệu trực tuyến';
    $section2Steps = $guideJson['section2_steps'] ?? [];
    $guideVideoTitle = $guideJson['video_title'] ?? 'Video hướng dẫn mượn trước & gia hạn:';
    $uploadedVideoUrl = $guideJson['uploaded_video_url'] ?? '';
    $embedVideoUrl = $guideJson['embed_video_url'] ?? ($guideJson['video_url'] ?? '');
    $videoSource = $guideJson['video_source'] ?? (!empty($uploadedVideoUrl) ? 'file' : 'url');
    $guideVideoUrl = ($videoSource === 'file') ? $uploadedVideoUrl : $embedVideoUrl;

    if (str_contains($guideVideoUrl, 'youtube.com/watch?v=')) {
        $guideVideoUrl = str_replace('youtube.com/watch?v=', 'youtube.com/embed/', $guideVideoUrl);
    } elseif (str_contains($guideVideoUrl, 'youtu.be/')) {
        $guideVideoUrl = str_replace('youtu.be/', 'youtube.com/embed/', $guideVideoUrl);
    }

    $isLocalFile = str_contains($guideVideoUrl, '/storage/') || str_ends_with($guideVideoUrl, '.mp4') || str_ends_with($guideVideoUrl, '.webm') || str_ends_with($guideVideoUrl, '.mov');
@endphp

<style>
    .guide-step-content a {
        color: #b91c1c;
        font-weight: 700;
        text-decoration: underline;
    }
</style>

<div class="space-y-4 animate-fade-in">
    <!-- Section Header -->
    <div class="border-b border-border pb-3">
        <h2 class="text-xl md:text-2xl font-black text-foreground tracking-tight leading-tight uppercase">
            {{ $guideTitle }}
        </h2>
        <div class="w-16 h-1 bg-vttu-red mt-2 rounded"></div>
    </div>

    <!-- Intro Card -->
    @if(!empty($guideCondDesc))
        <div class="bg-card border border-border rounded-md p-4 shadow-sm">
            @if(!empty($guideCondTitle))
                <h4 class="font-bold text-foreground text-sm mb-1">{{ $guideCondTitle }}</h4>
            @endif
            <p class="text-xs text-muted-foreground leading-relaxed">
                {{ $guideCondDesc }}
            </p>
        </div>
    @endif

    <!-- Section 1: Steps -->
    @if(!empty($guideSteps))
        <div class="space-y-3 pt-3 border-t border-border">
            <h3 class="font-bold text-foreground text-sm flex items-center gap-2">
                <span class="w-2.5 h-2.5 bg-vttu-red rounded-sm"></span>
                {{ $guideStepsTitle }}
            </h3>
            
            <div class="grid gap-2 text-xs">
                @foreach($guideSteps as $idx => $step)
                    <div class="flex gap-3 p-3 bg-card border border-border rounded-md shadow-sm">
                        <div class="w-6 h-6 bg-vttu-red/10 text-vttu-red rounded flex items-center justify-center font-bold flex-shrink-0">
                            {{ $step['step_number'] ?? ($idx + 1) }}
                        </div>
                        <div class="space-y-0.5 flex-1 min-w-0">
                            <h5 class="font-bold text-foreground">{{ $step['title'] ?? ('Bước ' . ($idx + 1)) }}</h5>
                            <div class="guide-step-content text-muted-foreground leading-relaxed prose dark:prose-invert max-w-none">
                                {!! $step['content'] ?? '' !!}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Section 2: Renewal Steps -->
    @if(!empty($section2Steps))
        <div class="space-y-3 pt-3 border-t border-border">
            <h3 class="font-bold text-foreground text-sm flex items-center gap-2">
                <span class="w-2.5 h-2.5 bg-vttu-red rounded-sm"></span>
                {{ $section2Title }}
            </h3>
            
            <div class="grid gap-2 text-xs">
                @foreach($section2Steps as $idx => $step)
                    <div class="flex gap-3 p-3 bg-card border border-border rounded-md shadow-sm">
                        <div class="w-6 h-6 bg-vttu-red/10 text-vttu-red rounded flex items-center justify-center font-bold flex-shrink-0">
                            {{ $step['step_number'] ?? ($idx + 1) }}
                        </div>
                        <div class="space-y-0.5 flex-1 min-w-0">
                            <h5 class="font-bold text-foreground">{{ $step['title'] ?? ('Bước ' . ($idx + 1)) }}</h5>
                            <div class="guide-step-content text-muted-foreground leading-relaxed prose dark:prose-invert max-w-none">
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
        <div class="space-y-3 pt-3 border-t border-border">
            <h3 class="font-bold text-foreground text-sm flex items-center gap-2">
                <span class="w-2.5 h-2.5 bg-vttu-red rounded-sm"></span>
                {{ $guideVideoTitle }}
            </h3>

            <div class="relative w-full aspect-video rounded-md overflow-hidden border border-border shadow-md bg-black">
                @if($isLocalFile)
                    <video src="{{ str_contains($guideVideoUrl, '/storage/') ? asset($guideVideoUrl) : asset('storage/' . $guideVideoUrl) }}" controls class="w-full h-full object-contain"></video>
                @else
                    <iframe src="{{ $guideVideoUrl }}" class="absolute inset-0 w-full h-full border-0" allowfullscreen allow="autoplay; fullscreen"></iframe>
                @endif
            </div>
        </div>
    @endif
</div>
