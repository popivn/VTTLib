@extends('layouts.site')

@section('title', $node->meta_title ?: 'Khung chương trình đào tạo - Thư viện VTTU')

@section('meta-description', $node->meta_description ?: 'Khung chương trình đào tạo chi tiết các ngành Y khoa, Răng - Hàm - Mặt, Dược học, Điều dưỡng, KTXN Y học, CNTT, QTKD tại Trường Đại học Võ Trường Toản.')

@section('content')
<div class="min-h-screen bg-slate-50/50 pt-24 pb-16">
    <!-- Header Hero Banner -->
    <div class="bg-gradient-to-r from-vttu-dark via-[#7a0606] to-vttu-red text-white py-12 px-4 relative overflow-hidden shadow-lg mb-8">
        <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#fff_1px,transparent_1px)] [background-size:16px_16px] pointer-events-none"></div>
        <div class="max-w-7xl mx-auto relative z-10">
            <!-- Breadcrumb -->
            <nav class="mb-4">
                <ol class="flex items-center space-x-2 text-xs font-semibold text-white/80">
                    <li><a href="{{ route('home') }}" class="hover:text-vttu-yellow transition-colors">Trang chủ</a></li>
                    <li><i class="fas fa-chevron-right text-[9px] opacity-60"></i></li>
                    <li><a href="{{ route('site.page', 'gioi-thieu') }}" class="hover:text-vttu-yellow transition-colors">Giới thiệu</a></li>
                    <li><i class="fas fa-chevron-right text-[9px] opacity-60"></i></li>
                    <li class="text-vttu-yellow font-bold">Khung chương trình đào tạo</li>
                </ol>
            </nav>

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <span class="px-3 py-1 bg-white/10 text-vttu-yellow text-[10px] font-black uppercase tracking-widest rounded-full border border-white/10 inline-block mb-3">
                        <i class="fas fa-graduation-cap mr-1"></i> Chương trình Đào tạo Đại học
                    </span>
                    <h1 class="text-2xl md:text-4xl font-black text-white tracking-tight uppercase">
                        Khung Chương Trình Đào Tạo
                    </h1>
                    <p class="text-xs md:text-sm text-white/80 mt-2 max-w-3xl font-medium leading-relaxed">
                        Cung cấp thông tin tổng quan, chuẩn đầu ra và khung chương trình đào tạo chi tiết của các ngành đào tạo trọng điểm tại Trường Đại học Võ Trường Toản.
                    </p>
                </div>

                <div class="flex-shrink-0">
                    <a href="{{ route('site.proposal.store') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-vttu-yellow hover:bg-yellow-400 text-vttu-dark text-xs font-black rounded-md shadow-md transition-all active:scale-95 uppercase tracking-wider">
                        <i class="fas fa-book mr-1"></i> Đề nghị bổ sung giáo trình
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Container -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- Left Sidebar Navigation -->
            <div class="lg:col-span-3 space-y-6">
                <!-- Majors Quick Access -->
                <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-200/80 sticky top-28">
                    <h3 class="text-xs font-black text-vttu-dark uppercase tracking-wider border-b border-slate-100 pb-3 mb-3 flex items-center gap-2">
                        <i class="fas fa-university text-vttu-red"></i>
                        <span>Ngành Đào Tạo</span>
                    </h3>
                    
                    <div class="space-y-1.5 text-xs font-bold">
                        <a href="#nganh-y-khoa" class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-700 hover:bg-vttu-red/5 hover:text-vttu-red transition-all">
                            <i class="fas fa-user-md text-vttu-red w-4"></i>
                            <span>Y Khoa (Bác sĩ)</span>
                        </a>
                        <a href="#nganh-rang-ham-mat" class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-700 hover:bg-vttu-red/5 hover:text-vttu-red transition-all">
                            <i class="fas fa-tooth text-vttu-red w-4"></i>
                            <span>Răng - Hàm - Mặt</span>
                        </a>
                        <a href="#nganh-duoc-hoc" class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-700 hover:bg-vttu-red/5 hover:text-vttu-red transition-all">
                            <i class="fas fa-pills text-vttu-red w-4"></i>
                            <span>Dược Học (Dược sĩ)</span>
                        </a>
                        <a href="#nganh-dieu-duong" class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-700 hover:bg-vttu-red/5 hover:text-vttu-red transition-all">
                            <i class="fas fa-user-nurse text-vttu-red w-4"></i>
                            <span>Điều Dưỡng</span>
                        </a>
                        <a href="#nganh-xet-nghiem" class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-700 hover:bg-vttu-red/5 hover:text-vttu-red transition-all">
                            <i class="fas fa-vial text-vttu-red w-4"></i>
                            <span>KTXN Y Học</span>
                        </a>
                        <a href="#nganh-cntt" class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-700 hover:bg-vttu-red/5 hover:text-vttu-red transition-all">
                            <i class="fas fa-laptop-code text-vttu-red w-4"></i>
                            <span>Công Nghệ Thông Tin</span>
                        </a>
                        <a href="#nganh-qtkd" class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-700 hover:bg-vttu-red/5 hover:text-vttu-red transition-all">
                            <i class="fas fa-chart-line text-vttu-red w-4"></i>
                            <span>Quản Trị Kinh Doanh</span>
                        </a>
                    </div>

                    <!-- Related Pages in Giới Thiệu -->
                    <div class="mt-6 pt-5 border-t border-slate-100">
                        <h4 class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-3">Mục liên quan</h4>
                        <div class="space-y-2 text-xs">
                            <a href="{{ route('site.page', 'gioi-thieu-chung') }}" class="block text-slate-600 hover:text-vttu-red font-medium transition-colors">
                                <i class="fas fa-chevron-right text-[8px] mr-1.5 opacity-40"></i> Giới thiệu chung
                            </a>
                            <a href="{{ route('site.page', 'chuc-nang-nhiem-vu') }}" class="block text-slate-600 hover:text-vttu-red font-medium transition-colors">
                                <i class="fas fa-chevron-right text-[8px] mr-1.5 opacity-40"></i> Chức năng nhiệm vụ
                            </a>
                            <a href="{{ route('site.page', 'noi-quy-thu-vien') }}" class="block text-slate-600 hover:text-vttu-red font-medium transition-colors">
                                <i class="fas fa-chevron-right text-[8px] mr-1.5 opacity-40"></i> Nội quy Thư viện
                            </a>
                            <a href="{{ route('site.page', 'thoi-gian-phuc-vu') }}" class="block text-slate-600 hover:text-vttu-red font-medium transition-colors">
                                <i class="fas fa-chevron-right text-[8px] mr-1.5 opacity-40"></i> Thời gian phục vụ
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Container -->
            <div class="lg:col-span-9">
                <div class="bg-white rounded-xl p-6 md:p-8 shadow-sm border border-slate-200/80">
                    <div class="prose max-w-none prose-slate prose-headings:font-black prose-headings:text-vttu-dark prose-a:text-vttu-red hover:prose-a:underline">
                        {!! $node->content !!}
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
