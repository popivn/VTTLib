@php
    $majors = \App\Models\CurriculumMajor::where('is_active', true)
        ->orderBy('sort_order', 'asc')
        ->get();
@endphp

<div class="w-full space-y-6">
    <!-- Top Hero Banner Image -->
    <div class="relative rounded-xl overflow-hidden shadow-sm border border-slate-200">
        <img src="{{ asset('storage/pages/banner_nganh_dao_tao.png') }}" 
             alt="Ngành Đào Tạo Banner" 
             class="w-full h-auto object-cover rounded-xl">
    </div>

    <!-- Majors List Cards -->
    <div class="space-y-6">
        @forelse($majors as $item)
            <div class="bg-card rounded-xl p-6 shadow-sm border border-border hover:shadow-md transition-all">
                <!-- Image Thumbnail -->
                @if($item->image)
                    <div class="mb-4">
                        <img src="{{ asset($item->image) }}" alt="{{ $item->title }}" class="w-48 h-32 object-cover rounded-lg shadow-sm border border-slate-200">
                    </div>
                @endif

                <!-- Title with Red Pipe -->
                <h3 class="text-xl font-black text-[#8b0000] uppercase tracking-wide flex items-center gap-2 mb-3">
                    <span class="text-vttu-red font-bold">|</span> {{ $item->title }}
                </h3>

                <!-- Description Text -->
                <p class="text-xs md:text-sm text-foreground/80 leading-relaxed font-normal mb-5 text-justify">
                    {{ $item->description }}
                </p>

                <!-- Xem thêm Button -->
                @if($item->link_url)
                    <div>
                        <a href="{{ $item->link_url }}" target="_blank" rel="noopener noreferrer" 
                           class="inline-block px-7 py-2 border-2 border-[#8b0000] text-[#8b0000] hover:bg-[#8b0000] hover:text-white text-xs font-bold rounded-md transition-all tracking-wider uppercase">
                            Xem thêm
                        </a>
                    </div>
                @endif
            </div>
        @empty
            <div class="bg-card p-12 text-center rounded-xl border border-border text-muted-foreground">
                Chưa có ngành đào tạo nào được cập nhật.
            </div>
        @endforelse
    </div>
</div>
