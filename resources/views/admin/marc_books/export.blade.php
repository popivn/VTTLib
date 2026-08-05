@extends('layouts.admin')

@section('title', __('MARC Records Export & Reports'))

@section('content')
<div class="w-full space-y-3 pb-4">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
        <div>
            <h1 class="text-lg font-bold text-foreground tracking-tight">{{ __('Báo cáo & Xuất bản ghi MARC') }}</h1>
            <p class="text-xs text-muted-foreground mt-0.5">{{ __('Chọn loại báo cáo từ cây phân hệ bên trái và cấu hình bộ lọc tương ứng bên phải.') }}</p>
        </div>
        <div class="flex items-center gap-2 w-full sm:w-auto">
            <a href="{{ route('admin.marc.import.index') }}" class="btn-compact-secondary">
                <i data-lucide="file-input" class="w-4 h-4 mr-1"></i>
                <span>{{ __('Nhập liệu') }}</span>
            </a>
            <a href="{{ route('admin.marc.book') }}" class="btn-compact-secondary">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i>
                <span>{{ __('Quay lại') }}</span>
            </a>
        </div>
    </div>

    <!-- Main Two-Column Layout -->
    <div class="flex flex-col lg:flex-row gap-3 w-full">
        
        <!-- Left Column: Report Tree Menu -->
        <div id="left-tree-column" class="w-full lg:w-80 xl:w-96 bg-card text-foreground rounded-md border border-border shadow-sm overflow-hidden flex flex-col shrink-0">
            <!-- Header bar matching layout admin with Collapse Button -->
            <div class="p-3 border-b border-border bg-muted/30 flex items-center justify-between font-bold shadow-sm">
                <div class="flex items-center gap-2">
                    <i data-lucide="bar-chart-2" class="w-4 h-4 text-muted-foreground"></i>
                    <span class="text-xs font-bold uppercase tracking-wider text-foreground">{{ __('BÁO CÁO PHÂN HỆ BIÊN MỤC') }}</span>
                </div>
                <button type="button" id="toggle-tree-btn" class="btn-icon-compact" title="{{ __('Thu gọn danh mục') }}">
                    <i data-lucide="chevron-left" class="w-4 h-4 text-muted-foreground"></i>
                </button>
            </div>
            
            <!-- Tree view area -->
            <div class="p-3 overflow-y-auto max-h-[600px] custom-scrollbar bg-card">
                <div class="tree-container">
                    
                    <!-- Group 1: Báo cáo phân hệ biên mục -->
                    @php
                    $isGroup1Active = in_array($reportType, ['cataloging_subsystem', 'article_index', 'book_stats', 'book_id_list']);
                    @endphp
                    <div class="tree-group">
                        <div class="tree-folder flex items-center py-1.5 px-1 hover:bg-muted/50 rounded cursor-pointer select-none {{ $reportType === 'cataloging_subsystem' ? 'active-leaf bg-primary text-primary-foreground font-semibold' : '' }}" data-group="group-1">
                            <span class="toggle-icon-container mr-1 {{ $reportType === 'cataloging_subsystem' ? 'text-primary-foreground' : 'text-muted-foreground' }} flex items-center justify-center">
                                <i data-lucide="square-minus" class="w-4 h-4 toggle-open {{ $isGroup1Active ? '' : 'hidden' }}"></i>
                                <i data-lucide="square-plus" class="w-4 h-4 toggle-closed {{ $isGroup1Active ? 'hidden' : '' }}"></i>
                            </span>
                            <a href="{{ route('admin.marc.reports.index', ['report_type' => 'cataloging_subsystem']) }}" class="flex items-center flex-1">
                                <span class="folder-icon-container mr-1.5 {{ $reportType === 'cataloging_subsystem' ? 'text-primary-foreground' : 'text-muted-foreground' }} flex items-center justify-center">
                                    <i data-lucide="folder-open" class="w-4 h-4 folder-open-icon {{ $isGroup1Active ? '' : 'hidden' }}"></i>
                                    <i data-lucide="folder" class="w-4 h-4 folder-closed-icon {{ $isGroup1Active ? 'hidden' : '' }}"></i>
                                </span>
                                <span class="font-bold text-xs {{ $reportType === 'cataloging_subsystem' ? 'text-primary-foreground' : 'text-foreground' }}">{{ __('Báo cáo phân hệ biên mục') }}</span>
                            </a>
                        </div>
                        
                        <!-- Group 1 children (with tree lines) -->
                        <div class="tree-node-parent {{ $isGroup1Active ? '' : 'hidden' }}" id="group-1">
                            
                            <a href="{{ route('admin.marc.reports.index', ['report_type' => 'article_index']) }}" 
                               class="tree-node-child tree-leaf flex items-center py-1 px-2 my-0.5 rounded border border-transparent hover:bg-muted {{ $reportType === 'article_index' ? 'active-leaf bg-primary text-primary-foreground font-semibold' : 'text-muted-foreground' }}">
                                <span class="relative flex items-center mr-1.5">
                                    <span class="w-1.5 h-1.5 bg-primary {{ $reportType === 'article_index' ? 'bg-primary-foreground' : 'invisible' }} rounded-full mr-2"></span>
                                    <i data-lucide="file-text" class="w-4 h-4 leaf-icon {{ $reportType === 'article_index' ? 'text-primary-foreground' : 'text-muted-foreground' }}"></i>
                                </span>
                                <span class="text-xs">{{ __('Thư mục bài trích') }}</span>
                            </a>
                            
                            <a href="{{ route('admin.marc.reports.index', ['report_type' => 'book_stats']) }}" 
                               class="tree-node-child tree-leaf flex items-center py-1 px-2 my-0.5 rounded border border-transparent hover:bg-muted {{ $reportType === 'book_stats' ? 'active-leaf bg-primary text-primary-foreground font-semibold' : 'text-muted-foreground' }}">
                                <span class="relative flex items-center mr-1.5">
                                    <span class="w-1.5 h-1.5 bg-primary {{ $reportType === 'book_stats' ? 'bg-primary-foreground' : 'invisible' }} rounded-full mr-2"></span>
                                    <i data-lucide="file-text" class="w-4 h-4 leaf-icon {{ $reportType === 'book_stats' ? 'text-primary-foreground' : 'text-muted-foreground' }}"></i>
                                </span>
                                <span class="text-xs">{{ __('Thống kê số lượng đầu sách') }}</span>
                            </a>
                            
                            <a href="{{ route('admin.marc.reports.index', ['report_type' => 'book_id_list']) }}" 
                               class="tree-node-child tree-leaf flex items-center py-1 px-2 my-0.5 rounded border border-transparent hover:bg-muted {{ $reportType === 'book_id_list' ? 'active-leaf bg-primary text-primary-foreground font-semibold' : 'text-muted-foreground' }}">
                                <span class="relative flex items-center mr-1.5">
                                    <span class="w-1.5 h-1.5 bg-primary {{ $reportType === 'book_id_list' ? 'bg-primary-foreground' : 'invisible' }} rounded-full mr-2"></span>
                                    <i data-lucide="file-text" class="w-4 h-4 leaf-icon {{ $reportType === 'book_id_list' ? 'text-primary-foreground' : 'text-muted-foreground' }}"></i>
                                </span>
                                <span class="text-xs text-left leading-tight">{{ __('Danh sách tài liệu theo mã sách') }}</span>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Group 2: Báo cáo tài liệu -->
                    @php
                    $isGroup2Active = in_array($reportType, ['inventory_status', 'spine_label', 'barcode_list', 'book_title_qty', 'accession_book', 'generated_barcodes']);
                    @endphp
                    <div class="tree-group mt-2">
                        <div class="tree-folder flex items-center py-1.5 px-1 hover:bg-muted/50 rounded cursor-pointer select-none" data-group="group-2">
                            <span class="toggle-icon-container mr-1 text-muted-foreground flex items-center justify-center">
                                <i data-lucide="square-minus" class="w-4 h-4 toggle-open {{ $isGroup2Active ? '' : 'hidden' }}"></i>
                                <i data-lucide="square-plus" class="w-4 h-4 toggle-closed {{ $isGroup2Active ? 'hidden' : '' }}"></i>
                            </span>
                            <span class="folder-icon-container mr-1.5 text-muted-foreground flex items-center justify-center">
                                <i data-lucide="folder-open" class="w-4 h-4 folder-open-icon {{ $isGroup2Active ? '' : 'hidden' }}"></i>
                                <i data-lucide="folder" class="w-4 h-4 folder-closed-icon {{ $isGroup2Active ? 'hidden' : '' }}"></i>
                            </span>
                            <span class="font-bold text-foreground text-xs">{{ __('Báo cáo tài liệu') }}</span>
                        </div>
                        
                        <!-- Group 2 children (with tree lines) -->
                        <div class="tree-node-parent {{ $isGroup2Active ? '' : 'hidden' }}" id="group-2">
                            <a href="{{ route('admin.marc.reports.index', ['report_type' => 'inventory_status']) }}" 
                               class="tree-node-child tree-leaf flex items-center py-1 px-2 my-0.5 rounded border border-transparent hover:bg-muted {{ $reportType === 'inventory_status' ? 'active-leaf bg-primary text-primary-foreground font-semibold' : 'text-muted-foreground' }}">
                                <span class="relative flex items-center mr-1.5">
                                    <span class="w-1.5 h-1.5 bg-primary {{ $reportType === 'inventory_status' ? 'bg-primary-foreground' : 'invisible' }} rounded-full mr-2"></span>
                                    <i data-lucide="file-text" class="w-4 h-4 leaf-icon {{ $reportType === 'inventory_status' ? 'text-primary-foreground' : 'text-muted-foreground' }}"></i>
                                </span>
                                <span class="text-xs">{{ __('Tình hình kho tài liệu') }}</span>
                            </a>
                            
                            <a href="{{ route('admin.marc.reports.index', ['report_type' => 'spine_label']) }}" 
                               class="tree-node-child tree-leaf flex items-center py-1 px-2 my-0.5 rounded border border-transparent hover:bg-muted {{ $reportType === 'spine_label' ? 'active-leaf bg-primary text-primary-foreground font-semibold' : 'text-muted-foreground' }}">
                                <span class="relative flex items-center mr-1.5">
                                    <span class="w-1.5 h-1.5 bg-primary {{ $reportType === 'spine_label' ? 'bg-primary-foreground' : 'invisible' }} rounded-full mr-2"></span>
                                    <i data-lucide="file-text" class="w-4 h-4 leaf-icon {{ $reportType === 'spine_label' ? 'text-primary-foreground' : 'text-muted-foreground' }}"></i>
                                </span>
                                <span class="text-xs">{{ __('In Nhãn gáy') }}</span>
                            </a>
                            
                            <a href="{{ route('admin.marc.reports.index', ['report_type' => 'barcode_list']) }}" 
                               class="tree-node-child tree-leaf flex items-center py-1 px-2 my-0.5 rounded border border-transparent hover:bg-muted {{ $reportType === 'barcode_list' ? 'active-leaf bg-primary text-primary-foreground font-semibold' : 'text-muted-foreground' }}">
                                <span class="relative flex items-center mr-1.5">
                                    <span class="w-1.5 h-1.5 bg-primary {{ $reportType === 'barcode_list' ? 'bg-primary-foreground' : 'invisible' }} rounded-full mr-2"></span>
                                    <i data-lucide="file-text" class="w-4 h-4 leaf-icon {{ $reportType === 'barcode_list' ? 'text-primary-foreground' : 'text-muted-foreground' }}"></i>
                                </span>
                                <span class="text-xs">{{ __('In mã vạch') }}</span>
                            </a>
                            
                            <a href="{{ route('admin.marc.reports.index', ['report_type' => 'book_title_qty']) }}" 
                               class="tree-node-child tree-leaf flex items-center py-1 px-2 my-0.5 rounded border border-transparent hover:bg-muted {{ $reportType === 'book_title_qty' ? 'active-leaf bg-primary text-primary-foreground font-semibold' : 'text-muted-foreground' }}">
                                <span class="relative flex items-center mr-1.5">
                                    <span class="w-1.5 h-1.5 bg-primary {{ $reportType === 'book_title_qty' ? 'bg-primary-foreground' : 'invisible' }} rounded-full mr-2"></span>
                                    <i data-lucide="file-text" class="w-4 h-4 leaf-icon {{ $reportType === 'book_title_qty' ? 'text-primary-foreground' : 'text-muted-foreground' }}"></i>
                                </span>
                                <span class="text-xs">{{ __('Danh sách nhan đề và số lượng') }}</span>
                            </a>
                            
                            <a href="{{ route('admin.marc.reports.index', ['report_type' => 'accession_book']) }}" 
                               class="tree-node-child tree-leaf flex items-center py-1 px-2 my-0.5 rounded border border-transparent hover:bg-muted {{ $reportType === 'accession_book' ? 'active-leaf bg-primary text-primary-foreground font-semibold' : 'text-muted-foreground' }}">
                                <span class="relative flex items-center mr-1.5">
                                    <span class="w-1.5 h-1.5 bg-primary {{ $reportType === 'accession_book' ? 'bg-primary-foreground' : 'invisible' }} rounded-full mr-2"></span>
                                    <i data-lucide="file-text" class="w-4 h-4 leaf-icon {{ $reportType === 'accession_book' ? 'text-primary-foreground' : 'text-muted-foreground' }}"></i>
                                </span>
                                <span class="text-xs">{{ __('Sổ đăng ký cá biệt') }}</span>
                            </a>
                            
                            <a href="{{ route('admin.marc.reports.index', ['report_type' => 'generated_barcodes']) }}" 
                               class="tree-node-child tree-leaf flex items-center py-1 px-2 my-0.5 rounded border border-transparent hover:bg-muted {{ $reportType === 'generated_barcodes' ? 'active-leaf bg-primary text-primary-foreground font-semibold' : 'text-muted-foreground' }}">
                                <span class="relative flex items-center mr-1.5">
                                    <span class="w-1.5 h-1.5 bg-primary {{ $reportType === 'generated_barcodes' ? 'bg-primary-foreground' : 'invisible' }} rounded-full mr-2"></span>
                                    <i data-lucide="file-text" class="w-4 h-4 leaf-icon {{ $reportType === 'generated_barcodes' ? 'text-primary-foreground' : 'text-muted-foreground' }}"></i>
                                </span>
                                <span class="text-xs">{{ __('In mã vạch phát sinh') }}</span>
                            </a>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
        
        <!-- Right Column: Filters and Actions Panel -->
        <div class="flex-1 min-w-0 bg-card text-foreground border border-border rounded-md shadow-sm flex flex-col min-h-[450px] relative">
            <!-- Panel Header with Expand Button -->
            <div class="p-3 border-b border-border bg-muted/30 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 w-full">
                <div class="flex items-start gap-2 flex-1 min-w-0">
                    <!-- Expand Button, hidden by default -->
                    <button type="button" id="expand-tree-btn" class="btn-icon-compact hidden shrink-0" title="{{ __('Mở rộng danh mục') }}">
                        <i data-lucide="chevron-right" class="w-4 h-4 text-muted-foreground"></i>
                    </button>
                    <div class="flex-1 min-w-0">
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-sm text-[10px] font-bold bg-primary/10 text-primary uppercase tracking-widest mb-1" id="current_report_label">
                            {{ $activeReport['title'] }}
                        </span>
                        <h2 class="text-sm font-bold text-foreground tracking-tight" id="right_panel_title">
                            {{ $activeReport['title'] }}
                        </h2>
                        <p class="text-xs text-muted-foreground mt-0.5 leading-relaxed" id="right_panel_desc">
                            {{ $activeReport['desc'] }}
                        </p>
                    </div>
                </div>
            </div>
            
            @if($reportType === 'article_index')
                <!-- Feature Pending State -->
                <div class="p-4 flex-1 flex flex-col justify-center">
                    <x-feature-pending 
                        title="{{ __('Thư mục bài trích chưa cập nhật') }}"
                        desc="{{ __('Tính năng báo cáo Thư mục bài trích hiện đang được phát triển và sẽ sớm được cập nhật trong phiên bản tiếp theo.') }}"
                        icon="file-text"
                    />
                </div>
            @elseif($reportType === 'inventory_status')
                <!-- Feature Pending State -->
                <div class="p-4 flex-1 flex flex-col justify-center">
                    <x-feature-pending 
                        title="{{ __('Báo cáo chi tiết tình hình kho chưa cập nhật') }}"
                        desc="{{ __('Tính năng báo cáo chi tiết tình hình kho hiện đang được phát triển và sẽ sớm được cập nhật trong phiên bản tiếp theo.') }}"
                        icon="construction"
                    />
                </div>
            @elseif($reportType === 'accession_book')
                <!-- Feature Pending State -->
                <div class="p-4 flex-1 flex flex-col justify-center">
                    <x-feature-pending 
                        title="{{ __('Sổ đăng ký cá biệt chưa cập nhật') }}"
                        desc="{{ __('Tính năng báo cáo Sổ đăng ký cá biệt hiện đang được phát triển và sẽ sớm được cập nhật trong phiên bản tiếp theo.') }}"
                        icon="book-open"
                    />
                </div>
            @elseif($reportType === 'generated_barcodes')
                <!-- Feature Pending State -->
                <div class="p-4 flex-1 flex flex-col justify-center">
                    <x-feature-pending 
                        title="{{ __('In mã vạch phát sinh chưa cập nhật') }}"
                        desc="{{ __('Tính năng báo cáo In mã vạch phát sinh hiện đang được phát triển và sẽ sớm được cập nhật trong phiên bản tiếp theo.') }}"
                        icon="barcode"
                    />
                </div>
            @else
                <!-- Filter Form -->
                <form action="{{ route('admin.marc.reports.generate') }}" method="POST" target="_blank" id="reportForm" class="p-3 flex-1 flex flex-col justify-between space-y-3">
                    @csrf
                    <input type="hidden" name="report_type" id="selected_report_type" value="{{ $reportType }}">
                    
                    <!-- Filter Search & Collapse Header -->
                    <div class="space-y-2 relative">
                        <!-- Simple Search Bar -->
                        <div class="flex items-center gap-2">
                            <div class="relative flex-1">
                                <input type="text" name="search" id="search_input" placeholder="{{ __('Tìm kiếm nhanh bằng từ khóa (Tiêu đề, Tác giả, Mã vạch, Số ĐKCB)...') }}" class="w-full h-9 pl-3 pr-10 text-xs border border-input rounded-sm bg-background text-foreground placeholder:text-muted-foreground/60 focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                                <button type="submit" class="absolute right-1 top-1 h-7 w-7 flex items-center justify-center rounded-sm bg-primary text-primary-foreground hover:bg-primary/90 transition-colors">
                                    <i data-lucide="search" class="w-4 h-4"></i>
                                </button>
                            </div>
                            <button type="button" id="toggle-advanced-btn" class="btn-compact-secondary">
                                <i data-lucide="sliders" class="w-4 h-4 mr-1"></i>
                                <span>{{ __('Tìm nâng cao') }}</span>
                                <i data-lucide="chevron-down" class="w-4 h-4 ml-1 transition-transform duration-200 transform" id="advanced-chevron"></i>
                            </button>
                        </div>

                        <!-- Collapsible Advanced Search Panel (Floating Overlay) -->
                        <div id="advanced-search-panel" class="hidden absolute left-0 right-0 top-full mt-1 z-30 border border-border rounded-md bg-card p-3 space-y-3 shadow-lg transition-all duration-300">
                            <!-- Tab Selector Buttons -->
                            <div class="flex items-center gap-1 bg-muted p-1 rounded-md border border-border w-fit max-w-full overflow-x-auto">
                                <button type="button" class="tab-btn px-3 py-1 text-xs font-semibold rounded bg-background text-foreground shadow-sm transition-all duration-200" data-tab="tab-info">Thông tin</button>
                                <button type="button" class="tab-btn px-3 py-1 text-xs font-medium rounded text-muted-foreground hover:text-foreground transition-all duration-200" data-tab="tab-dist">Phân phối</button>
                                <button type="button" class="tab-btn px-3 py-1 text-xs font-medium rounded text-muted-foreground hover:text-foreground transition-all duration-200" data-tab="tab-limit">Giới hạn</button>
                                <button type="button" class="tab-btn px-3 py-1 text-xs font-medium rounded text-muted-foreground hover:text-foreground transition-all duration-200" data-tab="tab-location">Vị trí</button>
                                <button type="button" class="tab-btn px-3 py-1 text-xs font-medium rounded text-muted-foreground hover:text-foreground transition-all duration-200" data-tab="tab-categories">Phân loại</button>
                            </div>

                            <!-- Tab Content Sections (Taller Height area) -->
                            <div class="tab-contents min-h-[240px]">
                                
                                <!-- Tab 1: Thông tin (4 Conditions Builder) -->
                                <div id="tab-info" class="tab-content space-y-1.5">
                                    @for($i = 0; $i < 4; $i++)
                                    <div class="flex items-center gap-2">
                                        @if($i == 0)
                                        <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest w-16 text-center">Bắt đầu</span>
                                        <input type="hidden" name="info_ops[]" value="AND">
                                        @else
                                        <select name="info_ops[]" class="h-8 px-2 text-xs border border-input rounded-sm bg-background text-foreground w-16">
                                            <option value="AND">AND</option>
                                            <option value="OR">OR</option>
                                            <option value="NOT">NOT</option>
                                        </select>
                                        @endif
                                        <select name="info_fields[]" class="h-8 px-2 text-xs border border-input rounded-sm bg-background text-foreground w-28 md:w-36 shrink-0">
                                            <option value="title" {{ $i==0 ? 'selected' : '' }}>{{ __('Tiêu đề') }}</option>
                                            <option value="author" {{ $i==1 ? 'selected' : '' }}>{{ __('Tác giả') }}</option>
                                            <option value="barcode">{{ __('Mã vạch') }}</option>
                                            <option value="isbn">ISBN</option>
                                            <option value="issn">ISSN</option>
                                            <option value="subject">{{ __('Chủ đề') }}</option>
                                            <option value="dewey">Dewey (DDC)</option>
                                            <option value="publisher">{{ __('Nhà xuất bản') }}</option>
                                            <option value="publisher_place">{{ __('Nơi xuất bản') }}</option>
                                            <option value="publisher_year">{{ __('Năm xuất bản') }}</option>
                                            <option value="accession_number">{{ __('Số ĐK cá biệt') }}</option>
                                            <option value="order_code">{{ __('Mã đơn hàng') }}</option>
                                            <option value="language_code">{{ __('Mã ngôn ngữ') }}</option>
                                            <option value="lc_call_number">{{ __('Xếp giá LC') }}</option>
                                            <option value="summary">{{ __('Tóm tắt') }}</option>
                                            <option value="notes">{{ __('Ghi chú') }}</option>
                                            <option value="genre">{{ __('Thể loại') }}</option>
                                            <option value="any">{{ __('Bất kỳ') }}</option>
                                            <option value="fulltext">Fulltext</option>
                                        </select>
                                        <input type="text" name="info_vals[]" placeholder="{{ __('Nhập từ khóa tìm kiếm...') }}" class="flex-1 h-8 px-2 text-xs border border-input rounded-sm bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                                    </div>
                                    @endfor
                                </div>

                                <!-- Tab 2: Phân phối ( circulation_statuses, storage_types, distribution_statuses ) -->
                                <div id="tab-dist" class="tab-content hidden grid grid-cols-1 md:grid-cols-3 gap-3">
                                    <!-- Tình trạng lưu thông (Taller scrolling list) -->
                                    <div class="space-y-1">
                                        <span class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest block border-b border-border pb-1">Tình trạng lưu thông</span>
                                        <div class="max-h-56 overflow-y-auto custom-scrollbar space-y-1 pr-1">
                                            @php
                                            $circStatuses = [
                                                'available' => 'Sẵn có',
                                                'borrowed' => 'Đang cho mượn',
                                                'lost' => 'Bị mất',
                                                'damaged' => 'Bị hỏng',
                                                'cancelled' => 'Đã hủy',
                                                'disposed' => 'Đã thanh lý',
                                                'ordered' => 'Đang đặt hàng',
                                                'binding' => 'Đang đóng tập',
                                                'reserved' => 'Đang giữ lại',
                                                'photocopy' => 'Đang Photocopy',
                                                'repairing' => 'Đang sửa chữa',
                                                'shelving' => 'Đang xếp giá',
                                                'processing' => 'Đang xử lý',
                                                'non_circulating' => 'Không lưu thông',
                                                'reading_room' => 'Mượn đọc',
                                                'missing' => 'Thất lạc'
                                            ];
                                            @endphp
                                            @foreach($circStatuses as $val => $label)
                                            <label class="flex items-center text-xs text-foreground cursor-pointer select-none">
                                                <input type="checkbox" name="circulation_statuses[]" value="{{ $val }}" class="w-3.5 h-3.5 text-primary bg-background border-border rounded-sm focus:ring-primary">
                                                <span class="ml-1.5 text-[11px]">{{ $label }}</span>
                                            </label>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Dạng lưu trữ (Taller scrolling list) -->
                                    <div class="space-y-1">
                                        <span class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest block border-b border-border pb-1">Dạng lưu trữ</span>
                                        <div class="max-h-56 overflow-y-auto custom-scrollbar space-y-1 pr-1">
                                            @php
                                            $storageTypes = [
                                                'Monograph' => 'Chuyên khảo / Monograph',
                                                'Serial' => 'Tạp chí / Serial',
                                                'Newspaper' => 'Báo quyển / Newspaper',
                                                'DailyNewspaper' => 'Nhật báo',
                                                'Thesis' => 'Luận án / Thesis',
                                                'Dissertation' => 'Luận văn',
                                                'Map' => 'Bản đồ / Map',
                                                'Audio' => 'Ghi âm',
                                                'Video' => 'Ghi hình',
                                                'Software' => 'Phần mềm CD/PC',
                                                'Braille' => 'TL khiếm thị/thính',
                                                'AV' => 'Tài liệu nghe nhìn',
                                                'Reference' => 'Tham khảo / Reference',
                                                'Microfilm' => 'Vi phim',
                                                'Project' => 'Đề tài'
                                            ];
                                            @endphp
                                            @foreach($storageTypes as $val => $label)
                                            <label class="flex items-center text-xs text-foreground cursor-pointer select-none">
                                                <input type="checkbox" name="storage_types[]" value="{{ $val }}" class="w-3.5 h-3.5 text-primary bg-background border-border rounded-sm focus:ring-primary">
                                                <span class="ml-1.5 text-[11px]">{{ $label }}</span>
                                            </label>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Tình trạng phân phối -->
                                    <div class="space-y-1">
                                        <span class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest block border-b border-border pb-1">Tình trạng phân phối</span>
                                        <div class="space-y-1">
                                            <label class="flex items-center text-xs text-foreground cursor-pointer select-none">
                                                <input type="checkbox" name="distribution_statuses[]" value="distributed" class="w-3.5 h-3.5 text-primary bg-background border-border rounded-sm focus:ring-primary">
                                                <span class="ml-1.5 text-[11px]">Đã phân phối</span>
                                            </label>
                                            <label class="flex items-center text-xs text-foreground cursor-pointer select-none">
                                                <input type="checkbox" name="distribution_statuses[]" value="not_distributed" class="w-3.5 h-3.5 text-primary bg-background border-border rounded-sm focus:ring-primary">
                                                <span class="ml-1.5 text-[11px]">Chưa phân phối</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tab 3: Giới hạn ( Updated Date, size code, etc ) -->
                                <div id="tab-limit" class="tab-content hidden grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <!-- Ngày cập nhật (date created moved to quick filter bar) -->
                                    <div class="space-y-2">
                                        <div class="space-y-1">
                                            <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest block">Khoảng ngày cập nhật</label>
                                            <div class="flex items-center gap-1">
                                                <input type="date" name="updated_date_from" class="flex-1 h-8 px-2 text-xs border border-input rounded-sm bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                                                <span class="text-[10px] text-muted-foreground">đến</span>
                                                <input type="date" name="updated_date_to" class="flex-1 h-8 px-2 text-xs border border-input rounded-sm bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Người tạo, người cập nhật, mã khổ cỡ, limits -->
                                    <div class="grid grid-cols-2 gap-2">
                                        <div class="space-y-0.5">
                                            <label class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest block">Người tạo</label>
                                            <input type="text" name="created_by" placeholder="Tên..." class="w-full h-8 px-2 text-xs border border-input rounded-sm bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                                        </div>
                                        <div class="space-y-0.5">
                                            <label class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest block">Người cập nhật</label>
                                            <input type="text" name="updated_by" placeholder="Tên..." class="w-full h-8 px-2 text-xs border border-input rounded-sm bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                                        </div>
                                        <div class="space-y-0.5">
                                            <label class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest block">Mã khổ cỡ</label>
                                            <input type="text" name="size_code" placeholder="Mã..." class="w-full h-8 px-2 text-xs border border-input rounded-sm bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                                        </div>
                                        <div class="space-y-0.5">
                                            <label class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest block">Cấp độ sử dụng</label>
                                            <input type="text" name="usage_level" placeholder="Cấp..." class="w-full h-8 px-2 text-xs border border-input rounded-sm bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                                        </div>
                                        <div class="space-y-0.5">
                                            <label class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest block">Giới hạn kết quả</label>
                                            <input type="number" name="result_limit" placeholder="Số lượng bản ghi..." class="w-full h-8 px-2 text-xs border border-input rounded-sm bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                                        </div>
                                        <div class="space-y-0.5">
                                            <label class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest block">Mã ngành</label>
                                            <input type="text" name="branch_code" placeholder="Mã ngành..." class="w-full h-8 px-2 text-xs border border-input rounded-sm bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all">
                                        </div>
                                        <div class="col-span-2 flex items-center pt-1">
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" name="waits_for_print" value="1" class="w-4 h-4 text-primary bg-background border-border rounded-sm focus:ring-primary">
                                                <span class="ml-2 text-[9px] font-bold text-muted-foreground uppercase tracking-widest">Danh sách chờ in thẻ</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tab 4: Vị trí ( branch_id, storage_location_id ) -->
                                <div id="tab-location" class="tab-content hidden grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div class="space-y-1">
                                        <label for="branch_id" class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest block">Kho / Phòng</label>
                                        <select name="branch_id" id="branch_id" class="w-full h-9 px-3 text-xs border border-input rounded-md bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all duration-200 cursor-pointer">
                                            <option value="">-- Tất cả Kho/Phòng --</option>
                                            @foreach($branches as $b)
                                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="space-y-1">
                                        <label for="storage_location_id" class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest block">Vị trí / Kệ</label>
                                        <select name="storage_location_id" id="storage_location_id" class="w-full h-9 px-3 text-xs border border-input rounded-md bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all duration-200 cursor-pointer">
                                            <option value="">-- Tất cả Vị trí --</option>
                                            @foreach($storageLocations as $sl)
                                            <option value="{{ $sl->id }}">{{ $sl->name }} ({{ optional($sl->branch)->name }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <!-- Tab 5: Phân loại Checkboxes ( frameworks, document_types, statuses ) -->
                                <div id="tab-categories" class="tab-content hidden grid grid-cols-1 md:grid-cols-3 gap-3">
                                    <!-- Khung biên mục (Taller scrolling list) -->
                                    <div class="space-y-1">
                                        <span class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest block border-b border-border pb-1">Khung biên mục</span>
                                        <div class="max-h-56 overflow-y-auto custom-scrollbar space-y-1 pr-1">
                                            @foreach($frameworks as $fw)
                                            <label class="flex items-center text-xs text-foreground cursor-pointer select-none">
                                                <input type="checkbox" name="frameworks[]" value="{{ $fw->code }}" class="w-3.5 h-3.5 text-primary bg-background border-border rounded-sm focus:ring-primary">
                                                <span class="ml-1.5 text-[11px]">{{ $fw->name }} ({{ $fw->code }})</span>
                                            </label>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Loại tài liệu (Taller scrolling list) -->
                                    <div class="space-y-1">
                                        <span class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest block border-b border-border pb-1">Loại tài liệu</span>
                                        <div class="max-h-56 overflow-y-auto custom-scrollbar space-y-1 pr-1">
                                            @foreach($documentTypes as $dt)
                                            <label class="flex items-center text-xs text-foreground cursor-pointer select-none">
                                                <input type="checkbox" name="document_types[]" value="{{ $dt->id }}" class="w-3.5 h-3.5 text-primary bg-background border-border rounded-sm focus:ring-primary">
                                                <span class="ml-1.5 text-[11px]">{{ $dt->name }}</span>
                                            </label>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Trạng thái -->
                                    <div class="space-y-1">
                                        <span class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest block border-b border-border pb-1">Trạng thái</span>
                                        <div class="space-y-1">
                                            <label class="flex items-center text-xs text-foreground cursor-pointer select-none">
                                                <input type="checkbox" name="statuses[]" value="approved" class="w-3.5 h-3.5 text-primary bg-background border-border rounded-sm focus:ring-primary">
                                                <span class="ml-1.5 text-[11px]">Đã duyệt</span>
                                            </label>
                                            <label class="flex items-center text-xs text-foreground cursor-pointer select-none">
                                                <input type="checkbox" name="statuses[]" value="pending" class="w-3.5 h-3.5 text-primary bg-background border-border rounded-sm focus:ring-primary">
                                                <span class="ml-1.5 text-[11px]">Chưa duyệt</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <!-- Close Button inside Floating Panel -->
                            <div class="flex justify-end pt-1.5 border-t border-border mt-2">
                                <button type="button" class="close-advanced-btn flex items-center gap-1 text-[11px] font-bold text-muted-foreground hover:text-foreground transition-all">
                                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                    <span>{{ __('Đóng') }}</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Date Filter Bar (always visible) -->
                    <div class="flex flex-col sm:flex-row sm:items-center gap-2 border-t border-border pt-3">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest whitespace-nowrap">
                                <i data-lucide="calendar" class="w-3.5 h-3.5 inline-block mr-1"></i>Lọc theo ngày tạo:
                            </span>
                            <div class="flex items-center gap-1.5">
                                <input type="date" name="date_from" class="h-8 px-2 text-xs border border-input rounded-sm bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all" onchange="loadPreview(1)">
                                <span class="text-[10px] text-muted-foreground">→</span>
                                <input type="date" name="date_to" class="h-8 px-2 text-xs border border-input rounded-sm bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all" onchange="loadPreview(1)">
                            </div>
                            <button type="button" onclick="clearDateFilter()" class="text-[10px] text-muted-foreground hover:text-destructive font-bold transition-colors px-1.5 py-0.5 rounded border border-border hover:border-destructive/30">
                                <i data-lucide="x" class="w-3 h-3 inline-block"></i> Xóa ngày
                            </button>
                        </div>
                    </div>

                    <!-- Format Toggle and Include Items (Bottom bar) -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 flex-wrap border-t border-border pt-3">
                        <!-- Format Toggle -->
                        <div class="space-y-1">
                            <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest block">{{ __('Định dạng file') }}</span>
                            <div class="inline-flex p-0.5 bg-muted rounded-sm border border-border shadow-inner">
                                <label class="relative group cursor-pointer">
                                    <input type="radio" name="format" value="excel" checked class="sr-only peer">
                                    <div class="px-3 py-1 text-xs font-bold rounded-sm transition-all peer-checked:bg-primary peer-checked:text-primary-foreground text-muted-foreground hover:text-foreground">
                                        <i class="fas fa-file-excel mr-1 text-xs"></i>Excel
                                    </div>
                                </label>
                                <label class="relative group cursor-pointer ml-1">
                                    <input type="radio" name="format" value="csv" class="sr-only peer">
                                    <div class="px-3 py-1 text-xs font-bold rounded-sm transition-all peer-checked:bg-primary peer-checked:text-primary-foreground text-muted-foreground hover:text-foreground">
                                        <i class="fas fa-file-csv mr-1 text-xs"></i>CSV
                                    </div>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Paginate Limit Selector (Segmented Controls) -->
                        <div class="space-y-1">
                            <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest block">{{ __('Số dòng/Trang') }}</span>
                            <div class="inline-flex p-0.5 bg-muted rounded-sm border border-border shadow-inner">
                                <label class="relative group cursor-pointer">
                                    <input type="radio" name="per_page" value="10" checked onchange="loadPreview(1)" class="sr-only peer">
                                    <div class="px-2.5 py-1 text-xs font-bold rounded-sm transition-all peer-checked:bg-primary peer-checked:text-primary-foreground text-muted-foreground hover:text-foreground">
                                        10
                                    </div>
                                </label>
                                <label class="relative group cursor-pointer ml-0.5">
                                    <input type="radio" name="per_page" value="25" onchange="loadPreview(1)" class="sr-only peer">
                                    <div class="px-2.5 py-1 text-xs font-bold rounded-sm transition-all peer-checked:bg-primary peer-checked:text-primary-foreground text-muted-foreground hover:text-foreground">
                                        25
                                    </div>
                                </label>
                                <label class="relative group cursor-pointer ml-0.5">
                                    <input type="radio" name="per_page" value="50" onchange="loadPreview(1)" class="sr-only peer">
                                    <div class="px-2.5 py-1 text-xs font-bold rounded-sm transition-all peer-checked:bg-primary peer-checked:text-primary-foreground text-muted-foreground hover:text-foreground">
                                        50
                                    </div>
                                </label>
                                <label class="relative group cursor-pointer ml-0.5">
                                    <input type="radio" name="per_page" value="100" onchange="loadPreview(1)" class="sr-only peer">
                                    <div class="px-2.5 py-1 text-xs font-bold rounded-sm transition-all peer-checked:bg-primary peer-checked:text-primary-foreground text-muted-foreground hover:text-foreground">
                                        100
                                    </div>
                                </label>
                                <label class="relative group cursor-pointer ml-0.5">
                                    <input type="radio" name="per_page" value="200" onchange="loadPreview(1)" class="sr-only peer">
                                    <div class="px-2.5 py-1 text-xs font-bold rounded-sm transition-all peer-checked:bg-primary peer-checked:text-primary-foreground text-muted-foreground hover:text-foreground">
                                        200
                                    </div>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Include Items checkbox -->
                        <div class="flex items-center pt-1 sm:pt-0">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="include_items" id="include_items" value="1" class="w-4 h-4 text-primary bg-background border-border rounded-sm focus:ring-primary focus:ring-offset-background">
                                <span class="ml-2 text-[10px] font-bold text-muted-foreground uppercase tracking-widest">
                                    {{ __('Kèm thông tin ấn phẩm') }}
                                </span>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex items-center justify-between gap-2 border-t border-border pt-3 mt-3">
                        <button type="button" onclick="resetForm()" class="btn-compact-muted">
                            <i data-lucide="rotate-ccw" class="w-4 h-4 mr-1"></i>
                            {{ __('Reset bộ lọc') }}
                        </button>
                    </div>
                </form>

                <!-- Table Preview Card -->
                <div class="bg-card text-foreground border-t border-border shadow-sm overflow-hidden flex flex-col mt-4 rounded-b-md" id="report-preview-card">
                    <div class="p-3 border-b border-border bg-muted/30 flex items-center justify-between font-bold shadow-sm">
                        <div class="flex items-center gap-2">
                            <i data-lucide="eye" class="w-4 h-4 text-muted-foreground"></i>
                            <span class="text-xs font-bold uppercase tracking-wider text-foreground" id="preview-title">{{ __('XEM TRƯỚC BÁO CÁO') }}</span>
                        </div>
                        <!-- Export Button -->
                        <button type="button" id="export-excel-btn" class="btn-compact-primary flex items-center gap-1.5 text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white border-none">
                            <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5 text-white"></i>
                            <span>{{ __('Xuất tệp Excel') }}</span>
                        </button>
                    </div>
                    
                    <!-- Preview Table Container -->
                    <div class="overflow-x-auto w-full custom-scrollbar" id="preview-table-container">
                        <div class="flex flex-col items-center justify-center py-16 text-muted-foreground">
                            <i data-lucide="info" class="w-8 h-8 mb-2 text-muted-foreground/40"></i>
                            <p class="text-xs font-semibold">{{ __('Nhấp nút tìm kiếm hoặc nhập từ khóa để xem trước dữ liệu.') }}</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
        
    </div>
</div>

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Customize Select2 to comply with Rule.txt constraints */
    .select2-container--default .select2-selection--single {
        height: 36px !important; /* h-9 */
        padding: 5px 10px !important;
        border-radius: 2px !important; /* rounded-sm */
        border: 1px solid hsl(var(--border)) !important;
        background-color: hsl(var(--background)) !important;
        font-size: 0.875rem !important; /* text-sm */
        font-weight: 500 !important;
        color: hsl(var(--foreground)) !important;
        outline: none !important;
        transition: all 0.2s !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: hsl(var(--foreground)) !important;
        padding-left: 0px !important;
        line-height: 24px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 34px !important;
        right: 6px !important;
    }
    .select2-dropdown {
        border-radius: 4px !important; /* rounded-md */
        border: 1px solid hsl(var(--border)) !important;
        background-color: hsl(var(--card)) !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important;
        overflow: hidden !important;
    }
    .select2-results__option {
        padding: 6px 12px !important;
        font-size: 0.75rem !important;
        font-weight: 500 !important;
        color: hsl(var(--foreground)) !important;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: hsl(var(--primary)) !important;
        color: hsl(var(--primary-foreground)) !important;
    }
    
    /* Custom Tree View Styles matching standard file directories */
    .tree-node-parent {
        position: relative;
        padding-left: 12px;
    }
    .tree-node-parent::before {
        content: "";
        position: absolute;
        left: 8px;
        top: 0px;
        bottom: 12px;
        border-left: 1.5px dashed hsl(var(--border));
    }
    .tree-node-child {
        position: relative;
        padding-left: 16px;
    }
    .tree-node-child::before {
        content: "";
        position: absolute;
        left: -4px;
        top: 14px;
        width: 14px;
        border-top: 1.5px dashed hsl(var(--border));
    }
    
    /* Custom Scrollbar for tree */
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: hsl(var(--border));
        border-radius: 2px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: hsl(var(--muted-foreground));
    }

    /* Active Tab style constraint rule */
    .active-tab {
        background-color: hsl(var(--card)) !important;
        border-color: hsl(var(--border)) !important;
        color: hsl(var(--foreground)) !important;
        border-bottom-color: transparent !important;
    }

    /* Tree leaf hover rules preventing hover effect on active leaf but keeping it for inactive ones */
    .tree-leaf.active-leaf:hover, .tree-folder.active-leaf:hover {
        background-color: hsl(var(--primary)) !important;
        color: hsl(var(--primary-foreground)) !important;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Load initial preview on page load
        loadPreview(1);

        // Auto-collapse left tree column on page load
        const treeCol = document.getElementById('left-tree-column');
        if (treeCol) treeCol.classList.add('hidden');
        const expandBtn = document.getElementById('expand-tree-btn');
        if (expandBtn) expandBtn.classList.remove('hidden');

        // Folder toggle handler (collapse/expand)
        document.querySelectorAll('.tree-folder').forEach(function(el) {
            el.addEventListener('click', function(e) {
                if (e.target.closest('a')) {
                    return;
                }
                const groupId = this.getAttribute('data-group');
                const group = document.getElementById(groupId);
                if (group) {
                    if (group.classList.contains('hidden')) {
                        group.classList.remove('hidden');
                    } else {
                        group.classList.add('hidden');
                    }
                    this.querySelector('.toggle-open').classList.toggle('hidden');
                    this.querySelector('.toggle-closed').classList.toggle('hidden');
                    this.querySelector('.folder-open-icon').classList.toggle('hidden');
                    this.querySelector('.folder-closed-icon').classList.toggle('hidden');
                }
            });
        });

        // Collapse left column
        const toggleTreeBtn = document.getElementById('toggle-tree-btn');
        if (toggleTreeBtn) {
            toggleTreeBtn.addEventListener('click', function() {
                const treeCol = document.getElementById('left-tree-column');
                if (treeCol) treeCol.classList.add('hidden');
                const expandBtn = document.getElementById('expand-tree-btn');
                if (expandBtn) expandBtn.classList.remove('hidden');
            });
        }

        // Expand left column
        const expandTreeBtn = document.getElementById('expand-tree-btn');
        if (expandTreeBtn) {
            expandTreeBtn.addEventListener('click', function() {
                this.classList.add('hidden');
                const treeCol = document.getElementById('left-tree-column');
                if (treeCol) treeCol.classList.remove('hidden');
            });
        }

        // Toggle Advanced Search collapse
        const toggleAdvancedBtn = document.getElementById('toggle-advanced-btn');
        if (toggleAdvancedBtn) {
            toggleAdvancedBtn.addEventListener('click', function() {
                const panel = document.getElementById('advanced-search-panel');
                if (panel) panel.classList.toggle('hidden');
                const chevron = document.getElementById('advanced-chevron');
                if (chevron) chevron.classList.toggle('rotate-180');
            });
        }

        // Close Advanced Search
        document.querySelectorAll('.close-advanced-btn').forEach(function(el) {
            el.addEventListener('click', function() {
                const panel = document.getElementById('advanced-search-panel');
                if (panel) panel.classList.add('hidden');
                const chevron = document.getElementById('advanced-chevron');
                if (chevron) chevron.classList.remove('rotate-180');
            });
        });

        // Tab selection handler (Shadcn style toggles)
        document.querySelectorAll('.tab-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.tab-btn').forEach(function(b) {
                    b.classList.remove('bg-background', 'text-foreground', 'shadow-sm', 'font-semibold');
                    b.classList.add('text-muted-foreground', 'hover:text-foreground', 'font-medium');
                });
                
                this.classList.remove('text-muted-foreground', 'hover:text-foreground', 'font-medium');
                this.classList.add('bg-background', 'text-foreground', 'shadow-sm', 'font-semibold');
                
                const activeTabId = this.getAttribute('data-tab');
                document.querySelectorAll('.tab-content').forEach(function(c) {
                    c.classList.add('hidden');
                });
                const activeContent = document.getElementById(activeTabId);
                if (activeContent) activeContent.classList.remove('hidden');
            });
        });

        // Intercept form submit to load AJAX preview
        const form = document.getElementById('reportForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                loadPreview(1);
                // Auto close advanced search panel after search
                const panel = document.getElementById('advanced-search-panel');
                if (panel) panel.classList.add('hidden');
                const chevron = document.getElementById('advanced-chevron');
                if (chevron) chevron.classList.remove('rotate-180');
            });
        }

        // Trigger background export when export button is clicked
        const exportBtn = document.getElementById('export-excel-btn');
        if (exportBtn) {
            exportBtn.addEventListener('click', function() {
                const form = document.getElementById('reportForm');
                if (form) {
                    // Bắn thông báo đã nhận yêu cầu ngay lập tức
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: {
                            message: '{{ __("Yêu cầu xuất tệp của bạn đã được ghi nhận và đang xử lý...") }}',
                            type: 'info'
                        }
                    }));
                    
                    // Kích hoạt cơ chế Polling thông báo ở quả chuông
                    window.dispatchEvent(new CustomEvent('export-started'));

                    const formData = new FormData(form);
                    const params = new URLSearchParams();
                    for (const [key, value] of formData.entries()) {
                        params.append(key, value);
                    }

                    const csrfToken = form.querySelector('input[name="_token"]')?.value || "";

                    fetch("{{ route('admin.marc.reports.generate') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded",
                            "X-CSRF-TOKEN": csrfToken,
                            "X-Requested-With": "XMLHttpRequest"
                        },
                        body: params.toString()
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (!data.success) {
                            window.dispatchEvent(new CustomEvent('toast', {
                                detail: {
                                    message: data.message || '{{ __("Lỗi xảy ra khi gửi yêu cầu.") }}',
                                    type: 'error'
                                }
                            }));
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: {
                                message: '{{ __("Lỗi kết nối máy chủ.") }}',
                                type: 'error'
                            }
                        }));
                    });
                }
            });
        }

        // Handle preview table pagination clicks
        document.addEventListener('click', function(e) {
            const pageLink = e.target.closest('.preview-pagination a');
            if (pageLink) {
                e.preventDefault();
                const href = pageLink.getAttribute('href');
                if (href) {
                    const page = new URL(href).searchParams.get('page') || 1;
                    loadPreview(page);
                }
            }
        });
    });

    // Load Report Preview via AJAX (Fetch API)
    function loadPreview(page = 1) {
        const container = document.getElementById('preview-table-container');
        if (!container) return;
        
        container.innerHTML = `
            <div class="flex flex-col items-center justify-center py-20 text-muted-foreground">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mb-3"></div>
                <p class="text-xs">Đang tải bản xem trước...</p>
            </div>
        `;
        
        const form = document.getElementById('reportForm');
        if (!form) return;

        const formData = new FormData(form);
        formData.append('page', page);

        const searchParams = new URLSearchParams();
        for (const [key, value] of formData.entries()) {
            searchParams.append(key, value);
        }
        
        const csrfToken = form.querySelector('input[name="_token"]')?.value || "";

        fetch("{{ route('admin.marc.reports.preview') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
                "X-CSRF-TOKEN": csrfToken
            },
            body: searchParams.toString(),
            credentials: 'same-origin'
        })
        .then(function(response) {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.text();
        })
        .then(function(html) {
            container.innerHTML = html;
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        })
        .catch(function(error) {
            console.error(error);
            container.innerHTML = `
                <div class="flex flex-col items-center justify-center py-16 text-destructive">
                    <i data-lucide="alert-triangle" class="w-8 h-8 mb-2"></i>
                    <p class="text-xs font-semibold">Lỗi tải dữ liệu xem trước.</p>
                </div>
            `;
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    }

    // Clear date filter and reload preview
    function clearDateFilter() {
        document.querySelectorAll('input[type="date"]').forEach(function(el) {
            el.value = '';
        });
        loadPreview(1);
    }

    // Reset Form function
    function resetForm() {
        // Clear text inputs
        const searchInput = document.getElementById('search_input');
        if (searchInput) searchInput.value = '';
        
        document.querySelectorAll('input[name="info_vals[]"]').forEach(function(el) {
            el.value = '';
        });
        document.querySelectorAll('input[type="date"]').forEach(function(el) {
            el.value = '';
        });
        document.querySelectorAll('input[type="text"]').forEach(function(el) {
            el.value = '';
        });
        document.querySelectorAll('input[type="number"]').forEach(function(el) {
            el.value = '';
        });
        
        // Uncheck checkboxes
        document.querySelectorAll('input[type="checkbox"]').forEach(function(el) {
            el.checked = false;
        });
        
        // Reset select dropdowns
        document.querySelectorAll('select[name="info_fields[]"]').forEach(function(el, index) {
            if (index == 0) el.value = 'title';
            else if (index == 1) el.value = 'author';
            else el.value = 'title';
        });
        document.querySelectorAll('select[name="info_ops[]"]').forEach(function(el) {
            el.value = 'AND';
        });
        
        // Reset dropdowns
        const branchEl = document.getElementById('branch_id');
        if (branchEl) branchEl.value = '';
        const locEl = document.getElementById('storage_location_id');
        if (locEl) locEl.value = '';
        
        // Reset Format radio buttons
        const excelRadio = document.querySelector('input[name="format"][value="excel"]');
        if (excelRadio) excelRadio.checked = true;
        
        // Reload preview
        loadPreview(1);
    }
</script>
@endpush
@endsection
