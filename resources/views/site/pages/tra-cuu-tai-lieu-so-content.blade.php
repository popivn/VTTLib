@php
    $guideJson = json_decode($node->content_json ?? '{}', true) ?: [];
    $guideTitle = $guideJson['title'] ?? 'Hướng dẫn tra cứu tài liệu số';
    $guideCondTitle = $guideJson['condition_title'] ?? 'Học liệu và tài liệu số hóa trực tuyến:';
    $guideCondDesc = $guideJson['condition_desc'] ?? 'Bạn đọc có thể dễ dàng truy cập và đọc trực tuyến hàng nghìn đầu sách số, giáo trình điện tử, tài nguyên y khoa và báo cáo khoa học mọi lúc, mọi nơi mà không cần đến thư viện.';
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
    
    $uploadedVideoUrl = $guideJson['uploaded_video_url'] ?? '';
    $embedVideoUrl = $guideJson['embed_video_url'] ?? ($guideJson['video_url'] ?? '');
    $videoSource = $guideJson['video_source'] ?? (!empty($uploadedVideoUrl) ? 'file' : (!empty($embedVideoUrl) ? 'url' : 'none'));

    $guideVideoUrl = ($videoSource === 'none') ? '' : (($videoSource === 'file' && !empty($uploadedVideoUrl)) ? $uploadedVideoUrl : $embedVideoUrl);

    if ($videoSource === 'url' && !empty($guideVideoUrl)) {
        if (str_contains($guideVideoUrl, 'youtube.com/watch?v=')) {
            $guideVideoUrl = str_replace('youtube.com/watch?v=', 'youtube.com/embed/', $guideVideoUrl);
        } elseif (str_contains($guideVideoUrl, 'youtu.be/')) {
            $guideVideoUrl = str_replace('youtu.be/', 'youtube.com/embed/', $guideVideoUrl);
        }
    }

    $isLocalFile = ($videoSource === 'file') || str_contains($guideVideoUrl, '/storage/') || str_ends_with($guideVideoUrl, '.mp4') || str_ends_with($guideVideoUrl, '.webm') || str_ends_with($guideVideoUrl, '.mov');
    // Dynamic PDF Check
    $isPdf = str_contains(strtolower($guideVideoUrl), '.pdf');
@endphp

<style>
    .guide-step-content a {
        color: #006064;
        font-weight: 700;
        text-decoration: underline;
    }
</style>

<div class="space-y-4 animate-fade-in">
    <!-- Banner Header -->
    <div class="relative w-full rounded-md overflow-hidden border border-border shadow-sm bg-[#E0F7FA] py-6 px-4 text-center">
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Dancing+Script:wght@700&display=swap');
            .handwritten-title {
                font-family: 'Dancing Script', cursive;
                color: #006064;
            }
        </style>
        <div class="flex items-center justify-between">
            <div class="hidden sm:flex flex-col gap-3 opacity-60">
                <i data-lucide="laptop" class="w-5 h-5 text-[#006064] rotate-12"></i>
                <i data-lucide="cpu" class="w-5 h-5 text-[#006064] -rotate-12"></i>
            </div>
            
            <div class="flex-1 space-y-1">
                <h1 class="text-2xl md:text-3xl font-black handwritten-title leading-tight uppercase tracking-wide">
                    {{ $guideTitle }}
                </h1>
            </div>

            <div class="hidden sm:flex flex-col gap-3 opacity-60">
                <i data-lucide="file-digit" class="w-5 h-5 text-[#006064] -rotate-45"></i>
                <i data-lucide="hard-drive" class="w-5 h-5 text-[#006064] rotate-12"></i>
            </div>
        </div>
    </div>

    <!-- Info Card -->
    @if(!empty($guideCondDesc))
        <div class="bg-card border border-border rounded-md p-4 shadow-sm">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 bg-cyan-500/10 rounded flex items-center justify-center text-cyan-600 flex-shrink-0">
                    <i data-lucide="cloud-lightning" class="w-4 h-4"></i>
                </div>
                <div class="space-y-1">
                    @if(!empty($guideCondTitle))
                        <h4 class="font-bold text-foreground text-sm">{{ $guideCondTitle }}</h4>
                    @endif
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
                        <i data-lucide="info" class="w-4 h-4 text-[#006064]"></i> {{ $sec['title'] }}
                    </h4>
                @endif
                
                <div class="grid gap-3 text-xs">
                    @foreach($sec['steps'] as $idx => $step)
                        <div class="flex gap-3 p-3 bg-card border border-border rounded-md shadow-sm">
                            <div class="w-6 h-6 bg-cyan-500/10 text-cyan-600 rounded flex items-center justify-center font-bold flex-shrink-0">
                                {{ $step['step_number'] ?? ($idx + 1) }}
                            </div>
                            <div class="space-y-1 flex-1">
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

    <!-- Embedded Video / PDF Section -->
    @if(!empty($guideVideoUrl))
        <div class="space-y-3 pt-3 border-t border-border">
            <h4 class="font-bold text-foreground text-sm flex items-center gap-2">
                <i data-lucide="video" class="w-4 h-4 text-cyan-600"></i> {{ $guideVideoTitle }}
            </h4>
            
            @if($isPdf)
                <div class="relative w-full h-[85vh] min-h-[750px] rounded-lg overflow-hidden border border-border shadow-md bg-muted/30">
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
