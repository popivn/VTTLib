@extends('layouts.admin')

@section('content')
<div class="space-y-4 animate-in fade-in duration-500">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded bg-primary flex items-center justify-center text-primary-foreground shadow-sm">
                <i data-lucide="star" class="w-5 h-5 fill-primary-foreground"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold text-foreground tracking-tight">{{ __('Khảo sát ý kiến bạn đọc') }}</h1>
                <p class="text-sm text-muted-foreground">{{ __('Danh sách các đánh giá, phản hồi và ý kiến đóng góp từ độc giả.') }}</p>
            </div>
        </div>
    </div>

    <!-- Stats Summary Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="bg-card p-4 rounded-md border border-border shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-blue-500/10 text-blue-500 flex items-center justify-center">
                <i data-lucide="file-text" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-xs text-muted-foreground font-medium">{{ __('Tổng lượt khảo sát') }}</p>
                <p class="text-lg font-bold text-foreground">{{ number_format($totalSurveys) }}</p>
            </div>
        </div>

        <div class="bg-card p-4 rounded-md border border-border shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-amber-500/10 text-amber-500 flex items-center justify-center">
                <i data-lucide="star" class="w-5 h-5 fill-amber-500 text-amber-500"></i>
            </div>
            <div>
                <p class="text-xs text-muted-foreground font-medium">{{ __('Đánh giá trung bình') }}</p>
                <p class="text-lg font-bold text-foreground">{{ $avgRating }} / 5 ⭐</p>
            </div>
        </div>

        <div class="bg-card p-4 rounded-md border border-border shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-emerald-500/10 text-emerald-500 flex items-center justify-center">
                <i data-lucide="smile" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-xs text-muted-foreground font-medium">{{ __('Đánh giá 5 Sao') }}</p>
                <p class="text-lg font-bold text-foreground">{{ number_format($fiveStarCount) }}</p>
            </div>
        </div>

        <div class="bg-card p-4 rounded-md border border-border shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-indigo-500/10 text-indigo-500 flex items-center justify-center">
                <i data-lucide="thumbs-up" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-xs text-muted-foreground font-medium">{{ __('Đánh giá 4 Sao') }}</p>
                <p class="text-lg font-bold text-foreground">{{ number_format($fourStarCount) }}</p>
            </div>
        </div>
    </div>

    <!-- Main Content Box -->
    <div class="bg-card rounded-md shadow-sm border border-border overflow-hidden">
        <!-- Filter & Search Bar -->
        <div class="p-3 bg-muted/30 border-b border-border">
            <form action="{{ route('admin.patron-surveys.index') }}" method="GET" class="flex flex-col sm:flex-row gap-2">
                <!-- Search Input -->
                <div class="relative flex-1 sm:max-w-xs">
                    <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none">
                        <i data-lucide="search" class="w-4 h-4 text-muted-foreground"></i>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" 
                        placeholder="Tìm tên độc giả, mã thẻ, email, nội dung..." 
                        class="block w-full pl-9 pr-3 py-1.5 h-9 text-sm border border-input rounded bg-background text-foreground placeholder-muted-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                </div>
                
                <!-- Rating Filter -->
                <select name="rating" class="h-9 w-full sm:w-44 px-3 py-1.5 text-sm border border-input rounded bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                    <option value="">{{ __('Tất cả điểm số') }}</option>
                    <option value="5" {{ request('rating') == '5' ? 'selected' : '' }}>5 Sao (Rất tốt)</option>
                    <option value="4" {{ request('rating') == '4' ? 'selected' : '' }}>4 Sao (Tốt)</option>
                    <option value="3" {{ request('rating') == '3' ? 'selected' : '' }}>3 Sao (Bình thường)</option>
                    <option value="2" {{ request('rating') == '2' ? 'selected' : '' }}>2 Sao (Kém)</option>
                    <option value="1" {{ request('rating') == '1' ? 'selected' : '' }}>1 Sao (Rất kém)</option>
                </select>

                <!-- Action Buttons -->
                <div class="flex gap-2">
                    <button type="submit" class="inline-flex items-center justify-center gap-2 px-4 h-9 rounded text-xs font-medium transition-all duration-200 active:scale-95 bg-primary text-primary-foreground hover:bg-primary/90 shadow-sm">
                        {{ __('Tìm kiếm') }}
                    </button>

                    @if(request('search') || request('rating'))
                        <a href="{{ route('admin.patron-surveys.index') }}" 
                            class="inline-flex items-center justify-center gap-2 px-3 h-9 rounded text-xs font-medium transition-all duration-200 active:scale-95 bg-muted text-muted-foreground hover:bg-muted/80 border border-border">
                            <i data-lucide="x" class="w-4 h-4"></i>
                            {{ __('Xóa lọc') }}
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Table View -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-muted/50 text-xs font-semibold uppercase tracking-wider text-muted-foreground border-b border-border">
                    <tr>
                        <th class="py-3 px-4">{{ __('Thông tin độc giả') }}</th>
                        <th class="py-3 px-4 text-center">{{ __('Đánh giá chung') }}</th>
                        <th class="py-3 px-4">{{ __('Chi tiết các tiêu chí động') }}</th>
                        <th class="py-3 px-4">{{ __('Nội dung đóng góp / Góp ý') }}</th>
                        <th class="py-3 px-4 text-right">{{ __('Thời gian gửi') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border text-sm">
                    @forelse($surveys as $survey)
                        <tr class="hover:bg-muted/30 transition-colors">
                            <!-- Patron Info -->
                            <td class="py-3 px-4 align-top min-w-[200px]">
                                <div class="font-bold text-foreground">{{ $survey->full_name ?: 'Độc giả ẩn danh' }}</div>
                                @if($survey->card_number)
                                    <div class="text-xs text-muted-foreground flex items-center gap-1 mt-0.5">
                                        <i data-lucide="credit-card" class="w-3 h-3 text-primary"></i>
                                        <span>Mã thẻ: {{ $survey->card_number }}</span>
                                    </div>
                                @endif
                                @if($survey->patron_group)
                                    <div class="text-[10px] text-muted-foreground mt-0.5">
                                        Đối tượng: <span class="font-medium text-foreground">{{ ucfirst(str_replace('_', ' ', $survey->patron_group)) }}</span>
                                    </div>
                                @endif
                                @if($survey->email_phone)
                                    <div class="text-[10px] text-muted-foreground mt-1 flex items-center gap-1">
                                        <i data-lucide="contact" class="w-3 h-3 text-muted-foreground"></i>
                                        <span>{{ $survey->email_phone }}</span>
                                    </div>
                                @endif
                            </td>

                            <!-- Overall Rating -->
                            <td class="py-3 px-4 align-top text-center min-w-[120px]">
                                <div class="inline-flex flex-col items-center">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-black uppercase tracking-wider shadow-sm border
                                        {{ $survey->rating_overall >= 4 ? 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20' : ($survey->rating_overall == 3 ? 'bg-amber-500/10 text-amber-600 border-amber-500/20' : 'bg-rose-500/10 text-rose-600 border-rose-500/20') }}">
                                        {{ $survey->rating_overall }} / 5 ⭐
                                    </span>
                                    <div class="flex items-center gap-0.5 mt-1.5 text-amber-400">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i data-lucide="star" class="w-3.5 h-3.5 {{ $i <= $survey->rating_overall ? 'fill-amber-400 text-amber-400' : 'text-muted-foreground/30' }}"></i>
                                        @endfor
                                    </div>
                                </div>
                            </td>

                            <!-- Dynamic Detailed Criteria Ratings -->
                            <td class="py-3 px-4 align-top min-w-[220px]">
                                @if($survey->ratings && $survey->ratings->count() > 0)
                                    <div class="space-y-1.5 text-xs">
                                        @foreach($survey->ratings as $item)
                                            <div class="flex justify-between items-center gap-2 text-muted-foreground">
                                                <span class="truncate max-w-[160px]" title="{{ $item->criterion?->name }}">{{ $item->criterion?->name ?? 'Tiêu chí' }}:</span>
                                                <span class="font-bold text-foreground flex items-center gap-0.5">
                                                    {{ $item->rating }}/5 <i data-lucide="star" class="w-3 h-3 text-amber-400 fill-amber-400 inline"></i>
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-xs text-muted-foreground italic">Chưa chọn tiêu chí chi tiết</span>
                                @endif
                            </td>

                            <!-- Content / Feedback Text -->
                            <td class="py-3 px-4 align-top">
                                @if($survey->content)
                                    <div class="p-2.5 rounded bg-muted/40 border border-border text-xs leading-relaxed text-foreground italic whitespace-pre-line max-w-md">
                                        "{{ $survey->content }}"
                                    </div>
                                @else
                                    <span class="text-xs text-muted-foreground italic">Chưa có nội dung đóng góp</span>
                                @endif
                            </td>

                            <!-- Created At -->
                            <td class="py-3 px-4 align-top text-right whitespace-nowrap text-xs text-muted-foreground">
                                <div class="font-medium text-foreground">{{ $survey->created_at ? $survey->created_at->format('H:i d/m/Y') : '-' }}</div>
                                <div class="text-[10px] mt-0.5">{{ $survey->created_at ? $survey->created_at->diffForHumans() : '' }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-muted-foreground">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <i data-lucide="inbox" class="w-10 h-10 text-muted-foreground/40"></i>
                                    <p class="font-medium text-sm">{{ __('Chưa có khảo sát nào.') }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Bar -->
        @if($surveys->hasPages())
            <div class="p-3 bg-muted/20 border-t border-border flex items-center justify-between">
                {{ $surveys->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
