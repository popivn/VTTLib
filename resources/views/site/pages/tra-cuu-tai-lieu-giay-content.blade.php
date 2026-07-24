@php
    $guideJson = json_decode($node->content_json ?? '{}', true) ?: [];
    $guideTitle = $guideJson['title'] ?? 'HƯỚNG DẪN TRA CỨU TÀI LIỆU IN / GIẤY (OPAC)';
    $guideCondTitle = $guideJson['condition_title'] ?? 'Tra cứu trực tuyến trước khi tìm sách:';
    $guideCondDesc = $guideJson['condition_desc'] ?? 'Hệ thống tra cứu trực tuyến OPAC cho phép bạn tìm kiếm sách giấy nhanh chóng và xác định chính xác vị trí kệ sách cần tìm để tiết kiệm thời gian.';
    $guideStepsTitle = $guideJson['steps_title'] ?? 'Các bước tra cứu sách giấy:';
    $guideSteps = $guideJson['steps'] ?? [];
    $guideVideoTitle = $guideJson['video_title'] ?? 'Video hướng dẫn tra cứu OPAC:';
    $guideVideoUrl = $guideJson['video_url'] ?? '';

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
    <!-- Banner Header -->
    <div class="relative w-full rounded-md overflow-hidden border border-border shadow-sm bg-[#FEF9DD] py-6 px-4 text-center">
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Dancing+Script:wght@700&display=swap');
            .handwritten-title {
                font-family: 'Dancing Script', cursive;
                color: #8B0000;
            }
        </style>
        <div class="flex items-center justify-between">
            <div class="hidden sm:flex flex-col gap-3 opacity-60">
                <i data-lucide="paperclip" class="w-5 h-5 text-[#8B0000] rotate-45"></i>
                <i data-lucide="paperclip" class="w-5 h-5 text-[#8B0000] -rotate-12"></i>
            </div>
            
            <div class="flex-1 space-y-1">
                <h1 class="text-2xl md:text-3xl font-black handwritten-title leading-tight uppercase tracking-wide">
                    {{ $guideTitle }}
                </h1>
            </div>

            <div class="hidden sm:flex flex-col gap-3 opacity-60">
                <i data-lucide="pen-tool" class="w-5 h-5 text-[#8B0000] -rotate-45"></i>
                <i data-lucide="file-text" class="w-5 h-5 text-[#8B0000] rotate-12"></i>
            </div>
        </div>
    </div>

    <!-- Info Card -->
    @if(!empty($guideCondDesc))
        <div class="bg-card border border-border rounded-md p-4 shadow-sm">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 bg-vttu-red/10 rounded flex items-center justify-center text-vttu-red flex-shrink-0">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </div>
                <div class="space-y-1">
                    <h4 class="font-bold text-foreground text-sm">{{ $guideCondTitle }}</h4>
                    <p class="text-xs text-muted-foreground leading-relaxed">
                        {{ $guideCondDesc }}
                    </p>
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
