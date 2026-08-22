<div class="space-y-4">
    <!-- Banner -->
    <div class="w-full">
        <img src="{{ asset('assets/imgs/huong-dan/banner.png') }}" alt="Hướng dẫn sử dụng thư viện" class="w-full h-auto rounded border border-border shadow-sm">
    </div>

    <!-- Grid of guides -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <!-- Card 1: Cẩm nang HDSD -->
        <a href="/cam-nang-hdsd" class="group flex flex-col items-center p-3 bg-card border border-border rounded-md hover:bg-muted/50 active:scale-[0.98] transition-all duration-200">
            <div class="w-full h-32 flex items-center justify-center mb-3">
                <svg viewBox="0 0 120 100" class="w-24 h-24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="10" y="80" width="100" height="6" rx="2" fill="#8B4513"/>
                    <rect x="15" y="45" width="90" height="4" rx="1" fill="#8B4513"/>
                    <rect x="18" y="10" width="4" height="70" fill="#A0522D"/>
                    <rect x="98" y="10" width="4" height="70" fill="#A0522D"/>
                    <rect x="25" y="15" width="10" height="30" rx="1" fill="#7B0000"/>
                    <rect x="35" y="12" width="8" height="33" rx="1" fill="#FFD700"/>
                    <rect x="43" y="18" width="12" height="27" rx="1" fill="#10B981"/>
                    <rect x="65" y="20" width="9" height="25" rx="1" fill="#3B82F6"/>
                    <path d="M78 20 L88 35 H82 L74 20 Z" fill="#8B5CF6"/>
                    <path d="M84 22 L94 37 H88 L80 22 Z" fill="#F59E0B"/>
                    <rect x="25" y="50" width="12" height="30" rx="1" fill="#3B82F6"/>
                    <rect x="37" y="55" width="10" height="25" rx="1" fill="#7B0000"/>
                    <rect x="47" y="52" width="8" height="28" rx="1" fill="#8B5CF6"/>
                    <rect x="60" y="48" width="14" height="32" rx="1" fill="#10B981"/>
                    <rect x="74" y="53" width="10" height="27" rx="1" fill="#FFD700"/>
                    <path d="M86 55 L96 70 H90 L82 55 Z" fill="#EF4444"/>
                </svg>
            </div>
            <div class="w-full py-2 bg-vttu-red text-white text-center font-bold text-[9px] uppercase tracking-wider rounded-sm group-hover:bg-vttu-dark transition-colors">
                Cẩm nang HDSD Thư viện
            </div>
        </a>

        <!-- Card 2: Tải ứng dụng -->
        <a href="/tai-app-mobile" class="group flex flex-col items-center p-3 bg-card border border-border rounded-md hover:bg-muted/50 active:scale-[0.98] transition-all duration-200">
            <div class="w-full h-32 flex items-center justify-center mb-3">
                <svg viewBox="0 0 120 100" class="w-24 h-24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="40" y="10" width="40" height="80" rx="6" fill="#1E293B" stroke="#475569" stroke-width="2"/>
                    <rect x="43" y="15" width="34" height="66" rx="3" fill="#F1F5F9"/>
                    <rect x="56" y="12" width="8" height="1.5" rx="0.75" fill="#64748B"/>
                    <circle cx="53" cy="12.75" r="0.75" fill="#64748B"/>
                    <rect x="48" y="20" width="6" height="6" rx="1" fill="#EF4444"/>
                    <rect x="57" y="20" width="6" height="6" rx="1" fill="#10B981"/>
                    <rect x="66" y="20" width="6" height="6" rx="1" fill="#3B82F6"/>
                    <rect x="48" y="29" width="6" height="6" rx="1" fill="#F59E0B"/>
                    <rect x="57" y="29" width="6" height="6" rx="1" fill="#8B5CF6"/>
                    <rect x="66" y="29" width="6" height="6" rx="1" fill="#EF4444"/>
                    <rect x="51" y="42" width="18" height="18" rx="2" fill="#7B0000"/>
                    <path d="M55 54 L60 48 L65 54 Z" fill="#FFD700"/>
                    <line x1="55" y1="85" x2="65" y2="85" stroke="#64748B" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
            </div>
            <div class="w-full py-2 bg-vttu-red text-white text-center font-bold text-[9px] uppercase tracking-wider rounded-sm group-hover:bg-vttu-dark transition-colors">
                Tải ứng dụng trên điện thoại
            </div>
        </a>

        <!-- Card 3: Đăng nhập -->
        <a href="{{ route('login') }}" class="group flex flex-col items-center p-3 bg-card border border-border rounded-md hover:bg-muted/50 active:scale-[0.98] transition-all duration-200">
            <div class="w-full h-32 flex items-center justify-center mb-3">
                <svg viewBox="0 0 120 100" class="w-24 h-24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="25" y="15" width="70" height="70" rx="4" fill="#FFFFFF" stroke="#E2E8F0" stroke-width="1.5"/>
                    <rect x="25" y="15" width="70" height="12" rx="4" fill="#F8FAFC" stroke="#E2E8F0" stroke-width="1.5"/>
                    <circle cx="32" cy="21" r="2" fill="#EF4444"/>
                    <circle cx="38" cy="21" r="2" fill="#F59E0B"/>
                    <circle cx="44" cy="21" r="2" fill="#10B981"/>
                    <circle cx="60" cy="42" r="8" fill="#CBD5E1"/>
                    <path d="M52 56 C52 51 55 49 60 49 C65 49 68 51 68 56 Z" fill="#CBD5E1"/>
                    <rect x="35" y="60" width="50" height="5" rx="1" fill="#F1F5F9" stroke="#E2E8F0" stroke-width="0.5"/>
                    <rect x="35" y="68" width="50" height="5" rx="1" fill="#F1F5F9" stroke="#E2E8F0" stroke-width="0.5"/>
                    <rect x="35" y="76" width="50" height="5" rx="1" fill="#7B0000"/>
                </svg>
            </div>
            <div class="w-full py-2 bg-vttu-red text-white text-center font-bold text-[9px] uppercase tracking-wider rounded-sm group-hover:bg-vttu-dark transition-colors">
                Đăng nhập tài khoản
            </div>
        </a>

        <!-- Card 4: Đổi mật khẩu -->
        <a href="{{ route('site.page', 'doi-mat-khau') }}" class="group flex flex-col items-center p-3 bg-card border border-border rounded-md hover:bg-muted/50 active:scale-[0.98] transition-all duration-200">
            <div class="w-full h-32 flex items-center justify-center mb-3">
                <svg viewBox="0 0 120 100" class="w-24 h-24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="40" y="30" width="40" height="30" rx="8" fill="#475569" stroke="#1E293B" stroke-width="2"/>
                    <rect x="48" y="40" width="24" height="10" rx="5" fill="#F8FAFC"/>
                    <circle cx="54" cy="45" r="2" fill="#3B82F6"/>
                    <circle cx="66" cy="45" r="2" fill="#3B82F6"/>
                    <line x1="60" y1="30" x2="60" y2="20" stroke="#475569" stroke-width="2"/>
                    <circle cx="60" cy="18" r="3" fill="#EF4444"/>
                    <rect x="48" y="65" width="24" height="20" rx="3" fill="#F59E0B"/>
                    <path d="M52 65 V60 C52 55 56 52 60 52 C64 52 68 55 68 60 V65" stroke="#F59E0B" stroke-width="2" stroke-linecap="round"/>
                    <circle cx="60" cy="73" r="1.5" fill="#1E293B"/>
                    <line x1="60" y1="74.5" x2="60" y2="80" stroke="#1E293B" stroke-width="1.5"/>
                </svg>
            </div>
            <div class="w-full py-2 bg-vttu-red text-white text-center font-bold text-[9px] uppercase tracking-wider rounded-sm group-hover:bg-vttu-dark transition-colors">
                Đổi mật khẩu tài khoản
            </div>
        </a>

        <!-- Card 5: Tra cứu sách giấy -->
        <a href="{{ url('opac') }}" class="group flex flex-col items-center p-3 bg-card border border-border rounded-md hover:bg-muted/50 active:scale-[0.98] transition-all duration-200">
            <div class="w-full h-32 flex items-center justify-center mb-3">
                <svg viewBox="0 0 120 100" class="w-24 h-24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="30" y="58" width="60" height="12" rx="1" fill="#EF4444" stroke="#DC2626" stroke-width="1"/>
                    <rect x="35" y="48" width="50" height="10" rx="1" fill="#10B981" stroke="#059669" stroke-width="1"/>
                    <rect x="32" y="38" width="56" height="10" rx="1" fill="#F59E0B" stroke="#D97706" stroke-width="1"/>
                    <path d="M40 32 C50 27 58 32 60 34 C62 32 70 27 80 32 V17 C70 12 62 17 60 19 C58 17 50 12 40 17 Z" fill="#FFFFFF" stroke="#475569" stroke-width="1.5"/>
                    <line x1="60" y1="19" x2="60" y2="34" stroke="#475569" stroke-width="1.5"/>
                    <circle cx="75" cy="42" r="14" fill="#3B82F6" fill-opacity="0.1" stroke="#3B82F6" stroke-width="3"/>
                    <line x1="85" y1="52" x2="100" y2="67" stroke="#3B82F6" stroke-width="4.5" stroke-linecap="round"/>
                </svg>
            </div>
            <div class="w-full py-2 bg-vttu-red text-white text-center font-bold text-[9px] uppercase tracking-wider rounded-sm group-hover:bg-vttu-dark transition-colors">
                Tra cứu tài liệu giấy
            </div>
        </a>

        <!-- Card 6: Tra cứu sách số -->
        <a href="{{ route('site.page', 'tai-lieu-so') }}" class="group flex flex-col items-center p-3 bg-card border border-border rounded-md hover:bg-muted/50 active:scale-[0.98] transition-all duration-200">
            <div class="w-full h-32 flex items-center justify-center mb-3">
                <svg viewBox="0 0 120 100" class="w-24 h-24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="25" y="15" width="70" height="46" rx="4" fill="#475569" stroke="#334155" stroke-width="1.5"/>
                    <rect x="29" y="19" width="62" height="38" rx="2" fill="#0F172A"/>
                    <path d="M15 61 H105 L110 75 H10 Z" fill="#E2E8F0"/>
                    <rect x="45" y="70" width="30" height="3" rx="1" fill="#94A3B8"/>
                    <path d="M45 42 C50 38 54 40 55 41 C56 40 60 38 65 42 V28 C60 24 56 26 55 27 C54 26 50 24 45 28 Z" fill="#3B82F6"/>
                    <line x1="55" y1="27" x2="55" y2="41" stroke="#0F172A" stroke-width="1"/>
                    <line x1="33" y1="23" x2="43" y2="23" stroke="#94A3B8" stroke-width="1.5" stroke-linecap="round"/>
                    <line x1="33" y1="28" x2="39" y2="28" stroke="#94A3B8" stroke-width="1.5" stroke-linecap="round"/>
                    <line x1="68" y1="23" x2="87" y2="23" stroke="#10B981" stroke-width="1.5" stroke-linecap="round"/>
                    <line x1="68" y1="28" x2="81" y2="28" stroke="#10B981" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
            </div>
            <div class="w-full py-2 bg-vttu-red text-white text-center font-bold text-[9px] uppercase tracking-wider rounded-sm group-hover:bg-vttu-dark transition-colors">
                Tra cứu tài liệu số
            </div>
        </a>

        <!-- Card 7: Mượn trước gia hạn -->
        <a href="/muon-truoc-gia-han" class="group flex flex-col items-center p-3 bg-card border border-border rounded-md hover:bg-muted/50 active:scale-[0.98] transition-all duration-200">
            <div class="w-full h-32 flex items-center justify-center mb-3">
                <svg viewBox="0 0 120 100" class="w-24 h-24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g transform="translate(15, 10)">
                        <rect x="10" y="45" width="60" height="20" rx="3" fill="#EF4444" stroke="#DC2626" stroke-width="1"/>
                        <text x="40" y="58" fill="#FFFFFF" font-family="sans-serif" font-weight="900" font-size="9" text-anchor="middle" letter-spacing="1">ONLINE</text>
                        
                        <rect x="20" y="25" width="60" height="20" rx="3" fill="#3B82F6" stroke="#2563EB" stroke-width="1"/>
                        <text x="50" y="38" fill="#FFFFFF" font-family="sans-serif" font-weight="900" font-size="9" text-anchor="middle" letter-spacing="1">ONLINE</text>
                        
                        <rect x="30" y="5" width="60" height="20" rx="3" fill="#10B981" stroke="#059669" stroke-width="1"/>
                        <text x="60" y="18" fill="#FFFFFF" font-family="sans-serif" font-weight="900" font-size="9" text-anchor="middle" letter-spacing="1">ONLINE</text>
                    </g>
                </svg>
            </div>
            <div class="w-full py-2 bg-vttu-red text-white text-center font-bold text-[9px] uppercase tracking-wider rounded-sm group-hover:bg-vttu-dark transition-colors">
                Mượn trước - Gia hạn
            </div>
        </a>

        <!-- Card 8: Đề nghị bổ sung -->
        <a href="/de-nghi-bo-sung" class="group flex flex-col items-center p-3 bg-card border border-border rounded-md hover:bg-muted/50 active:scale-[0.98] transition-all duration-200">
            <div class="w-full h-32 flex items-center justify-center mb-3">
                <svg viewBox="0 0 120 100" class="w-24 h-24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="25" y="15" width="18" height="70" rx="2" fill="#7B0000"/>
                    <rect x="29" y="15" width="3" height="70" fill="#FFD700" opacity="0.3"/>
                    <line x1="25" y1="25" x2="43" y2="25" stroke="#FFD700" stroke-width="2"/>
                    <line x1="25" y1="75" x2="43" y2="75" stroke="#FFD700" stroke-width="2"/>
                    
                    <rect x="47" y="10" width="22" height="75" rx="2" fill="#334155"/>
                    <circle cx="58" cy="25" r="4" fill="#FFD700"/>
                    <line x1="47" y1="75" x2="69" y2="75" stroke="#E2E8F0" stroke-width="1.5"/>
                    
                    <rect x="73" y="20" width="16" height="65" rx="2" fill="#10B981"/>
                    <line x1="73" y1="30" x2="89" y2="30" stroke="#FFFFFF" stroke-width="2"/>
                    <line x1="73" y1="75" x2="89" y2="75" stroke="#FFFFFF" stroke-width="2"/>
                    
                    <path d="M57 85 L57 93 L60 90 L63 93 L63 85 Z" fill="#FFD700"/>
                </svg>
            </div>
            <div class="w-full py-2 bg-vttu-red text-white text-center font-bold text-[9px] uppercase tracking-wider rounded-sm group-hover:bg-vttu-dark transition-colors">
                Đề nghị bổ sung tài liệu
            </div>
        </a>
    </div>
</div>
