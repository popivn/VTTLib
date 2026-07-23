@extends('layouts.admin')

@section('title', __('Báo cáo & Thống kê Tài liệu Số'))

@section('content')
<div class="w-full space-y-3 pb-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
        <div>
            <h1 class="text-lg font-bold text-foreground tracking-tight">{{ __('Báo cáo & Thống kê Tài liệu Số') }}</h1>
            <p class="text-xs text-muted-foreground mt-0.5">{{ __('Chọn loại báo cáo từ cây phân hệ bên trái, xem trước giao diện và xuất file dữ liệu.') }}</p>
        </div>
        <div class="flex items-center gap-2 w-full sm:w-auto">
            <button type="button" onclick="openHistoryModal()" class="btn-compact-secondary relative">
                <i data-lucide="history" class="w-4 h-4 mr-1"></i>
                <span>{{ __('Lịch sử xuất file') }}</span>
                <span id="history-badge" class="hidden absolute -top-1 -right-1 px-1.5 py-0.5 bg-red-600 text-white font-bold text-[9px] rounded-full"></span>
            </button>
            <a href="{{ route('admin.digital-resources.dashboard') }}" class="btn-compact-secondary">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i>
                <span>{{ __('Quay lại') }}</span>
            </a>
        </div>
    </div>

    <!-- Main Two-Column Layout -->
    <div class="flex flex-col lg:flex-row gap-3 w-full">
        
        <!-- Left Column: Report Tree Menu -->
        <div id="left-tree-column" class="w-full lg:w-80 xl:w-96 bg-card text-foreground rounded-md border border-border shadow-sm overflow-hidden flex flex-col shrink-0">
            <!-- Header bar with Collapse Button -->
            <div class="p-3 border-b border-border bg-muted/30 flex items-center justify-between font-bold shadow-sm">
                <div class="flex items-center gap-2">
                    <i data-lucide="folder-archive" class="w-4 h-4 text-muted-foreground"></i>
                    <span class="text-xs font-bold uppercase tracking-wider text-foreground">{{ __('BÁO CÁO PHÂN HỆ TÀI LIỆU SỐ') }}</span>
                </div>
                <button type="button" id="toggle-tree-btn" class="btn-icon-compact" title="{{ __('Thu gọn danh mục') }}">
                    <i data-lucide="chevron-left" class="w-4 h-4 text-muted-foreground"></i>
                </button>
            </div>
            
            <!-- Tree view area -->
            <div class="p-3 overflow-y-auto max-h-[600px] custom-scrollbar bg-card">
                <div class="tree-container">
                    @php
                    $isGroup1Active = in_array($reportType, [
                        'digital_qty_stats', 'most_viewed_by_period', 'most_downloaded_by_period', 
                        'digital_list', 'most_viewed', 'most_downloaded'
                    ]);
                    @endphp
                    <div class="tree-group">
                        <div class="tree-folder flex items-center py-1.5 px-1 hover:bg-muted/50 rounded cursor-pointer select-none" data-group="group-1">
                            <span class="toggle-icon-container mr-1 text-muted-foreground flex items-center justify-center">
                                <i data-lucide="square-minus" class="w-4 h-4 toggle-open {{ $isGroup1Active ? '' : 'hidden' }}"></i>
                                <i data-lucide="square-plus" class="w-4 h-4 toggle-closed {{ $isGroup1Active ? 'hidden' : '' }}"></i>
                            </span>
                            <span class="folder-icon-container mr-1.5 text-muted-foreground flex items-center justify-center">
                                <i data-lucide="folder-open" class="w-4 h-4 folder-open-icon {{ $isGroup1Active ? '' : 'hidden' }}"></i>
                                <i data-lucide="folder" class="w-4 h-4 folder-closed-icon {{ $isGroup1Active ? 'hidden' : '' }}"></i>
                            </span>
                            <span class="font-bold text-foreground text-xs">{{ __('Báo cáo phân hệ tài liệu số') }}</span>
                        </div>
                        
                        <div class="tree-node-parent {{ $isGroup1Active ? '' : 'hidden' }}" id="group-1">
                            <a href="{{ route('admin.digital.reports.index', ['report_type' => 'digital_qty_stats']) }}" 
                               class="tree-node-child tree-leaf flex items-center py-1 px-2 my-0.5 rounded border border-transparent hover:bg-muted {{ $reportType === 'digital_qty_stats' ? 'active-leaf bg-primary text-primary-foreground font-semibold' : 'text-muted-foreground' }}">
                                <span class="relative flex items-center mr-1.5">
                                    <span class="w-1.5 h-1.5 bg-primary {{ $reportType === 'digital_qty_stats' ? 'bg-primary-foreground' : 'invisible' }} rounded-full mr-2"></span>
                                    <i data-lucide="file-text" class="w-4 h-4 leaf-icon {{ $reportType === 'digital_qty_stats' ? 'text-primary-foreground' : 'text-muted-foreground' }}"></i>
                                </span>
                                <span class="text-xs">{{ __('Thống kê số lượng tài liệu số') }}</span>
                            </a>
                            
                            <a href="{{ route('admin.digital.reports.index', ['report_type' => 'most_viewed_by_period']) }}" 
                               class="tree-node-child tree-leaf flex items-center py-1 px-2 my-0.5 rounded border border-transparent hover:bg-muted {{ $reportType === 'most_viewed_by_period' ? 'active-leaf bg-primary text-primary-foreground font-semibold' : 'text-muted-foreground' }}">
                                <span class="relative flex items-center mr-1.5">
                                    <span class="w-1.5 h-1.5 bg-primary {{ $reportType === 'most_viewed_by_period' ? 'bg-primary-foreground' : 'invisible' }} rounded-full mr-2"></span>
                                    <i data-lucide="file-text" class="w-4 h-4 leaf-icon {{ $reportType === 'most_viewed_by_period' ? 'text-primary-foreground' : 'text-muted-foreground' }}"></i>
                                </span>
                                <span class="text-xs text-left leading-tight">{{ __('Danh sách tài liệu số xem nhiều theo thời gian') }}</span>
                            </a>
                            
                            <a href="{{ route('admin.digital.reports.index', ['report_type' => 'most_downloaded_by_period']) }}" 
                               class="tree-node-child tree-leaf flex items-center py-1 px-2 my-0.5 rounded border border-transparent hover:bg-muted {{ $reportType === 'most_downloaded_by_period' ? 'active-leaf bg-primary text-primary-foreground font-semibold' : 'text-muted-foreground' }}">
                                <span class="relative flex items-center mr-1.5">
                                    <span class="w-1.5 h-1.5 bg-primary {{ $reportType === 'most_downloaded_by_period' ? 'bg-primary-foreground' : 'invisible' }} rounded-full mr-2"></span>
                                    <i data-lucide="file-text" class="w-4 h-4 leaf-icon {{ $reportType === 'most_downloaded_by_period' ? 'text-primary-foreground' : 'text-muted-foreground' }}"></i>
                                </span>
                                <span class="text-xs text-left leading-tight">{{ __('Danh sách tài liệu số tải nhiều theo thời gian') }}</span>
                            </a>
                            
                            <a href="{{ route('admin.digital.reports.index', ['report_type' => 'digital_list']) }}" 
                               class="tree-node-child tree-leaf flex items-center py-1 px-2 my-0.5 rounded border border-transparent hover:bg-muted {{ $reportType === 'digital_list' ? 'active-leaf bg-primary text-primary-foreground font-semibold' : 'text-muted-foreground' }}">
                                <span class="relative flex items-center mr-1.5">
                                    <span class="w-1.5 h-1.5 bg-primary {{ $reportType === 'digital_list' ? 'bg-primary-foreground' : 'invisible' }} rounded-full mr-2"></span>
                                    <i data-lucide="file-text" class="w-4 h-4 leaf-icon {{ $reportType === 'digital_list' ? 'text-primary-foreground' : 'text-muted-foreground' }}"></i>
                                </span>
                                <span class="text-xs">{{ __('Danh sách tài liệu số trong thư viện') }}</span>
                            </a>

                            <a href="{{ route('admin.digital.reports.index', ['report_type' => 'most_viewed']) }}" 
                               class="tree-node-child tree-leaf flex items-center py-1 px-2 my-0.5 rounded border border-transparent hover:bg-muted {{ $reportType === 'most_viewed' ? 'active-leaf bg-primary text-primary-foreground font-semibold' : 'text-muted-foreground' }}">
                                <span class="relative flex items-center mr-1.5">
                                    <span class="w-1.5 h-1.5 bg-primary {{ $reportType === 'most_viewed' ? 'bg-primary-foreground' : 'invisible' }} rounded-full mr-2"></span>
                                    <i data-lucide="file-text" class="w-4 h-4 leaf-icon {{ $reportType === 'most_viewed' ? 'text-primary-foreground' : 'text-muted-foreground' }}"></i>
                                </span>
                                <span class="text-xs">{{ __('Danh sách tài liệu số xem nhiều') }}</span>
                            </a>

                            <a href="{{ route('admin.digital.reports.index', ['report_type' => 'most_downloaded']) }}" 
                               class="tree-node-child tree-leaf flex items-center py-1 px-2 my-0.5 rounded border border-transparent hover:bg-muted {{ $reportType === 'most_downloaded' ? 'active-leaf bg-primary text-primary-foreground font-semibold' : 'text-muted-foreground' }}">
                                <span class="relative flex items-center mr-1.5">
                                    <span class="w-1.5 h-1.5 bg-primary {{ $reportType === 'most_downloaded' ? 'bg-primary-foreground' : 'invisible' }} rounded-full mr-2"></span>
                                    <i data-lucide="file-text" class="w-4 h-4 leaf-icon {{ $reportType === 'most_downloaded' ? 'text-primary-foreground' : 'text-muted-foreground' }}"></i>
                                </span>
                                <span class="text-xs">{{ __('Danh sách tài liệu số tải nhiều') }}</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right Column: Filters and Actions Panel -->
        <div class="flex-1 min-w-0 bg-card text-foreground border border-border rounded-md shadow-sm flex flex-col min-h-[450px] relative space-y-3">
            <!-- Panel Header -->
            <div class="p-3 border-b border-border bg-muted/30 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 w-full">
                <div class="flex items-start gap-2 flex-1 min-w-0">
                    <button type="button" id="expand-tree-btn" class="btn-icon-compact hidden shrink-0" title="{{ __('Mở rộng danh mục') }}">
                        <i data-lucide="chevron-right" class="w-4 h-4 text-muted-foreground"></i>
                    </button>
                    <div class="flex-1 min-w-0">
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-sm text-[10px] font-bold bg-primary/10 text-primary uppercase tracking-widest mb-1">
                            {{ $activeReport['title'] }}
                        </span>
                        <h2 class="text-sm font-bold text-foreground tracking-tight">
                            {{ $activeReport['title'] }}
                        </h2>
                        <p class="text-xs text-muted-foreground mt-0.5 leading-relaxed">
                            {{ $activeReport['desc'] }}
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Filter Form -->
            <form id="reportForm" class="p-3 flex-1 flex flex-col justify-between space-y-3">
                @csrf
                <input type="hidden" name="report_type" id="selected_report_type" value="{{ $reportType }}">
                
                <div class="space-y-2 relative">
                    <!-- Simple Search Bar -->
                    <div class="flex items-center gap-2">
                        <div class="relative flex-1">
                            <input type="text" name="search" id="search_input" placeholder="{{ __('Tìm kiếm nhanh bằng từ khóa (Tiêu đề, Tác giả, Nhà xuất bản)...') }}" class="w-full h-9 pl-3 pr-10 text-xs border border-input rounded-sm bg-background text-foreground placeholder:text-muted-foreground/60 focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                            <button type="button" onclick="triggerPreview()" class="absolute right-1 top-1 h-7 w-7 flex items-center justify-center rounded-sm bg-primary text-primary-foreground hover:bg-primary/90 transition-colors">
                                <i data-lucide="search" class="w-4 h-4"></i>
                            </button>
                        </div>
                        <button type="button" id="toggle-advanced-btn" class="btn-compact-secondary">
                            <i data-lucide="sliders" class="w-4 h-4 mr-1"></i>
                            <span>{{ __('Bộ lọc nâng cao') }}</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 ml-1 transition-transform duration-200" id="advanced-chevron"></i>
                        </button>
                    </div>

                    <!-- Collapsible Advanced Search Panel -->
                    <div id="advanced-search-panel" class="hidden absolute left-0 right-0 top-full mt-1 z-30 border border-border rounded-md bg-card p-3 space-y-3 shadow-lg">
                        <div class="flex flex-wrap gap-1 border-b border-border pb-1">
                            <button type="button" class="tab-btn px-3 py-1 text-[10px] font-bold rounded-sm border border-transparent bg-muted/60 text-muted-foreground hover:bg-muted active-tab" data-tab="tab-dist">Thư mục & Trạng thái</button>
                            <button type="button" class="tab-btn px-3 py-1 text-[10px] font-bold rounded-sm border border-transparent bg-muted/60 text-muted-foreground hover:bg-muted" data-tab="tab-limit">Giới hạn & Ngày</button>
                        </div>

                        <div class="tab-contents min-h-[160px]">
                            <!-- Tab 1: Thư mục & Trạng thái -->
                            <div id="tab-dist" class="tab-content grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div class="space-y-1">
                                    <span class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest block border-b border-border pb-1">Trạng thái tài liệu</span>
                                    <div class="space-y-1">
                                        <label class="flex items-center text-xs text-foreground cursor-pointer select-none">
                                            <input type="checkbox" name="statuses[]" value="active" checked class="w-3.5 h-3.5 text-primary bg-background border-border rounded-sm">
                                            <span class="ml-1.5 text-[11px]">Đã duyệt / Khả dụng</span>
                                        </label>
                                        <label class="flex items-center text-xs text-foreground cursor-pointer select-none">
                                            <input type="checkbox" name="statuses[]" value="draft" class="w-3.5 h-3.5 text-primary bg-background border-border rounded-sm">
                                            <span class="ml-1.5 text-[11px]">Bản nháp / Chờ duyệt</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="space-y-1">
                                    <span class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest block border-b border-border pb-1">Thư mục tài liệu</span>
                                    <div class="max-h-40 overflow-y-auto custom-scrollbar space-y-1 pr-1">
                                        @foreach($folders as $f)
                                        <label class="flex items-center text-xs text-foreground cursor-pointer select-none">
                                            <input type="checkbox" name="folder_ids[]" value="{{ $f->id }}" class="w-3.5 h-3.5 text-primary bg-background border-border rounded-sm">
                                            <span class="ml-1.5 text-[11px]">{{ $f->name }}</span>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- Tab 2: Giới hạn & Ngày -->
                            <div id="tab-limit" class="tab-content hidden grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div class="space-y-1">
                                    <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest block">Khoảng ngày đăng / tạo</label>
                                    <div class="flex items-center gap-1">
                                        <input type="date" name="date_from" class="flex-1 h-8 px-2 text-xs border border-input rounded-sm bg-background text-foreground">
                                        <span class="text-[10px] text-muted-foreground">đến</span>
                                        <input type="date" name="date_to" class="flex-1 h-8 px-2 text-xs border border-input rounded-sm bg-background text-foreground">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end pt-1.5 border-t border-border mt-2">
                            <button type="button" class="close-advanced-btn flex items-center gap-1 text-[11px] font-bold text-muted-foreground hover:text-foreground">
                                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                <span>{{ __('Đóng') }}</span>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Format Toggle & Action Buttons -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-t border-border pt-3">
                    <div class="space-y-1">
                        <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest block">{{ __('Định dạng file xuất') }}</span>
                        <div class="inline-flex p-0.5 bg-muted rounded-sm border border-border shadow-inner">
                            <label class="relative group cursor-pointer">
                                <input type="radio" name="format" value="excel" checked class="sr-only peer">
                                <div class="px-3 py-1 text-xs font-bold rounded-sm transition-all peer-checked:bg-primary peer-checked:text-primary-foreground text-muted-foreground hover:text-foreground">
                                    <i class="fas fa-file-excel mr-1 text-xs"></i>Excel (.xlsx)
                                </div>
                            </label>
                            <label class="relative group cursor-pointer ml-1">
                                <input type="radio" name="format" value="csv" class="sr-only peer">
                                <div class="px-3 py-1 text-xs font-bold rounded-sm transition-all peer-checked:bg-primary peer-checked:text-primary-foreground text-muted-foreground hover:text-foreground">
                                    <i class="fas fa-file-csv mr-1 text-xs"></i>CSV (.csv)
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" onclick="triggerPreview()" class="btn-compact-secondary">
                            <i data-lucide="eye" class="w-4 h-4 mr-1"></i>
                            {{ __('XEM TRƯỚC') }}
                        </button>
                        <button type="button" onclick="triggerExport()" class="btn-compact-primary">
                            <i data-lucide="download" class="w-4 h-4 mr-1 text-primary-foreground"></i>
                            {{ __('XUẤT BÁO CÁO') }}
                        </button>
                    </div>
                </div>
            </form>

            <!-- Preview Container Area -->
            <div class="p-3 border-t border-border">
                <div id="preview-loading" class="hidden py-8 text-center text-muted-foreground">
                    <i data-lucide="loader-2" class="w-6 h-6 animate-spin mx-auto mb-2 text-primary"></i>
                    <p class="text-xs font-bold uppercase tracking-wider">{{ __('Đang tải bản xem trước báo cáo...') }}</p>
                </div>
                <div id="preview-content" class="w-full">
                    <!-- Default loaded preview -->
                </div>
            </div>
        </div>
        
    </div>
</div>

<!-- History Modal -->
<div id="historyModal" class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-card text-card-foreground border border-border rounded-lg shadow-xl w-full max-w-2xl max-h-[85vh] flex flex-col">
        <div class="p-4 border-b border-border flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i data-lucide="history" class="w-5 h-5 text-primary"></i>
                <h3 class="font-bold text-sm uppercase tracking-wider">{{ __('Lịch sử xuất báo cáo') }}</h3>
            </div>
            <button type="button" onclick="closeHistoryModal()" class="text-muted-foreground hover:text-foreground">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <div class="p-4 overflow-y-auto flex-1 custom-scrollbar" id="history-modal-body">
            <div class="text-center py-6 text-muted-foreground">
                <i data-lucide="loader-2" class="w-6 h-6 animate-spin mx-auto mb-2"></i>
                <p class="text-xs">{{ __('Đang nạp lịch sử...') }}</p>
            </div>
        </div>
        <div class="p-3 border-t border-border flex justify-between items-center bg-muted/20">
            <button type="button" onclick="clearHistory()" class="text-xs text-red-600 hover:underline font-bold">
                {{ __('Xóa lịch sử đã hoàn tất') }}
            </button>
            <button type="button" onclick="closeHistoryModal()" class="btn-compact-secondary">
                {{ __('Đóng') }}
            </button>
        </div>
    </div>
</div>

@push('styles')
<style>
    .tree-node-parent { position: relative; padding-left: 12px; }
    .tree-node-parent::before { content: ""; position: absolute; left: 8px; top: 0px; bottom: 12px; border-left: 1.5px dashed hsl(var(--border)); }
    .tree-node-child { position: relative; padding-left: 16px; }
    .tree-node-child::before { content: ""; position: absolute; left: -4px; top: 14px; width: 14px; border-top: 1.5px dashed hsl(var(--border)); }
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: hsl(var(--border)); border-radius: 2px; }
    .active-tab { background-color: hsl(var(--card)) !important; border-color: hsl(var(--border)) !important; color: hsl(var(--foreground)) !important; }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle tree
        document.getElementById('toggle-tree-btn')?.addEventListener('click', function() {
            document.getElementById('left-tree-column').style.display = 'none';
            document.getElementById('expand-tree-btn').classList.remove('hidden');
        });
        document.getElementById('expand-tree-btn')?.addEventListener('click', function() {
            document.getElementById('left-tree-column').style.display = 'flex';
            document.getElementById('expand-tree-btn').classList.add('hidden');
        });

        // Advanced filter toggle
        document.getElementById('toggle-advanced-btn')?.addEventListener('click', function() {
            const panel = document.getElementById('advanced-search-panel');
            panel.classList.toggle('hidden');
        });
        document.querySelector('.close-advanced-btn')?.addEventListener('click', function() {
            document.getElementById('advanced-search-panel').classList.add('hidden');
        });

        // Tab switcher
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active-tab'));
                this.classList.add('active-tab');
                document.querySelectorAll('.tab-content').forEach(c => c.classList.add('hidden'));
                document.getElementById(this.dataset.tab).classList.remove('hidden');
            });
        });

        // Auto trigger preview on initial page load
        triggerPreview();

        // Check background history polling
        fetchHistory();
        setInterval(fetchHistory, 5000);
    });

    function triggerPreview() {
        const form = document.getElementById('reportForm');
        const formData = new FormData(form);
        
        document.getElementById('preview-loading').classList.remove('hidden');
        document.getElementById('preview-content').classList.add('opacity-40');

        fetch('{{ route("admin.digital.reports.preview") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(r => r.text())
        .then(html => {
            document.getElementById('preview-loading').classList.add('hidden');
            document.getElementById('preview-content').classList.remove('opacity-40');
            document.getElementById('preview-content').innerHTML = html;
        })
        .catch(err => {
            document.getElementById('preview-loading').classList.add('hidden');
            document.getElementById('preview-content').classList.remove('opacity-40');
        });
    }

    function triggerExport() {
        const form = document.getElementById('reportForm');
        const formData = new FormData(form);

        fetch('{{ route("admin.digital.reports.generate") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                openHistoryModal();
                fetchHistory();
            } else {
                alert(res.message || 'Lỗi tạo yêu cầu xuất báo cáo');
            }
        })
        .catch(err => alert('Lỗi kết nối server'));
    }

    function openHistoryModal() {
        document.getElementById('historyModal').classList.remove('hidden');
        fetchHistory();
    }

    function closeHistoryModal() {
        document.getElementById('historyModal').classList.add('hidden');
    }

    function fetchHistory() {
        fetch('{{ route("admin.digital.reports.history") }}')
        .then(r => r.json())
        .then(data => {
            if (!data.success) return;
            const histories = data.histories || [];
            const badge = document.getElementById('history-badge');
            
            if (data.unread_count > 0) {
                badge.innerText = data.unread_count;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }

            const body = document.getElementById('history-modal-body');
            if (!body) return;

            if (histories.length === 0) {
                body.innerHTML = '<p class="text-center py-6 text-xs text-muted-foreground">Chưa có lịch sử xuất báo cáo nào.</p>';
                return;
            }

            let html = '<div class="space-y-2">';
            histories.forEach(h => {
                let statusBadge = '';
                if (h.status === 'completed') {
                    statusBadge = `<span class="px-2 py-0.5 bg-green-100 text-green-700 rounded text-[10px] font-bold">Hoàn tất (100%)</span>`;
                } else if (h.status === 'processing' || h.status === 'pending') {
                    statusBadge = `<span class="px-2 py-0.5 bg-amber-100 text-amber-700 rounded text-[10px] font-bold animate-pulse">Đang xử lý... (${h.progress || 0}%)</span>`;
                } else {
                    statusBadge = `<span class="px-2 py-0.5 bg-red-100 text-red-700 rounded text-[10px] font-bold">Lỗi</span>`;
                }

                let downloadBtn = '';
                if (h.status === 'completed') {
                    downloadBtn = `<a href="/topsecret/digital-reports/history/${h.id}/download" class="btn-compact-primary text-xs"><i class="fas fa-download mr-1"></i> Tải về</a>`;
                }

                html += `
                <div class="p-3 border border-border rounded-md bg-background flex items-center justify-between gap-3">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-xs text-foreground">${h.title}</span>
                            ${statusBadge}
                        </div>
                        <p class="text-[11px] text-muted-foreground">${h.filename} • ${h.created_at || ''}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        ${downloadBtn}
                        <button type="button" onclick="deleteHistory(${h.id})" class="text-xs text-muted-foreground hover:text-red-600"><i class="fas fa-trash"></i></button>
                    </div>
                </div>`;
            });
            html += '</div>';
            body.innerHTML = html;
        });
    }

    function deleteHistory(id) {
        fetch(`/topsecret/digital-reports/history/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        }).then(() => fetchHistory());
    }

    function clearHistory() {
        fetch('/topsecret/digital-reports/history/clear', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        }).then(() => fetchHistory());
    }

    function resetForm() {
        document.getElementById('search_input').value = '';
        document.querySelectorAll('input[type="checkbox"]').forEach(c => c.checked = false);
        document.querySelectorAll('input[type="date"]').forEach(d => d.value = '');
        triggerPreview();
    }
</script>
@endpush
@endsection
