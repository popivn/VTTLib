@php
    $guideJson = json_decode($node->content_json ?? '{}', true) ?: [];
    $guideTitle = $guideJson['title'] ?? 'HƯỚNG DẪN MƯỢN TRƯỚC VÀ GIA HẠN TÀI LIỆU';
    $guideCondTitle = $guideJson['condition_title'] ?? 'Thông tin dịch vụ mượn trước & gia hạn:';
    $guideCondDesc = $guideJson['condition_desc'] ?? 'Trong quá trình học tập và nghiên cứu tại trường, bạn đọc có thể đặt mượn tài liệu, theo dõi thông tin quá hạn sách, gia hạn sách trực tuyến... thông qua tài khoản thư viện cá nhân.';

    // Dynamic Sections Parsing (with fallback to legacy steps / section2_steps)
    $sections = $guideJson['sections'] ?? [];
    if (empty($sections)) {
        $legacySteps = $guideJson['steps'] ?? [];
        $legacySec2Steps = $guideJson['section2_steps'] ?? [];
        if (!empty($legacySteps)) {
            $sections[] = [
                'title' => $guideJson['steps_title'] ?? 'Các bước thực hiện:',
                'steps' => $legacySteps
            ];
        }
        if (!empty($legacySec2Steps)) {
            $sections[] = [
                'title' => $guideJson['section2_title'] ?? 'Các bước thực hiện Phần 2:',
                'steps' => $legacySec2Steps
            ];
        }
    }

    $guideVideoTitle = $guideJson['video_title'] ?? 'Video hướng dẫn thao tác:';
    $uploadedVideoUrl = $guideJson['uploaded_video_url'] ?? '';
    $embedVideoUrl = $guideJson['embed_video_url'] ?? ($guideJson['video_url'] ?? '');
    $videoSource = $guideJson['video_source'] ?? (!empty($uploadedVideoUrl) ? 'file' : (!empty($embedVideoUrl) ? 'url' : 'none'));
    $guideVideoUrl = ($videoSource === 'none') ? '' : (($videoSource === 'file') ? $uploadedVideoUrl : $embedVideoUrl);

    if (str_contains($guideVideoUrl, 'youtube.com/watch?v=')) {
        $guideVideoUrl = str_replace('youtube.com/watch?v=', 'youtube.com/embed/', $guideVideoUrl);
    } elseif (str_contains($guideVideoUrl, 'youtu.be/')) {
        $guideVideoUrl = str_replace('youtu.be/', 'youtube.com/embed/', $guideVideoUrl);
    }

    $isPdf = str_contains(strtolower($guideVideoUrl), '.pdf');
    $isLocalFile = ($videoSource === 'file') || str_contains($guideVideoUrl, '/storage/') || str_ends_with($guideVideoUrl, '.mp4') || str_ends_with($guideVideoUrl, '.webm') || str_ends_with($guideVideoUrl, '.mov');
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

    <!-- Info Card -->
    @if(!empty($guideCondDesc))
        <div class="bg-card border border-border rounded-md p-4 shadow-sm">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 bg-vttu-red/10 rounded flex items-center justify-center text-vttu-red flex-shrink-0">
                    <i data-lucide="bookmark-check" class="w-4 h-4"></i>
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

    <!-- Dynamic Sections Rendering -->
    @foreach($sections as $sIdx => $sec)
        @if(!empty($sec['steps']))
            <div class="space-y-3 pt-3 border-t border-border">
                @if(!empty($sec['title']))
                    <h4 class="font-bold text-foreground text-sm flex items-center gap-2">
                        <i data-lucide="info" class="w-4 h-4 text-vttu-red"></i> {{ $sec['title'] }}
                    </h4>
                @endif
                
                <div class="grid gap-2 text-xs">
                    @foreach($sec['steps'] as $idx => $step)
                        <div class="flex gap-3 p-3 bg-card border border-border rounded-md shadow-sm">
                            <div class="w-6 h-6 bg-vttu-red/10 text-vttu-red rounded flex items-center justify-center font-bold flex-shrink-0">
                                {{ $step['step_number'] ?? ($idx + 1) }}
                            </div>
                            <div class="space-y-0.5 flex-1 min-w-0">
                                @if(!empty($step['title']))
                                    <h5 class="font-bold text-foreground">{{ $step['title'] }}</h5>
                                @endif
                                <div class="guide-step-content text-muted-foreground leading-relaxed prose dark:prose-invert max-w-none">
                                    {!! $step['content'] ?? '' !!}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endforeach

    <!-- Video / PDF Section -->
    @if(!empty($guideVideoUrl))
        <div class="space-y-3 pt-3 border-t border-border">
            <h3 class="font-bold text-foreground text-sm flex items-center gap-2">
                <span class="w-2.5 h-2.5 bg-vttu-red rounded-sm"></span>
                {{ $guideVideoTitle }}
            </h3>
            
            @if($isPdf)
                <div class="relative w-full h-[85vh] min-h-[750px] rounded-md overflow-hidden border border-border shadow-sm bg-muted/30">
                    <iframe src="{{ str_contains($guideVideoUrl, 'http') ? $guideVideoUrl : asset($guideVideoUrl) }}#view=FitH&toolbar=0" class="w-full h-full border-0"></iframe>
                </div>
            @else
                <div class="relative w-full rounded-lg overflow-hidden border border-border shadow-md bg-black aspect-video">
                    @if($isLocalFile)
                        <video class="w-full h-full object-contain" controls preload="metadata">
                            <source src="{{ str_contains($guideVideoUrl, 'http') ? $guideVideoUrl : asset($guideVideoUrl) }}" type="video/mp4">
                            {{ __('Trình duyệt của bạn không hỗ trợ thẻ video.') }}
                        </video>
                    @else
                        <iframe 
                            class="absolute top-0 left-0 w-full h-full"
                            src="{{ $guideVideoUrl }}" 
                            title="{{ $guideTitle }}"
                            frameborder="0" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                            allowfullscreen>
                        </iframe>
                    @endif
                </div>
            @endif
        </div>
    @endif
</div>
