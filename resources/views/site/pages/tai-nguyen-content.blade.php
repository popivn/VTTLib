<div class="not-prose w-full space-y-6">
    <!-- Introduction Text -->
    <div class="text-xs md:text-sm text-muted-foreground leading-relaxed">
        {{ __('Thư viện Trường Đại học Võ Trường Toản cung cấp nguồn tài nguyên học liệu đa dạng và phong phú, kết hợp giữa tài liệu in truyền thống và hệ thống tài liệu số hiện đại, giúp độc giả dễ dàng tiếp cận tri thức mọi lúc, mọi nơi.') }}
    </div>

    <!-- 4 Resource Categories Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <!-- Card 1: Tài liệu giấy -->
        @php
            $giayNode = \App\Models\SiteNode::where('node_code', 'tai-lieu-giay')->first();
            $giayUrl = $giayNode ? $giayNode->getUrl() : '/page/tai-lieu-giay';
        @endphp
        <div class="flex flex-col bg-card border border-border rounded shadow-sm hover:shadow hover:border-vttu-red/30 transition-all duration-300 overflow-hidden group">
            <div class="h-40 overflow-hidden relative bg-slate-100 dark:bg-slate-900">
                <img src="https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?w=400&h=250&fit=crop" 
                     alt="Tài liệu giấy" 
                     class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                <div class="absolute bottom-3 left-3 flex items-center gap-2 text-white">
                    <div class="w-8 h-8 rounded bg-vttu-red flex items-center justify-center border border-white/20">
                        <i data-lucide="book" class="w-4 h-4 text-white"></i>
                    </div>
                    <span class="text-xs font-black uppercase tracking-wider">{{ __('Tài liệu giấy') }}</span>
                </div>
            </div>
            <div class="p-4 flex-1 flex flex-col justify-between space-y-4">
                <p class="text-xs text-muted-foreground leading-relaxed">
                    {{ __('Kho tài liệu in gồm giáo trình, tài liệu tham khảo, luận văn, luận án và báo, tạp chí chuyên ngành phục vụ trực tiếp tại các phòng đọc của thư viện.') }}
                </p>
                <a href="{{ $giayUrl }}" 
                   class="inline-flex items-center justify-center gap-1.5 w-full py-2 bg-[#A80D0D] hover:bg-[#8f0b0b] !text-white text-xs font-black rounded uppercase tracking-wider transition-all shadow-sm">
                    <span>{{ __('Khám phá kho sách') }}</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform"></i>
                </a>
            </div>
        </div>

        <!-- Card 2: Tài liệu số -->
        @php
            $soNode = \App\Models\SiteNode::where('node_code', 'tai-lieu-so')->first();
            $soUrl = $soNode ? $soNode->getUrl() : '/tai-lieu-so';
        @endphp
        <div class="flex flex-col bg-card border border-border rounded shadow-sm hover:shadow hover:border-vttu-red/30 transition-all duration-300 overflow-hidden group">
            <div class="h-40 overflow-hidden relative bg-slate-100 dark:bg-slate-900">
                <img src="https://images.unsplash.com/photo-1497633762265-9d179a990aa6?w=400&h=250&fit=crop" 
                     alt="Tài liệu số" 
                     class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                <div class="absolute bottom-3 left-3 flex items-center gap-2 text-white">
                    <div class="w-8 h-8 rounded bg-vttu-red flex items-center justify-center border border-white/20">
                        <i data-lucide="file-text" class="w-4 h-4 text-white"></i>
                    </div>
                    <span class="text-xs font-black uppercase tracking-wider">{{ __('Tài liệu số') }}</span>
                </div>
            </div>
            <div class="p-4 flex-1 flex flex-col justify-between space-y-4">
                <p class="text-xs text-muted-foreground leading-relaxed">
                    {{ __('Hàng ngàn giáo trình số, tài liệu học tập, sách tham khảo điện tử có bản quyền, hỗ trợ độc giả đăng nhập đọc trực tuyến 24/7 vô cùng thuận tiện.') }}
                </p>
                <a href="{{ $soUrl }}" 
                   class="inline-flex items-center justify-center gap-1.5 w-full py-2 bg-[#A80D0D] hover:bg-[#8f0b0b] !text-white text-xs font-black rounded uppercase tracking-wider transition-all shadow-sm">
                    <span>{{ __('Đọc tài liệu số') }}</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform"></i>
                </a>
            </div>
        </div>

        <!-- Card 3: Cơ sở dữ liệu -->
        @php
            $csdlNode = \App\Models\SiteNode::where('node_code', 'co-so-du-lieu')->first();
            $csdlUrl = $csdlNode ? $csdlNode->getUrl() : '/page/co-so-du-lieu';
        @endphp
        <div class="flex flex-col bg-card border border-border rounded shadow-sm hover:shadow hover:border-vttu-red/30 transition-all duration-300 overflow-hidden group">
            <div class="h-40 overflow-hidden relative bg-slate-100 dark:bg-slate-900">
                <img src="https://images.unsplash.com/photo-1544383835-bda2bc66a55d?w=400&h=250&fit=crop" 
                     alt="Cơ sở dữ liệu" 
                     class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                <div class="absolute bottom-3 left-3 flex items-center gap-2 text-white">
                    <div class="w-8 h-8 rounded bg-vttu-red flex items-center justify-center border border-white/20">
                        <i data-lucide="database" class="w-4 h-4 text-white"></i>
                    </div>
                    <span class="text-xs font-black uppercase tracking-wider">{{ __('Cơ sở dữ liệu') }}</span>
                </div>
            </div>
            <div class="p-4 flex-1 flex flex-col justify-between space-y-4">
                <p class="text-xs text-muted-foreground leading-relaxed">
                    {{ __('Liên kết truy cập các cơ sở dữ liệu khoa học trực tuyến lớn trong nước và quốc tế có bản quyền phục vụ công tác giảng dạy, học tập và nghiên cứu.') }}
                </p>
                <a href="{{ $csdlUrl }}" 
                   class="inline-flex items-center justify-center gap-1.5 w-full py-2 bg-[#A80D0D] hover:bg-[#8f0b0b] !text-white text-xs font-black rounded uppercase tracking-wider transition-all shadow-sm">
                    <span>{{ __('Xem cơ sở dữ liệu') }}</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform"></i>
                </a>
            </div>
        </div>

        <!-- Card 4: Tài nguyên giáo dục mở -->
        @php
            $oerNode = \App\Models\SiteNode::where('node_code', 'tai-nguyen-giao-duc-mo')->first();
            $oerUrl = $oerNode ? $oerNode->getUrl() : '/oer';
        @endphp
        <div class="flex flex-col bg-card border border-border rounded shadow-sm hover:shadow hover:border-vttu-red/30 transition-all duration-300 overflow-hidden group">
            <div class="h-40 overflow-hidden relative bg-slate-100 dark:bg-slate-900">
                <img src="https://images.unsplash.com/photo-1509062522246-3755977927d7?w=400&h=250&fit=crop" 
                     alt="Tài nguyên giáo dục mở" 
                     class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                <div class="absolute bottom-3 left-3 flex items-center gap-2 text-white">
                    <div class="w-8 h-8 rounded bg-vttu-red flex items-center justify-center border border-white/20">
                        <i data-lucide="globe" class="w-4 h-4 text-white"></i>
                    </div>
                    <span class="text-xs font-black uppercase tracking-wider">{{ __('Học liệu mở OER') }}</span>
                </div>
            </div>
            <div class="p-4 flex-1 flex flex-col justify-between space-y-4">
                <p class="text-xs text-muted-foreground leading-relaxed">
                    {{ __('Kho học liệu mở phi thương mại chất lượng cao gồm bài giảng, giáo trình mở phục vụ nhu cầu nghiên cứu học thuật hoàn toàn miễn phí.') }}
                </p>
                <a href="{{ $oerUrl }}" 
                   class="inline-flex items-center justify-center gap-1.5 w-full py-2 bg-[#A80D0D] hover:bg-[#8f0b0b] !text-white text-xs font-black rounded uppercase tracking-wider transition-all shadow-sm">
                    <span>{{ __('Khám phá học liệu mở') }}</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform"></i>
                </a>
            </div>
        </div>
    </div>
</div>
