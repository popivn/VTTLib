@php
    $guideJson = json_decode($node->content_json ?? '{}', true) ?: [];
    $guideTitle = $guideJson['title'] ?? 'HƯỚNG DẪN ĐĂNG NHẬP TÀI KHOẢN THƯ VIỆN ĐIỆN TỬ';
    $guideCondTitle = $guideJson['condition_title'] ?? 'Điều kiện để đăng nhập thành công:';
    $guideCondDesc = $guideJson['condition_desc'] ?? 'Bạn đọc là học sinh, sinh viên, cán bộ, giảng viên, nhân viên (CBGV) đang học tập và làm việc tại Trường Đại học Võ Trường Toản.';
    
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

    $guideVideoTitle = $guideJson['video_title'] ?? 'Bạn đọc vui lòng xem video hướng dẫn dưới đây:';
    $guideVideoUrl = $guideJson['video_url'] ?? 'https://www.canva.com/design/DAGAyk7W3b8/OtC9Ngs-WllWWEM06hRnwA/watch?embed';

    if (str_contains($guideVideoUrl, 'youtube.com/watch?v=')) {
        $guideVideoUrl = str_replace('youtube.com/watch?v=', 'youtube.com/embed/', $guideVideoUrl);
    } elseif (str_contains($guideVideoUrl, 'youtu.be/')) {
        $guideVideoUrl = str_replace('youtu.be/', 'youtube.com/embed/', $guideVideoUrl);
    }

    // Dynamic PDF Check
    $isPdf = str_contains(strtolower($guideVideoUrl), '.pdf');
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

    <!-- Info Card -->
    @if(!empty($guideCondDesc))
        <div class="bg-card border border-border rounded-md p-4 shadow-sm">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 bg-vttu-red/10 rounded flex items-center justify-center text-vttu-red flex-shrink-0">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
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
        <div class="space-y-2 pt-3 border-t border-border">
            <h4 class="font-bold text-foreground text-sm flex items-center gap-2">
                <i data-lucide="play-circle" class="w-4 h-4 text-vttu-red"></i> {{ $guideVideoTitle }}
            </h4>
            @if($isPdf)
                <div class="relative w-full h-[85vh] min-h-[750px] rounded-md overflow-hidden border border-border shadow-sm bg-muted/30">
                    <iframe src="{{ str_contains($guideVideoUrl, 'http') ? $guideVideoUrl : asset($guideVideoUrl) }}#view=FitH&toolbar=0" class="w-full h-full border-0"></iframe>
                </div>
            @else
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
            @endif
        </div>
    @endif
</div>
