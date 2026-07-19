<div class="space-y-4 animate-fade-in">
    <!-- Intro Card (Golden Ratio Banner) -->
    <div class="bg-white border border-slate-100 border-l-4 border-l-vttu-red rounded-lg p-4 shadow-sm">
        <div class="flex items-start gap-3">
            <div class="w-8 h-8 rounded-full bg-vttu-red/10 flex items-center justify-center text-vttu-red flex-shrink-0 mt-0.5">
                <i class="fas fa-poll text-sm"></i>
            </div>
            <div>
                <h3 class="text-xs font-black text-vttu-dark uppercase tracking-wider mb-1">PHIẾU KHẢO SÁT VÀ ĐÓNG GÓP Ý KIẾN BẠN ĐỌC</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Ý kiến đóng góp quý báu của Quý bạn đọc giúp Thư viện Võ Trường Toản liên tục nâng cao chất lượng dịch vụ, nguồn học liệu và cơ sở vật chất. Mọi thông tin phản hồi sẽ được xử lý bảo mật.
                </p>
            </div>
        </div>
    </div>

    <!-- Feedback Alerts -->
    @if(session('success'))
        <div class="flex items-center gap-2.5 p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg text-xs font-medium shadow-sm">
            <i class="fas fa-check-circle text-emerald-500 text-sm flex-shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="p-3.5 bg-red-50 border border-red-200 text-red-700 rounded-lg text-xs space-y-1 shadow-sm">
            <div class="flex items-center gap-2 font-bold">
                <i class="fas fa-exclamation-circle text-red-500 text-sm flex-shrink-0"></i>
                <span>Vui lòng kiểm tra lại thông tin bên dưới:</span>
            </div>
            <ul class="list-disc list-inside pl-2 text-[11px] space-y-0.5 text-red-600">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Survey Form Card -->
    <form action="{{ route('site.survey.store') }}" method="POST" class="bg-white border border-slate-100 rounded-lg p-5 shadow-sm space-y-5">
        @csrf

        <!-- SECTION 1: Thông tin bạn đọc -->
        <div class="space-y-3 pb-4 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-slate-100 text-vttu-red text-xs font-black flex items-center justify-center">1</span>
                <h3 class="text-xs font-black text-vttu-dark uppercase tracking-wider">Thông tin người thực hiện khảo sát</h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3.5 pt-1">
                <!-- Họ và tên -->
                <div>
                    <label for="full_name" class="block text-[11px] font-bold text-slate-700 mb-1.5 uppercase tracking-wide">
                        Họ và tên
                    </label>
                    <input type="text" 
                           id="full_name" 
                           name="full_name" 
                           class="w-full h-9 px-3 text-xs border border-slate-200 bg-slate-50/50 focus:bg-white text-slate-800 rounded focus:border-vttu-red focus:ring-1 focus:ring-vttu-red/30 outline-none transition-all" 
                           placeholder="Họ và tên (không bắt buộc)"
                           value="{{ auth()->check() ? auth()->user()->name : old('full_name') }}">
                </div>

                <!-- Mã thẻ / MSSV -->
                <div>
                    <label for="card_number" class="block text-[11px] font-bold text-slate-700 mb-1.5 uppercase tracking-wide">
                        Mã thẻ / MSSV / MSGV
                    </label>
                    <input type="text" 
                           id="card_number" 
                           name="card_number" 
                           class="w-full h-9 px-3 text-xs border border-slate-200 bg-slate-50/50 focus:bg-white text-slate-800 rounded focus:border-vttu-red focus:ring-1 focus:ring-vttu-red/30 outline-none transition-all" 
                           placeholder="Nhập mã thẻ hoặc MSSV"
                           value="{{ auth()->check() ? (auth()->user()->username ?? '') : old('card_number') }}">
                </div>

                <!-- Email / SĐT -->
                <div>
                    <label for="email_phone" class="block text-[11px] font-bold text-slate-700 mb-1.5 uppercase tracking-wide">
                        Email / SĐT liên hệ <span class="text-vttu-red">*</span>
                    </label>
                    <input type="text" 
                           id="email_phone" 
                           name="email_phone" 
                           required 
                           class="w-full h-9 px-3 text-xs border border-slate-200 bg-slate-50/50 focus:bg-white text-slate-800 rounded focus:border-vttu-red focus:ring-1 focus:ring-vttu-red/30 outline-none transition-all" 
                           placeholder="Nhập Email hoặc Số điện thoại"
                           value="{{ auth()->check() ? (auth()->user()->email ?? auth()->user()->username) : old('email_phone') }}">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 pt-1">
                <!-- Đối tượng bạn đọc -->
                <div>
                    <label for="patron_group" class="block text-[11px] font-bold text-slate-700 mb-1.5 uppercase tracking-wide">
                        Đối tượng bạn đọc <span class="text-vttu-red">*</span>
                    </label>
                    <select id="patron_group" 
                            name="patron_group" 
                            required 
                            class="w-full h-9 px-3 text-xs border border-slate-200 bg-slate-50/50 focus:bg-white text-slate-800 rounded focus:border-vttu-red focus:ring-1 focus:ring-vttu-red/30 outline-none transition-all">
                        <option value="Sinh viên" {{ old('patron_group') == 'Sinh viên' ? 'selected' : '' }}>Sinh viên VTTU</option>
                        <option value="Giảng viên" {{ old('patron_group') == 'Giảng viên' ? 'selected' : '' }}>Giảng viên / Nghiên cứu viên</option>
                        <option value="Cán bộ nhân viên" {{ old('patron_group') == 'Cán bộ nhân viên' ? 'selected' : '' }}>Cán bộ / Nhân viên VTTU</option>
                        <option value="Khác" {{ old('patron_group') == 'Khác' ? 'selected' : '' }}>Bạn đọc ngoài trường</option>
                    </select>
                </div>

                <!-- Chủ đề đóng góp -->
                <div>
                    <label for="survey_category" class="block text-[11px] font-bold text-slate-700 mb-1.5 uppercase tracking-wide">
                        Chủ đề ý kiến đóng góp <span class="text-vttu-red">*</span>
                    </label>
                    <select id="survey_category" 
                            name="survey_category" 
                            required 
                            class="w-full h-9 px-3 text-xs border border-slate-200 bg-slate-50/50 focus:bg-white text-slate-800 rounded focus:border-vttu-red focus:ring-1 focus:ring-vttu-red/30 outline-none transition-all">
                        <option value="Dịch vụ mượn trả & Thái độ phục vụ" {{ old('survey_category') == 'Dịch vụ mượn trả & Thái độ phục vụ' ? 'selected' : '' }}>Dịch vụ mượn trả & Thái độ phục vụ</option>
                        <option value="Tài liệu số & Tra cứu trực tuyến" {{ old('survey_category') == 'Tài liệu số & Tra cứu trực tuyến' ? 'selected' : '' }}>Tài liệu số & Tra cứu trực tuyến (OPAC)</option>
                        <option value="Vốn tài liệu & Học liệu" {{ old('survey_category') == 'Vốn tài liệu & Học liệu' ? 'selected' : '' }}>Vốn tài liệu & Giáo trình học liệu</option>
                        <option value="Không gian & Cơ sở vật chất" {{ old('survey_category') == 'Không gian & Cơ sở vật chất' ? 'selected' : '' }}>Không gian đọc & Cơ sở vật chất</option>
                        <option value="Góp ý chung" {{ old('survey_category') == 'Góp ý chung' || !old('survey_category') ? 'selected' : '' }}>Góp ý chung khác</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- SECTION 2: Đánh giá chất lượng (Rating Stars) -->
        <div class="space-y-3 pb-4 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-slate-100 text-vttu-red text-xs font-black flex items-center justify-center">2</span>
                <h3 class="text-xs font-black text-vttu-dark uppercase tracking-wider">Đánh giá mức độ hài lòng (Thang điểm 1 - 5 Sao)</h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 pt-1">
                <!-- Rating 1: Service -->
                <div class="p-3 bg-slate-50/80 border border-slate-200/80 rounded-md flex flex-col justify-between hover:border-amber-300 transition-colors" x-data="{ val: {{ old('rating_service', 5) }} }">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-xs font-bold text-slate-700">1. Thái độ & Dịch vụ phục vụ</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-amber-100 text-amber-800" x-text="val + '/5 Sao'"></span>
                    </div>
                    <div class="flex items-center gap-2">
                        @for($i = 1; $i <= 5; $i++)
                            <label class="cursor-pointer group flex items-center">
                                <input type="radio" name="rating_service" value="{{ $i }}" @click="val = {{ $i }}" class="sr-only" {{ old('rating_service', 5) == $i ? 'checked' : '' }}>
                                <i class="fas fa-star text-base transition-transform group-hover:scale-125" :class="val >= {{ $i }} ? 'text-amber-400' : 'text-slate-300'"></i>
                            </label>
                        @endfor
                    </div>
                </div>

                <!-- Rating 2: Resource -->
                <div class="p-3 bg-slate-50/80 border border-slate-200/80 rounded-md flex flex-col justify-between hover:border-amber-300 transition-colors" x-data="{ val: {{ old('rating_resource', 5) }} }">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-xs font-bold text-slate-700">2. Vốn tài liệu / Học liệu</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-amber-100 text-amber-800" x-text="val + '/5 Sao'"></span>
                    </div>
                    <div class="flex items-center gap-2">
                        @for($i = 1; $i <= 5; $i++)
                            <label class="cursor-pointer group flex items-center">
                                <input type="radio" name="rating_resource" value="{{ $i }}" @click="val = {{ $i }}" class="sr-only" {{ old('rating_resource', 5) == $i ? 'checked' : '' }}>
                                <i class="fas fa-star text-base transition-transform group-hover:scale-125" :class="val >= {{ $i }} ? 'text-amber-400' : 'text-slate-300'"></i>
                            </label>
                        @endfor
                    </div>
                </div>

                <!-- Rating 3: Facility -->
                <div class="p-3 bg-slate-50/80 border border-slate-200/80 rounded-md flex flex-col justify-between hover:border-amber-300 transition-colors" x-data="{ val: {{ old('rating_facility', 5) }} }">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-xs font-bold text-slate-700">3. Cơ sở vật chất & Không gian đọc</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-amber-100 text-amber-800" x-text="val + '/5 Sao'"></span>
                    </div>
                    <div class="flex items-center gap-2">
                        @for($i = 1; $i <= 5; $i++)
                            <label class="cursor-pointer group flex items-center">
                                <input type="radio" name="rating_facility" value="{{ $i }}" @click="val = {{ $i }}" class="sr-only" {{ old('rating_facility', 5) == $i ? 'checked' : '' }}>
                                <i class="fas fa-star text-base transition-transform group-hover:scale-125" :class="val >= {{ $i }} ? 'text-amber-400' : 'text-slate-300'"></i>
                            </label>
                        @endfor
                    </div>
                </div>

                <!-- Rating 4: Overall -->
                <div class="p-3 bg-amber-50/50 border border-amber-200/80 rounded-md flex flex-col justify-between hover:border-amber-400 transition-colors" x-data="{ val: {{ old('rating_overall', 5) }} }">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-xs font-bold text-amber-900">4. Mức độ hài lòng chung <span class="text-vttu-red">*</span></span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-amber-200 text-amber-900" x-text="val + '/5 Sao'"></span>
                    </div>
                    <div class="flex items-center gap-2">
                        @for($i = 1; $i <= 5; $i++)
                            <label class="cursor-pointer group flex items-center">
                                <input type="radio" name="rating_overall" value="{{ $i }}" required @click="val = {{ $i }}" class="sr-only" {{ old('rating_overall', 5) == $i ? 'checked' : '' }}>
                                <i class="fas fa-star text-base transition-transform group-hover:scale-125" :class="val >= {{ $i }} ? 'text-amber-500' : 'text-slate-300'"></i>
                            </label>
                        @endfor
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 3: Nội dung ý kiến -->
        <div class="space-y-3">
            <div class="flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-slate-100 text-vttu-red text-xs font-black flex items-center justify-center">3</span>
                <h3 class="text-xs font-black text-vttu-dark uppercase tracking-wider">Nội dung đóng góp ý kiến & Đề xuất <span class="text-vttu-red">*</span></h3>
            </div>

            <textarea id="content" 
                      name="content" 
                      rows="4" 
                      required 
                      class="w-full p-3 text-xs border border-slate-200 bg-slate-50/50 focus:bg-white text-slate-800 rounded focus:border-vttu-red focus:ring-1 focus:ring-vttu-red/30 outline-none transition-all leading-relaxed" 
                      placeholder="Vui lòng nhập chi tiết nội dung phản hồi, ý kiến đóng góp hoặc nhu cầu cải tiến của Quý bạn đọc dành cho Thư viện VTTU...">{{ old('content') }}</textarea>
        </div>

        <!-- Submit Button -->
        <div class="pt-2 flex justify-end">
            <button type="submit" 
                    class="px-6 py-2.5 bg-gradient-to-r from-vttu-red to-vttu-dark hover:from-vttu-dark hover:to-vttu-red text-white font-bold text-xs uppercase tracking-wider rounded shadow-md hover:shadow-lg transition-all flex items-center gap-2 cursor-pointer">
                <i class="fas fa-paper-plane text-xs"></i>
                Gửi ý kiến khảo sát
            </button>
        </div>
    </form>
</div>
