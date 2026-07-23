@extends('layouts.admin')

@section('title', __('Patron Management'))

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/lucide@latest"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>
@endpush

@section('content')
<div class="space-y-4 pb-12">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-foreground tracking-tight">{{ __('Patron Management') }}</h1>
            <p class="text-muted-foreground text-xs font-medium mt-0.5">{{ __('Manage and audit library member identities.') }}</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('admin.patrons.import.index') }}" class="bg-emerald-600 text-white px-3 py-1.5 rounded text-xs font-bold shadow-sm transition-all hover:bg-emerald-500 flex items-center space-x-1.5 active:scale-[0.98]">
                <i data-lucide="upload" class="w-4 h-4"></i>
                <span>{{ __('Import Excel') }}</span>
            </a>
            <a href="{{ route('admin.patrons.create') }}" class="bg-primary text-primary-foreground px-3 py-1.5 rounded text-xs font-bold shadow-sm transition-all hover:bg-primary/90 flex items-center space-x-1.5 active:scale-[0.98]">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>{{ __('Add New Patron') }}</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-500/10 border border-emerald-500/20 rounded p-3 flex items-center space-x-2 shadow-sm text-emerald-500">
            <i data-lucide="check-circle" class="w-4 h-4"></i>
            <span class="text-xs font-bold">{{ session('success') }}</span>
        </div>
    @endif

    <!-- Search Section -->
    <div class="bg-card rounded-md shadow-sm border border-border p-4">
        <!-- Search Header (Always Visible) -->
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-xs font-bold text-foreground uppercase tracking-widest">{{ __('Search & Filters') }}</h2>
            <button type="button" onclick="toggleFilters()" class="text-primary hover:text-primary/80 text-xs font-bold flex items-center space-x-1">
                <i data-lucide="chevron-down" id="filterToggleIcon" class="w-4 h-4 transform transition-transform"></i>
                <span id="filterToggleText">{{ __('Show Filters') }}</span>
            </button>
        </div>
        
        <!-- Main Search Form -->
        <form method="GET" action="{{ route('admin.patrons.index') }}" id="mainSearchForm">
            <!-- Search Bar with Field and Button -->
            <div class="flex flex-col sm:flex-row gap-3 items-end">
                <!-- Search Field (Left) -->
                <div class="w-full sm:w-1/3">
                    <label class="block text-[10px] font-bold text-muted-foreground uppercase tracking-widest mb-1">{{ __('Search Field') }}</label>
                    <select name="search_field" class="w-full px-3 py-2 border border-border bg-background text-foreground rounded text-xs focus:ring-1 focus:ring-primary focus:border-primary outline-none">
                        <option value="all" {{ ($searchField ?? 'all') == 'all' ? 'selected' : '' }}>{{ __('All Fields') }}</option>
                        <option value="patron_code" {{ ($searchField ?? '') == 'patron_code' ? 'selected' : '' }}>{{ __('Patron Code') }}</option>
                        <option value="name" {{ ($searchField ?? '') == 'name' ? 'selected' : '' }}>{{ __('Name') }}</option>
                        <option value="email" {{ ($searchField ?? '') == 'email' ? 'selected' : '' }}>{{ __('Email') }}</option>
                        <option value="phone" {{ ($searchField ?? '') == 'phone' ? 'selected' : '' }}>{{ __('Phone') }}</option>
                        <option value="address" {{ ($searchField ?? '') == 'address' ? 'selected' : '' }}>{{ __('Address') }}</option>
                    </select>
                </div>
                
                <!-- Search Input (Center) -->
                <div class="w-full sm:flex-1 relative">
                    <input type="text" 
                           name="search" 
                           value="{{ $search ?? '' }}" 
                           placeholder="{{ __('Search patrons...') }}" 
                           class="w-full pl-9 pr-12 py-2 border border-border bg-background text-foreground rounded text-xs focus:ring-1 focus:ring-primary focus:border-primary outline-none">
                    <div class="absolute left-3 top-2.5">
                        <i data-lucide="search" id="searchIcon" class="w-4 h-4 text-muted-foreground transition-opacity duration-200"></i>
                    </div>
                    
                    <!-- Search Button (Right) -->
                    <button type="submit" class="absolute right-1 top-1 px-2.5 py-1 bg-primary hover:bg-primary/95 text-primary-foreground rounded text-xs font-bold transition-colors">
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </button>
                </div>
            </div>
            
            <!-- Hidden inputs for advanced filters to maintain state -->
            <input type="hidden" name="status" value="{{ $status ?? 'all' }}">
            <input type="hidden" name="patron_group" value="{{ $patronGroup ?? 'all' }}">
            <input type="hidden" name="branch" value="{{ $branch ?? 'all' }}">
            <input type="hidden" name="per_page" value="{{ $perPage ?? 15 }}">
            <input type="hidden" name="date_from" value="{{ $dateFrom ?? '' }}">
            <input type="hidden" name="date_to" value="{{ $dateTo ?? '' }}">
            <input type="hidden" name="expiry_status" value="{{ $expiryStatus ?? 'all' }}">
            <input type="hidden" name="expiring_in_days" value="{{ $expiringInDays ?? '' }}">
        </form>
        
        <!-- Advanced Filters (Collapsible) -->
        <div id="advancedFilters" class="border-t border-border mt-3 pt-3">
            <form method="GET" action="{{ route('admin.patrons.index') }}" id="advancedFiltersForm" class="space-y-3">
                <!-- Include search field and search input values -->
                <input type="hidden" name="search_field" value="{{ $searchField ?? 'all' }}">
                <input type="hidden" name="search" value="{{ $search ?? '' }}">
                
                <!-- Advanced Filters Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <!-- Left Column: Patron Group (Radio Buttons) -->
                    <div class="lg:border-r lg:border-border lg:pr-4">
                        <label class="block text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-2">{{ __('Nhóm độc giả') }}</label>
                        <div class="flex flex-col gap-1.5 max-h-64 overflow-y-auto pr-1">
                            <label class="flex items-center group cursor-pointer">
                                <input type="radio" name="patron_group" value="all" {{ ($patronGroup ?? 'all') == 'all' ? 'checked' : '' }} 
                                    onchange="this.form.submit()" class="hidden peer">
                                <div class="flex items-center space-x-2 w-full p-2 rounded border border-border bg-muted/30 transition-all peer-checked:bg-primary/10 peer-checked:border-primary/30 group-hover:border-primary/20">
                                    <div class="w-3.5 h-3.5 rounded-full border-2 border-muted flex items-center justify-center peer-checked:border-primary">
                                        <div class="w-1.5 h-1.5 rounded-full bg-primary opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                                    </div>
                                    <span class="text-xs font-bold text-muted-foreground peer-checked:text-foreground">{{ __('Tất cả các nhóm') }}</span>
                                </div>
                            </label>
                            @if(isset($patronGroups))
                                @foreach($patronGroups as $group)
                                    <label class="flex items-center group cursor-pointer">
                                        <input type="radio" name="patron_group" value="{{ $group->id }}" {{ ($patronGroup ?? '') == $group->id ? 'checked' : '' }} 
                                            onchange="this.form.submit()" class="hidden peer">
                                        <div class="flex items-center space-x-2 w-full p-2 rounded border border-border bg-muted/30 transition-all peer-checked:bg-primary/10 peer-checked:border-primary/30 group-hover:border-primary/20">
                                            <div class="w-3.5 h-3.5 rounded-full border-2 border-muted flex items-center justify-center peer-checked:border-primary">
                                                <div class="w-1.5 h-1.5 rounded-full bg-primary opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                                            </div>
                                            <span class="text-xs font-bold text-muted-foreground peer-checked:text-foreground">{{ $group->name }}</span>
                                        </div>
                                    </label>
                                @endforeach
                            @endif
                        </div>
                    </div>

                    <!-- Right Columns: Other Filters -->
                    <div class="lg:col-span-2 space-y-3">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <!-- Status -->
                            <div class="space-y-1">
                                <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground block ml-1">{{ __('Trạng thái') }}</label>
                                <select name="status" class="w-full bg-muted border border-border rounded px-3 py-2 text-xs font-bold text-foreground focus:bg-background focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all">
                                    <option value="all" {{ ($status ?? 'all') == 'all' ? 'selected' : '' }}>{{ __('Tất cả trạng thái') }}</option>
                                    <option value="active" {{ ($status ?? '') == 'active' ? 'selected' : '' }}>{{ __('Đang hoạt động') }}</option>
                                    <option value="locked" {{ ($status ?? '') == 'locked' ? 'selected' : '' }}>{{ __('Bị khóa') }}</option>
                                </select>
                            </div>

                            <!-- Branch -->
                            <div class="space-y-1">
                                <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground block ml-1">{{ __('Chi nhánh') }}</label>
                                <select name="branch" class="w-full bg-muted border border-border rounded px-3 py-2 text-xs font-bold text-foreground focus:bg-background focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all">
                                    <option value="all" {{ ($branch ?? 'all') == 'all' ? 'selected' : '' }}>{{ __('Tất cả chi nhánh') }}</option>
                                    @if(isset($branches))
                                        @foreach($branches as $branchItem)
                                            <option value="{{ $branchItem->id }}" {{ ($branch ?? '') == $branchItem->id ? 'selected' : '' }}>{{ $branchItem->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>

                        <!-- Date Range -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-2 border-t border-border">
                            <div class="space-y-1">
                                <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground block ml-1">{{ __('Ngày đăng ký từ') }}</label>
                                <input type="date" name="date_from" value="{{ $dateFrom ?? '' }}" class="w-full bg-muted border border-border rounded px-3 py-2 text-xs font-bold text-foreground focus:bg-background focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground block ml-1">{{ __('Ngày đăng ký đến') }}</label>
                                <input type="date" name="date_to" value="{{ $dateTo ?? '' }}" class="w-full bg-muted border border-border rounded px-3 py-2 text-xs font-bold text-foreground focus:bg-background focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all">
                            </div>
                        </div>

                        <!-- Expiration Filters -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-2 border-t border-border">
                            <!-- Expiry Status -->
                            <div class="space-y-1">
                                <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground block ml-1">{{ __('Hạn thẻ bạn đọc') }}</label>
                                <select name="expiry_status" class="w-full bg-muted border border-border rounded px-3 py-2 text-xs font-bold text-foreground focus:bg-background focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all">
                                    <option value="all" {{ ($expiryStatus ?? 'all') == 'all' ? 'selected' : '' }}>{{ __('Tất cả trạng thái hạn') }}</option>
                                    <option value="expired" {{ ($expiryStatus ?? '') == 'expired' ? 'selected' : '' }}>{{ __('Thẻ đã hết hạn') }}</option>
                                    <option value="active" {{ ($expiryStatus ?? '') == 'active' ? 'selected' : '' }}>{{ __('Thẻ còn hạn') }}</option>
                                </select>
                            </div>
                            <!-- Expiring in N days -->
                            <div class="space-y-1">
                                <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground block ml-1">{{ __('Sắp hết hạn trong (ngày)') }}</label>
                                <input type="number" min="1" name="expiring_in_days" value="{{ $expiringInDays ?? '' }}" placeholder="{{ __('Ví dụ: 30, 60, 90...') }}" class="w-full bg-muted border border-border rounded px-3 py-2 text-xs font-bold text-foreground focus:bg-background focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="flex items-center justify-between pt-3 border-t border-border">
                    <button type="submit" class="px-4 py-2 bg-primary hover:bg-primary/95 text-primary-foreground rounded text-xs font-bold transition-colors flex items-center space-x-1.5 active:scale-[0.98]">
                        <i data-lucide="search" class="w-3.5 h-3.5"></i>
                        <span>{{ __('Tìm kiếm') }}</span>
                    </button>
                    
                    <button type="button" onclick="clearFilters()" class="px-4 py-2 bg-muted hover:bg-muted/80 text-foreground border border-border rounded text-xs font-bold transition-colors flex items-center space-x-1.5 active:scale-[0.98]">
                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                        <span>{{ __('Clear') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bulk Actions Section -->
    <div class="bg-card rounded shadow-sm border border-border p-3" id="bulkActionsSection" style="display: none;">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center space-x-3">
                <span class="text-xs font-bold text-foreground">
                    <span id="selectedCount" class="text-primary font-black">0</span> {{ __('patrons selected') }}
                </span>
                <button type="button" onclick="clearSelection()" class="text-xs text-muted-foreground hover:text-foreground font-bold underline">
                    {{ __('Clear selection') }}
                </button>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <!-- Bulk Edit Button -->
                <button type="button" onclick="openBulkEditModal()" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-500 text-white rounded text-xs font-bold transition-colors flex items-center space-x-1.5 active:scale-[0.98]">
                    <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                    <span>{{ __('Bulk Edit') }}</span>
                </button>
                
                <!-- Print Cards Button -->
                <form method="POST" action="{{ route('admin.patrons.cards.generate') }}" class="inline">
                    @csrf
                    <input type="hidden" name="layout" value="batch">
                    <button type="submit" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded text-xs font-bold transition-colors flex items-center space-x-1.5 active:scale-[0.98]">
                        <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                        <span>{{ __('Print Cards') }}</span>
                    </button>
                </form>
                
                <!-- Delete Button -->
                <button type="button" onclick="confirmBulkDelete()" class="px-3 py-1.5 bg-red-600 hover:bg-red-500 text-white rounded text-xs font-bold transition-colors flex items-center space-x-1.5 active:scale-[0.98]">
                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                    <span>{{ __('Delete') }}</span>
                </button>
            </div>
        </div>
    </div>

    <!-- View Mode Toggle & Results Count -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 my-3">
        <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
            <span class="text-xs text-muted-foreground font-medium">
                @if(isset($patrons))
                    {{ __('Showing :count of :total results', ['count' => $patrons->count(), 'total' => $patrons->total()]) }}
                @else
                    {{ __('No results to display') }}
                @endif
            </span>
            
            <!-- Sort Radio Buttons -->
            <div class="flex items-center space-x-2">
                <span class="text-xs text-muted-foreground font-bold">{{ __('Sắp xếp:') }}</span>
                <div class="flex items-center space-x-2">
                    <label class="flex items-center cursor-pointer">
                        <input type="radio" name="sort" value="desc" 
                               {{ (request('sort', 'desc') == 'desc') ? 'checked' : '' }}
                               onchange="changeSort(this.value)"
                               class="w-3.5 h-3.5 text-primary border-border focus:ring-primary bg-background">
                        <span class="ml-1 text-xs text-foreground font-bold">{{ __('Giảm dần') }}</span>
                    </label>
                    <label class="flex items-center cursor-pointer">
                        <input type="radio" name="sort" value="asc" 
                               {{ (request('sort') == 'asc') ? 'checked' : '' }}
                               onchange="changeSort(this.value)"
                               class="w-3.5 h-3.5 text-primary border-border focus:ring-primary bg-background">
                        <span class="ml-1 text-xs text-foreground font-bold">{{ __('Tăng dần') }}</span>
                    </label>
                </div>
            </div>
        </div>
        
        <div class="flex items-center justify-between sm:justify-end gap-3">
            <!-- Per Page Select -->
            <div class="flex items-center space-x-1.5 whitespace-nowrap">
                <span class="text-xs text-muted-foreground font-bold">{{ __('Mỗi trang:') }}</span>
                <select onchange="changePerPage(this.value)" class="bg-muted border border-border rounded px-2 py-1 text-xs font-bold text-foreground focus:ring-0 cursor-pointer">
                    <option value="15" {{ ($perPage ?? 15) == 15 ? 'selected' : '' }}>15</option>
                    <option value="30" {{ ($perPage ?? 15) == 30 ? 'selected' : '' }}>30</option>
                    <option value="50" {{ ($perPage ?? 15) == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ ($perPage ?? 15) == 100 ? 'selected' : '' }}>100</option>
                    <option value="1000" {{ ($perPage ?? 15) == 1000 ? 'selected' : '' }}>1000</option>
                    <option value="2000" {{ ($perPage ?? 15) == 2000 ? 'selected' : '' }}>2000</option>
                    <option value="unlimited" {{ ($perPage ?? 15) == 'unlimited' ? 'selected' : '' }}>{{ __('Không giới hạn') }}</option>
                </select>
            </div>

            <!-- View Modes -->
            <div class="bg-muted border border-border rounded p-0.5 flex items-center">
                <button onclick="changeViewMode('card')" class="px-2.5 py-1 rounded-sm text-xs font-bold transition flex items-center {{ ($viewMode ?? 'card') == 'card' ? 'bg-card text-primary shadow-sm' : 'text-muted-foreground hover:text-foreground' }}">
                    <i data-lucide="layout-grid" class="w-3.5 h-3.5 mr-1"></i>
                    <span>{{ __('Cards') }}</span>
                </button>
                <button onclick="changeViewMode('grid')" class="px-2.5 py-1 rounded-sm text-xs font-bold transition flex items-center {{ ($viewMode ?? '') == 'grid' ? 'bg-card text-primary shadow-sm' : 'text-muted-foreground hover:text-foreground' }}">
                    <i data-lucide="grid" class="w-3.5 h-3.5 mr-1"></i>
                    <span>{{ __('Grid') }}</span>
                </button>
                <button onclick="changeViewMode('list')" class="px-2.5 py-1 rounded-sm text-xs font-bold transition flex items-center {{ ($viewMode ?? '') == 'list' ? 'bg-card text-primary shadow-sm' : 'text-muted-foreground hover:text-foreground' }}">
                    <i data-lucide="list" class="w-3.5 h-3.5 mr-1"></i>
                    <span>{{ __('List') }}</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Results Display -->
    @if(isset($patrons) && $patrons->count() > 0)
        <!-- Card View (Default) -->
        @if(($viewMode ?? 'card') == 'card')
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($patrons as $patron)
                    <div class="group relative bg-card border border-border rounded-md p-3 shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden flex flex-col justify-between">
                        <!-- Logo Watermark Background -->
                        <div class="absolute inset-0 flex items-center justify-center opacity-[0.02] pointer-events-none">
                            <img src="{{ asset('assets/imgs/logo-vttu.png') }}" class="w-1/2">
                        </div>
                        
                        <div>
                            <!-- Top Row: Label & Checkbox -->
                            <div class="flex justify-between items-start mb-3">
                                <span class="text-[10px] font-black text-primary tracking-wider uppercase">{{ __('Thẻ thư viện') }}</span>
                                <label class="cursor-pointer">
                                    <input type="checkbox" name="selected_patrons[]" value="{{ $patron->id }}" class="w-4 h-4 rounded border-border text-primary focus:ring-primary bg-background">
                                </label>
                            </div>

                            <!-- Middle Content -->
                            <div class="flex space-x-3">
                                <!-- Left: Profile Photo -->
                                <div class="w-[90px] h-[120px] flex-shrink-0 bg-muted border border-border rounded overflow-hidden relative">
                                    @if($patron->profile_image)
                                        <img src="{{ asset('storage/' . $patron->profile_image) }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center">
                                            <i data-lucide="user" class="w-8 h-8 text-muted-foreground"></i>
                                        </div>
                                    @endif
                                </div>

                                <!-- Right: Info Details -->
                                <div class="flex-1 flex flex-col pt-0.5">
                                    <h2 class="text-sm font-bold text-foreground uppercase leading-tight mb-2 truncate" title="{{ $patron->display_name }}">{{ $patron->display_name }}</h2>
                                    
                                    <div class="space-y-2">
                                        <div class="text-[10px] font-bold text-primary">
                                            Hạn: {{ date('d/m/Y', strtotime($patron->expiry_date)) }}
                                        </div>
                                        
                                        <!-- Barcode Area -->
                                        <div class="relative cursor-zoom-in" onclick="zoomBarcode(this, '{{ $patron->patron_code }}')">
                                            <div class="h-[35px] w-full bg-white dark:bg-slate-700 border border-border flex items-center justify-start overflow-hidden rounded-sm px-1">
                                                {!! $barcodeService->renderSvg($patron->patron_code) !!}
                                            </div>
                                            <div class="text-[9px] font-bold font-mono text-muted-foreground tracking-widest mt-1">
                                                {{ $patron->patron_code }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card Action Buttons -->
                        <div class="mt-3 pt-2 border-t border-border flex items-center justify-end space-x-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                            <!-- Lock/Unlock -->
                            @if($patron->card_status == 'normal')
                                <button type="button" onclick="openLockModal({{ json_encode(['id' => $patron->id, 'name' => $patron->display_name]) }})" class="p-1.5 bg-muted hover:bg-muted/80 border border-border rounded text-muted-foreground hover:text-amber-500 shadow-xs" title="{{ __('Lock Card') }}">
                                    <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                                </button>
                            @else
                                <button type="button" onclick="openUnlockModal({{ json_encode(['id' => $patron->id, 'name' => $patron->display_name, 'balance' => $patron->balance]) }})" class="p-1.5 bg-muted hover:bg-muted/80 border border-border rounded text-muted-foreground hover:text-emerald-500 shadow-xs" title="{{ __('Unlock Card') }}">
                                    <i data-lucide="unlock" class="w-3.5 h-3.5"></i>
                                </button>
                            @endif
                            
                            <!-- Financial Transaction -->
                            <button type="button" onclick="openTransactionModal({{ json_encode(['id' => $patron->id, 'name' => $patron->display_name, 'balance' => $patron->balance]) }})" class="p-1.5 bg-muted hover:bg-muted/80 border border-border rounded text-muted-foreground hover:text-blue-500 shadow-xs" title="{{ __('Financial Transaction') }}">
                                <i data-lucide="wallet" class="w-3.5 h-3.5"></i>
                            </button>
                            
                            <!-- Print Queue -->
                            @if($patron->isInPrintQueue())
                                <form action="{{ route('admin.patrons.remove-from-print-queue', $patron->id) }}" method="POST" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1.5 bg-muted hover:bg-muted/80 border border-border rounded text-muted-foreground hover:text-purple-500 shadow-xs" title="{{ __('Remove from Print Queue') }}">
                                        <i data-lucide="list-minus" class="w-3.5 h-3.5"></i>
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('admin.patrons.add-to-print-queue', $patron->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="p-1.5 bg-muted hover:bg-muted/80 border border-border rounded text-muted-foreground hover:text-slate-700 shadow-xs" title="{{ __('Add to Print Queue') }}">
                                        <i data-lucide="list-plus" class="w-3.5 h-3.5"></i>
                                    </button>
                                </form>
                            @endif
                            
                            <!-- Renew -->
                            <button type="button" onclick="openRenewModal({{ json_encode(['id' => $patron->id, 'name' => $patron->display_name, 'expiry' => $patron->expiry_date]) }})" 
                                class="p-1.5 bg-muted hover:bg-muted/80 border border-border rounded text-muted-foreground hover:text-primary shadow-xs" title="{{ __('Renew Card') }}">
                                <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                            </button>
                            
                            <!-- Edit -->
                            <a href="{{ route('admin.patrons.edit', $patron->id) }}" class="p-1.5 bg-muted hover:bg-muted/80 border border-border rounded text-muted-foreground hover:text-emerald-600 shadow-xs inline-flex items-center justify-center" title="{{ __('Edit') }}">
                                <i data-lucide="edit" class="w-3.5 h-3.5"></i>
                            </a>
                            
                            <!-- Delete -->
                            <button type="button" onclick="confirmDelete({{ $patron->id }}, '{{ $patron->display_name }}')" class="p-1.5 bg-muted hover:bg-muted/80 border border-border rounded text-muted-foreground hover:text-rose-500 shadow-xs">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>

                        <!-- Status Dot -->
                        <div class="absolute top-2.5 right-8">
                            <span class="flex h-2 w-2 relative">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75 {{ $patron->card_status == 'normal' ? 'bg-emerald-400' : 'bg-rose-400' }}"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 {{ $patron->card_status == 'normal' ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-12">
                        <p class="text-muted-foreground font-bold text-xs">{{ __('No patrons found.') }}</p>
                    </div>
                @endforelse
            </div>

        <!-- Grid View -->
        @elseif(($viewMode ?? '') == 'grid')
            <div class="bg-card rounded-md shadow-sm border border-border overflow-hidden">
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 p-3">
                    @forelse($patrons as $patron)
                        <div class="text-center p-3 border border-border rounded-md hover:bg-muted/50 transition-all duration-200 group cursor-pointer relative flex flex-col justify-between min-h-[160px]">
                            <!-- Checkbox for bulk selection -->
                            <div class="absolute top-2 left-2 opacity-0 group-hover:opacity-100 transition-opacity z-10">
                                <label class="cursor-pointer">
                                    <input type="checkbox" name="selected_patrons[]" value="{{ $patron->id }}" class="w-4 h-4 rounded border-border text-primary focus:ring-primary bg-background">
                                </label>
                            </div>
                            
                            <!-- Avatar -->
                            <div class="w-14 h-14 mx-auto mb-2 bg-muted rounded-full overflow-hidden relative border border-border">
                                @if($patron->profile_image)
                                    <img src="{{ asset('storage/' . $patron->profile_image) }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <i data-lucide="user" class="w-6 h-6 text-muted-foreground"></i>
                                    </div>
                                @endif
                                <!-- Status indicator -->
                                <div class="absolute bottom-0 right-0 w-3 h-3 rounded-full border border-card {{ $patron->card_status == 'normal' ? 'bg-emerald-500' : 'bg-rose-500' }}"></div>
                            </div>
                            
                            <!-- Name -->
                            <h3 class="font-bold text-xs text-foreground truncate mb-0.5" title="{{ $patron->display_name }}">
                                {{ $patron->display_name }}
                            </h3>
                            
                            <!-- Code -->
                            <p class="text-[10px] text-muted-foreground font-mono mb-1">{{ $patron->patron_code }}</p>
                            
                            <!-- Group -->
                            @if($patron->patronGroup)
                                <p class="text-[10px] font-bold text-primary mb-1.5 truncate">{{ $patron->patronGroup->name }}</p>
                            @endif
                            
                            <!-- Quick Actions -->
                            <div class="flex justify-center space-x-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                @if($patron->card_status == 'normal')
                                    <button type="button" onclick="openLockModal({{ json_encode(['id' => $patron->id, 'name' => $patron->display_name]) }})" class="p-1 bg-muted border border-border rounded text-muted-foreground hover:text-amber-500" title="{{ __('Lock') }}">
                                        <i data-lucide="lock" class="w-3 h-3"></i>
                                    </button>
                                @else
                                    <button type="button" onclick="openUnlockModal({{ json_encode(['id' => $patron->id, 'name' => $patron->display_name, 'balance' => $patron->balance]) }})" class="p-1 bg-muted border border-border rounded text-muted-foreground hover:text-emerald-500" title="{{ __('Unlock') }}">
                                        <i data-lucide="unlock" class="w-3 h-3"></i>
                                    </button>
                                @endif
                                <button onclick="openRenewModal({{ json_encode(['id' => $patron->id, 'name' => $patron->display_name, 'expiry' => $patron->expiry_date]) }})" 
                                    class="p-1 bg-muted border border-border rounded text-muted-foreground hover:text-primary" title="{{ __('Renew') }}">
                                    <i data-lucide="calendar" class="w-3 h-3"></i>
                                </button>
                                <button type="button" onclick="confirmDelete({{ $patron->id }}, '{{ $patron->display_name }}')" class="p-1 bg-muted border border-border rounded text-muted-foreground hover:text-rose-500">
                                    <i data-lucide="trash-2" class="w-3 h-3"></i>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-12">
                            <p class="text-muted-foreground font-bold text-xs">{{ __('No patrons found.') }}</p>
                        </div>
                    @endforelse
                </div>
            </div>

        <!-- List View -->
        @elseif(($viewMode ?? '') == 'list')
            <div class="bg-card rounded-md shadow-sm border border-border overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-border bg-muted/30">
                                <th class="py-2 px-3 text-left text-xs font-bold text-muted-foreground uppercase tracking-wide">
                                    <input type="checkbox" class="w-4 h-4 rounded border-border text-primary focus:ring-primary bg-background">
                                </th>
                                <th class="py-2 px-3 text-left text-xs font-bold text-muted-foreground uppercase tracking-wide">{{ __('Patron') }}</th>
                                <th class="py-2 px-3 text-left text-xs font-bold text-muted-foreground uppercase tracking-wide">{{ __('Code') }}</th>
                                <th class="py-2 px-3 text-left text-xs font-bold text-muted-foreground uppercase tracking-wide">{{ __('Email') }}</th>
                                <th class="py-2 px-3 text-left text-xs font-bold text-muted-foreground uppercase tracking-wide">{{ __('Phone') }}</th>
                                <th class="py-2 px-3 text-left text-xs font-bold text-muted-foreground uppercase tracking-wide">{{ __('Group') }}</th>
                                <th class="py-2 px-3 text-left text-xs font-bold text-muted-foreground uppercase tracking-wide">{{ __('Branch') }}</th>
                                <th class="py-2 px-3 text-left text-xs font-bold text-muted-foreground uppercase tracking-wide">{{ __('Status') }}</th>
                                <th class="py-2 px-3 text-left text-xs font-bold text-muted-foreground uppercase tracking-wide">{{ __('Registration') }}</th>
                                <th class="py-2 px-3 text-left text-xs font-bold text-muted-foreground uppercase tracking-wide">{{ __('Expiry') }}</th>
                                <th class="py-2 px-3 text-left text-xs font-bold text-muted-foreground uppercase tracking-wide">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            @forelse($patrons as $patron)
                                <tr class="hover:bg-muted/50 transition-colors">
                                    <td class="py-2 px-3 whitespace-nowrap">
                                        <input type="checkbox" name="selected_patrons[]" value="{{ $patron->id }}" class="w-4 h-4 rounded border-border text-primary focus:ring-primary bg-background">
                                    </td>
                                    <td class="py-2 px-3 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 bg-muted rounded-full overflow-hidden mr-2.5 border border-border">
                                                @if($patron->profile_image)
                                                    <img src="{{ asset('storage/' . $patron->profile_image) }}" class="w-full h-full object-cover">
                                                @else
                                                    <div class="w-full h-full flex items-center justify-center">
                                                        <i data-lucide="user" class="w-4 h-4 text-muted-foreground"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <div>
                                                <div class="text-xs font-bold text-foreground">{{ $patron->display_name }}</div>
                                                <div class="text-[10px] text-muted-foreground">{{ $patron->user->email ?? '' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-2 px-3 whitespace-nowrap text-xs font-mono text-foreground">
                                        {{ $patron->patron_code }}
                                    </td>
                                    <td class="py-2 px-3 whitespace-nowrap text-xs text-foreground">
                                        {{ $patron->user->email ?? '' }}
                                    </td>
                                    <td class="py-2 px-3 whitespace-nowrap text-xs text-foreground">
                                        {{ $patron->phone ?? '-' }}
                                    </td>
                                    <td class="py-2 px-3 whitespace-nowrap text-xs text-foreground">
                                        {{ $patron->patronGroup->name ?? '-' }}
                                    </td>
                                    <td class="py-2 px-3 whitespace-nowrap text-xs text-foreground">
                                        {{ $patron->branch ?? '-' }}
                                    </td>
                                    <td class="py-2 px-3 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-sm text-[10px] font-bold uppercase {{ $patron->card_status == 'normal' ? 'bg-emerald-500/10 text-emerald-500' : 'bg-rose-500/10 text-rose-500' }}">
                                            <span class="w-1.5 h-1.5 mr-1.5 rounded-full {{ $patron->card_status == 'normal' ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                            {{ $patron->card_status == 'normal' ? __('Active') : __('Locked') }}
                                        </span>
                                    </td>
                                    <td class="py-2 px-3 whitespace-nowrap text-xs text-foreground">
                                        {{ date('d/m/Y', strtotime($patron->registration_date)) }}
                                    </td>
                                    <td class="py-2 px-3 whitespace-nowrap text-xs text-foreground">
                                        <span class="{{ \Carbon\Carbon::parse($patron->expiry_date)->isPast() ? 'text-rose-500 font-semibold' : '' }}">
                                            {{ date('d/m/Y', strtotime($patron->expiry_date)) }}
                                        </span>
                                    </td>
                                    <td class="py-2 px-3 whitespace-nowrap">
                                        <div class="flex items-center space-x-1.5">
                                            <!-- Lock/Unlock -->
                                            @if($patron->card_status == 'normal')
                                                <button type="button" onclick="openLockModal({{ json_encode(['id' => $patron->id, 'name' => $patron->display_name]) }})" class="text-muted-foreground hover:text-amber-500" title="{{ __('Lock Card') }}">
                                                    <i data-lucide="lock" class="w-4 h-4"></i>
                                                </button>
                                            @else
                                                <button type="button" onclick="openUnlockModal({{ json_encode(['id' => $patron->id, 'name' => $patron->display_name, 'balance' => $patron->balance]) }})" class="text-muted-foreground hover:text-emerald-500" title="{{ __('Unlock Card') }}">
                                                    <i data-lucide="unlock" class="w-4 h-4"></i>
                                                </button>
                                            @endif
                                            
                                            <!-- Financial Transaction -->
                                            <button type="button" onclick="openTransactionModal({{ json_encode(['id' => $patron->id, 'name' => $patron->display_name, 'balance' => $patron->balance]) }})" class="text-muted-foreground hover:text-blue-500" title="{{ __('Financial Transaction') }}">
                                                <i data-lucide="wallet" class="w-4 h-4"></i>
                                            </button>
                                            
                                            <!-- Print Queue -->
                                            @if($patron->isInPrintQueue())
                                                <form action="{{ route('admin.patrons.remove-from-print-queue', $patron->id) }}" method="POST" class="inline">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="text-muted-foreground hover:text-purple-500" title="{{ __('Remove from Print Queue') }}">
                                                        <i data-lucide="list-minus" class="w-4 h-4"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('admin.patrons.add-to-print-queue', $patron->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-muted-foreground hover:text-slate-700" title="{{ __('Add to Print Queue') }}">
                                                        <i data-lucide="list-plus" class="w-4 h-4"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            
                                            <!-- Renew -->
                                            <button onclick="openRenewModal({{ json_encode(['id' => $patron->id, 'name' => $patron->display_name, 'expiry' => $patron->expiry_date]) }})" 
                                                class="text-muted-foreground hover:text-primary" title="{{ __('Renew') }}">
                                                <i data-lucide="calendar" class="w-4 h-4"></i>
                                            </button>
                                            
                                            <!-- Edit -->
                                            <a href="{{ route('admin.patrons.edit', $patron->id) }}" class="text-muted-foreground hover:text-emerald-500" title="{{ __('Edit') }}">
                                                <i data-lucide="edit" class="w-4 h-4"></i>
                                            </a>
                                            
                                            <!-- Delete -->
                                            <button type="button" onclick="confirmDelete({{ $patron->id }}, '{{ $patron->display_name }}')" class="text-muted-foreground hover:text-rose-500">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="py-6 text-center text-muted-foreground font-bold text-xs">
                                        {{ __('No patrons found.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Pagination -->
        @if($patrons->hasPages())
            <div class="mt-4">
                {{ $patrons->links() }}
            </div>
        @endif
    @else
        <div class="bg-card rounded-md shadow-sm border border-border p-8 text-center">
            <i data-lucide="alert-circle" class="w-12 h-12 text-muted-foreground mx-auto mb-3"></i>
            <h3 class="text-sm font-bold text-foreground mb-1">{{ __('No patrons found') }}</h3>
            <p class="text-xs text-muted-foreground">{{ __('Try adjusting your search criteria or filters.') }}</p>
        </div>
    @endif

    <!-- Barcode Zoom Modal -->
    <div id="barcodeZoomModal" class="fixed inset-0 z-[100] hidden flex items-center justify-center p-4 bg-slate-900/90 backdrop-blur-xs transition-all duration-300" onclick="closeBarcodeZoom()">
        <div class="relative bg-card p-6 rounded-md border border-border shadow-lg max-w-md w-full transform transition-all duration-300 scale-95 opacity-0" id="barcodeZoomContent" onclick="event.stopPropagation()">
            <button onclick="closeBarcodeZoom()" class="absolute -top-10 right-0 text-white hover:text-gray-300 transition-colors">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
            
            <div class="text-center">
                <h3 class="text-sm font-black text-foreground mb-4 uppercase tracking-widest" id="zoomPatronCode"></h3>
                <div id="zoomedBarcodeContainer" class="bg-white p-4 rounded border border-border flex justify-center items-center min-h-[100px]">
                </div>
                <p class="mt-4 text-muted-foreground text-xs font-bold uppercase tracking-wider">
                    {{ __('Nhấn ESC hoặc vùng ngoài để đóng') }}
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Renew Modal -->
<div id="renewModal" class="fixed inset-0 z-[110] hidden flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-xs" onclick="closeRenewModal()">
    <div class="relative bg-card rounded-md border border-border shadow-lg w-full max-w-sm overflow-hidden" onclick="event.stopPropagation()">
        <div class="p-4 border-b border-border text-center">
            <h3 class="text-sm font-black text-foreground tracking-widest uppercase">{{ __('Gia hạn thẻ') }}</h3>
            <p class="text-primary text-[10px] font-bold mt-1 uppercase" id="renewPatronName"></p>
        </div>
        <form id="renewForm" method="POST" class="p-4 space-y-4">
            @csrf @method('PATCH')
            <div class="space-y-1.5">
                <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">{{ __('Ngày hết hạn mới') }}</label>
                <input type="date" name="expiry_date" id="renew_expiry_date" required 
                    class="w-full bg-muted border border-border rounded px-3 py-2 text-xs font-bold text-foreground focus:bg-background focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all">
            </div>
            <div class="flex space-x-2 pt-2 border-t border-border">
                <button type="button" onclick="closeRenewModal()" class="flex-1 bg-muted hover:bg-muted/80 text-foreground border border-border py-2 rounded text-[10px] font-black uppercase transition-all">{{ __('Hủy') }}</button>
                <button type="submit" class="flex-1 bg-primary text-primary-foreground py-2 rounded text-[10px] font-black uppercase shadow-xs hover:bg-primary/95 transition-all">{{ __('Cập nhật') }}</button>
            </div>
        </form>
    </div>
</div>

<!-- Include Bulk Edit Modal -->
@include('admin.patrons.bulk-edit')

<!-- Include Patron Management Modals -->
@include('admin.patrons.modals')

<!-- Transaction Modal with 5 Tabs -->
<div id="transactionModal" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-xs" onclick="closeTransactionModal()"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-card border border-border rounded-md shadow-lg w-full max-w-2xl overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-border bg-muted/30">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-black text-foreground tracking-widest uppercase">{{ __('Giao dịch tài chính') }}</h3>
                    <p class="text-primary text-[10px] font-bold mt-1 uppercase" id="transactionPatronName"></p>
                    <div class="flex items-center space-x-2 mt-1">
                        <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider">{{ __('Số dư hiện tại') }}:</span>
                        <span class="text-xs font-bold text-foreground" id="currentBalance"></span>
                    </div>
                </div>
                <button type="button" onclick="closeTransactionModal()" class="text-muted-foreground hover:text-foreground transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
        </div>

        <!-- Tabs Navigation -->
        <div class="flex border-b border-border bg-muted/50">
            <button type="button" onclick="switchTransactionTab('add')" id="tab-add" class="transaction-tab flex-1 py-3 text-center transition-all border-b-2 border-emerald-500 text-emerald-600 bg-emerald-500/5">
                <i data-lucide="plus-circle" class="w-4 h-4 mx-auto mb-1"></i>
                <span class="text-[9px] font-black uppercase tracking-wider">{{ __('Nạp tiền') }}</span>
            </button>
            <button type="button" onclick="switchTransactionTab('print')" id="tab-print" class="transaction-tab flex-1 py-3 text-center transition-all border-b-2 border-transparent text-muted-foreground hover:text-foreground">
                <i data-lucide="printer" class="w-4 h-4 mx-auto mb-1"></i>
                <span class="text-[9px] font-black uppercase tracking-wider">{{ __('In ấn') }}</span>
            </button>
            <button type="button" onclick="switchTransactionTab('fine')" id="tab-fine" class="transaction-tab flex-1 py-3 text-center transition-all border-b-2 border-transparent text-muted-foreground hover:text-foreground">
                <i data-lucide="alert-triangle" class="w-4 h-4 mx-auto mb-1"></i>
                <span class="text-[9px] font-black uppercase tracking-wider">{{ __('Phạt sách') }}</span>
            </button>
            <button type="button" onclick="switchTransactionTab('service')" id="tab-service" class="transaction-tab flex-1 py-3 text-center transition-all border-b-2 border-transparent text-muted-foreground hover:text-foreground">
                <i data-lucide="settings" class="w-4 h-4 mx-auto mb-1"></i>
                <span class="text-[9px] font-black uppercase tracking-wider">{{ __('Dịch vụ khác') }}</span>
            </button>
            <button type="button" onclick="switchTransactionTab('withdraw')" id="tab-withdraw" class="transaction-tab flex-1 py-3 text-center transition-all border-b-2 border-transparent text-muted-foreground hover:text-foreground">
                <i data-lucide="minus-circle" class="w-4 h-4 mx-auto mb-1"></i>
                <span class="text-[9px] font-black uppercase tracking-wider">{{ __('Rút tiền') }}</span>
            </button>
        </div>

        <!-- Tab Content -->
        <form id="transactionForm" method="POST" class="p-4">
            @csrf
            
            <!-- Add Money Tab -->
            <div id="tab-content-add" class="transaction-content space-y-4">
                <div class="bg-emerald-500/10 border border-emerald-500/20 rounded p-3">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-9 h-9 bg-emerald-500/20 text-emerald-500 rounded flex items-center justify-center">
                            <i data-lucide="plus-circle" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-emerald-800 dark:text-emerald-200">{{ __('Nạp tiền vào tài khoản') }}</h4>
                            <p class="text-[10px] text-emerald-600 dark:text-emerald-400">{{ __('Tăng số dư cho độc giả') }}</p>
                        </div>
                    </div>
                </div>
                
                <input type="hidden" name="type" value="add">
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">{{ __('Số tiền') }} <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-emerald-500 font-bold text-xs">₫</span>
                            <input type="number" name="amount" required step="0.01" min="0.01" placeholder="0"
                                class="w-full bg-muted border border-border rounded pl-8 pr-3 py-2 text-xs font-bold text-foreground focus:bg-background focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all">
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">{{ __('Phương thức') }}</label>
                        <select name="payment_method" class="w-full bg-muted border border-border rounded px-3 py-2 text-xs font-bold text-foreground focus:bg-background focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all">
                            <option value="cash">{{ __('Tiền mặt') }}</option>
                            <option value="transfer">{{ __('Chuyển khoản') }}</option>
                            <option value="card">{{ __('Thẻ ngân hàng') }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Print Fee Tab -->
            <div id="tab-content-print" class="transaction-content space-y-4 hidden">
                <div class="bg-blue-500/10 border border-blue-500/20 rounded p-3">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-9 h-9 bg-blue-500/20 text-blue-500 rounded flex items-center justify-center">
                            <i data-lucide="printer" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-blue-800 dark:text-blue-200">{{ __('Phí in ấn') }}</h4>
                            <p class="text-[10px] text-blue-600 dark:text-blue-400">{{ __('Phí in tài liệu, sách, báo cáo') }}</p>
                        </div>
                    </div>
                </div>
                
                <input type="hidden" name="type" value="print">
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">{{ __('Số tiền') }} <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-blue-500 font-bold text-xs">₫</span>
                            <input type="number" name="amount" required step="0.01" min="0.01" placeholder="0"
                                class="w-full bg-muted border border-border rounded pl-8 pr-3 py-2 text-xs font-bold text-foreground focus:bg-background focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">{{ __('Loại in') }}</label>
                        <select name="print_type" class="w-full bg-muted border border-border rounded px-3 py-2 text-xs font-bold text-foreground focus:bg-background focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                            <option value="document">{{ __('Tài liệu') }}</option>
                            <option value="book">{{ __('Sách') }}</option>
                            <option value="report">{{ __('Báo cáo') }}</option>
                            <option value="other">{{ __('Khác') }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Fine Tab -->
            <div id="tab-content-fine" class="transaction-content space-y-4 hidden">
                <div class="bg-rose-500/10 border border-rose-500/20 rounded p-3">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-9 h-9 bg-rose-500/20 text-rose-500 rounded flex items-center justify-center">
                            <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-rose-800 dark:text-rose-200">{{ __('Phạt mượn sách') }}</h4>
                            <p class="text-[10px] text-rose-600 dark:text-rose-400">{{ __('Phạt trả muộn, làm mất, hư hỏng') }}</p>
                        </div>
                    </div>
                </div>
                
                <input type="hidden" name="type" value="fine">
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">{{ __('Số tiền') }} <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-rose-500 font-bold text-xs">₫</span>
                            <input type="number" name="amount" required step="0.01" min="0.01" placeholder="0"
                                class="w-full bg-muted border border-border rounded pl-8 pr-3 py-2 text-xs font-bold text-foreground focus:bg-background focus:ring-1 focus:ring-rose-500 focus:border-rose-500 outline-none transition-all">
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">{{ __('Loại phạt') }}</label>
                        <select name="fine_type" class="w-full bg-muted border border-border rounded px-3 py-2 text-xs font-bold text-foreground focus:bg-background focus:ring-1 focus:ring-rose-500 focus:border-rose-500 outline-none transition-all">
                            <option value="late">{{ __('Trả muộn') }}</option>
                            <option value="lost">{{ __('Làm mất') }}</option>
                            <option value="damaged">{{ __('Hư hỏng') }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Service Fee Tab -->
            <div id="tab-content-service" class="transaction-content space-y-4 hidden">
                <div class="bg-purple-500/10 border border-purple-500/20 rounded p-3">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-9 h-9 bg-purple-500/20 text-purple-500 rounded flex items-center justify-center">
                            <i data-lucide="settings" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-purple-800 dark:text-purple-200">{{ __('Dịch vụ khác') }}</h4>
                            <p class="text-[10px] text-purple-600 dark:text-purple-400">{{ __('Phí dịch vụ thư viện khác') }}</p>
                        </div>
                    </div>
                </div>
                
                <input type="hidden" name="type" value="service">
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">{{ __('Số tiền') }} <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-purple-500 font-bold text-xs">₫</span>
                            <input type="number" name="amount" required step="0.01" min="0.01" placeholder="0"
                                class="w-full bg-muted border border-border rounded pl-8 pr-3 py-2 text-xs font-bold text-foreground focus:bg-background focus:ring-1 focus:ring-purple-500 focus:border-purple-500 outline-none transition-all">
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">{{ __('Loại dịch vụ') }}</label>
                        <select name="service_type" class="w-full bg-muted border border-border rounded px-3 py-2 text-xs font-bold text-foreground focus:bg-background focus:ring-1 focus:ring-purple-500 focus:border-purple-500 outline-none transition-all">
                            <option value="membership">{{ __('Thành viên') }}</option>
                            <option value="research">{{ __('Nghiên cứu') }}</option>
                            <option value="consulting">{{ __('Tư vấn') }}</option>
                            <option value="other">{{ __('Khác') }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Withdraw Tab -->
            <div id="tab-content-withdraw" class="transaction-content space-y-4 hidden">
                <div class="bg-amber-500/10 border border-amber-500/20 rounded p-3">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-9 h-9 bg-amber-500/20 text-amber-500 rounded flex items-center justify-center">
                            <i data-lucide="minus-circle" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-amber-800 dark:text-amber-200">{{ __('Rút tiền khỏi tài khoản') }}</h4>
                            <p class="text-[10px] text-amber-600 dark:text-amber-400">{{ __('Hoàn tiền cho độc giả') }}</p>
                        </div>
                    </div>
                </div>
                
                <input type="hidden" name="type" value="withdraw">
                
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">{{ __('Số tiền') }} <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-amber-600 font-bold text-xs">₫</span>
                        <input type="number" name="amount" required step="0.01" min="0.01" placeholder="0"
                            class="w-full bg-muted border border-border rounded pl-8 pr-3 py-2 text-xs font-bold text-foreground focus:bg-background focus:ring-1 focus:ring-amber-500 focus:border-amber-500 outline-none transition-all">
                    </div>
                    <p class="text-[10px] text-muted-foreground uppercase font-bold">{{ __('Số dư khả dụng') }}: <span id="availableBalance" class="text-foreground font-black"></span> ₫</p>
                </div>
            </div>

            <!-- Common Fields -->
            <div class="space-y-3 pt-3 border-t border-border">
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">{{ __('Mô tả') }}</label>
                    <input type="text" name="description" placeholder="{{ __('Nhập mô tả giao dịch...') }}"
                        class="w-full bg-muted border border-border rounded px-3 py-2 text-xs font-bold text-foreground focus:bg-background focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all">
                </div>
                
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">{{ __('Ghi chú') }}</label>
                    <textarea name="notes" rows="2" placeholder="{{ __('Nhập ghi chú thêm...') }}"
                        class="w-full bg-muted border border-border rounded px-3 py-2 text-xs font-bold text-foreground focus:bg-background focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all resize-none"></textarea>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex space-x-3 pt-4 border-t border-border">
                <button type="button" onclick="closeTransactionModal()" class="flex-1 bg-muted hover:bg-muted/85 border border-border text-foreground py-2 rounded text-[10px] font-black uppercase transition-all">
                    {{ __('Hủy') }}
                </button>
                <button type="submit" class="flex-1 bg-primary text-primary-foreground py-2 rounded text-[10px] font-black uppercase hover:bg-primary/95 transition-all shadow-xs">
                    {{ __('Xác nhận giao dịch') }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openTransactionModal(patron) {
    document.getElementById('transactionForm').action = `{{ route('admin.patrons.transactions.store', ['id' => ':id']) }}`.replace(':id', patron.id);
    document.getElementById('transactionPatronName').textContent = patron.name;
    document.getElementById('currentBalance').textContent = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(patron.balance);
    document.getElementById('availableBalance').textContent = new Intl.NumberFormat('vi-VN').format(patron.balance);
    document.getElementById('transactionModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeTransactionModal() {
    document.getElementById('transactionModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function switchTransactionTab(tab) {
    // Hide all content
    document.querySelectorAll('.transaction-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active state from all tabs
    document.querySelectorAll('.transaction-tab').forEach(tabBtn => {
        tabBtn.className = "transaction-tab flex-1 py-3 text-center transition-all border-b-2 border-transparent text-muted-foreground hover:text-foreground";
    });
    
    // Show selected content
    document.getElementById('tab-content-' + tab).classList.remove('hidden');
    
    // Activate selected tab
    const activeTab = document.getElementById('tab-' + tab);
    
    if (tab === 'add') {
        activeTab.className = "transaction-tab flex-1 py-3 text-center transition-all border-b-2 border-emerald-500 text-emerald-600 bg-emerald-500/5";
    } else if (tab === 'print') {
        activeTab.className = "transaction-tab flex-1 py-3 text-center transition-all border-b-2 border-blue-500 text-blue-600 bg-blue-500/5";
    } else if (tab === 'fine') {
        activeTab.className = "transaction-tab flex-1 py-3 text-center transition-all border-b-2 border-rose-500 text-rose-600 bg-rose-500/5";
    } else if (tab === 'service') {
        activeTab.className = "transaction-tab flex-1 py-3 text-center transition-all border-b-2 border-purple-500 text-purple-600 bg-purple-500/5";
    } else if (tab === 'withdraw') {
        activeTab.className = "transaction-tab flex-1 py-3 text-center transition-all border-b-2 border-amber-500 text-amber-600 bg-amber-500/5";
    }
}

// Bulk Edit & Selection script helpers
function updateBulkActions() {
    const checkboxes = document.querySelectorAll('input[name="selected_patrons[]"]:checked');
    const section = document.getElementById('bulkActionsSection');
    const countSpan = document.getElementById('selectedCount');
    
    if (checkboxes.length > 0) {
        section.style.display = 'block';
        countSpan.textContent = checkboxes.length;
    } else {
        section.style.display = 'none';
        countSpan.textContent = '0';
    }
}

function openBulkEditModal() {
    const checkboxes = document.querySelectorAll('input[name="selected_patrons[]"]:checked');
    const selectedPatrons = Array.from(checkboxes).map(cb => cb.value);
    
    if (selectedPatrons.length === 0) {
        alert('{{ __("Vui lòng chọn ít nhất một bạn đọc để chỉnh sửa.") }}');
        return;
    }
    
    // Set patron IDs
    document.getElementById('bulkEditPatronIds').value = selectedPatrons.join(',');
    document.getElementById('selectedPatronsCount').textContent = selectedPatrons.length;
    
    // Show modal
    document.getElementById('bulkEditModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function clearSelection() {
    const checkboxes = document.querySelectorAll('input[name="selected_patrons[]"]');
    checkboxes.forEach(cb => cb.checked = false);
    const selectAllCheckbox = document.querySelector('input[type="checkbox"]:not([name="selected_patrons[]"])');
    if (selectAllCheckbox) selectAllCheckbox.checked = false;
    updateBulkActions();
}

function confirmBulkDelete() {
    const checkboxes = document.querySelectorAll('input[name="selected_patrons[]"]:checked');
    const selectedPatrons = Array.from(checkboxes).map(cb => cb.value);
    
    if (selectedPatrons.length === 0) {
        alert('{{ __("Vui lòng chọn ít nhất một bạn đọc để xóa.") }}');
        return;
    }
    
    if (confirm(`{{ __("Bạn có chắc chắn muốn xóa") }} ${selectedPatrons.length} {{ __("bạn đọc không? Hành động này không thể hoàn tác!") }}`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("admin.patrons.bulk.delete") }}';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        form.appendChild(csrfToken);
        
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        form.appendChild(methodField);
        
        selectedPatrons.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'patron_ids[]';
            input.value = id;
            form.appendChild(input);
        });
        
        document.body.appendChild(form);
        form.submit();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('input[name="selected_patrons[]"]');
    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateBulkActions);
    });
    
    const selectAllCheckbox = document.querySelector('input[type="checkbox"]:not([name="selected_patrons[]"])');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('input[name="selected_patrons[]"]');
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateBulkActions();
        });
    }
});

function confirmDelete(patronId, patronName) {
    Swal.fire({
        title: '{{ __("Xác nhận xóa?") }}',
        html: `{{ __("Bạn có chắc chắn muốn xóa độc giả") }} <strong>${patronName}</strong> {{ __("không?") }}<br><br><small class="text-red-500 font-bold">{{ __("Hành động này không thể hoàn tác!") }}</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '{{ __("Xóa") }}',
        cancelButtonText: '{{ __("Hủy") }}',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `{{ route('admin.patrons.destroy', ['id' => ':id']) }}`.replace(':id', patronId);
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);
            
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);
            
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function changeSort(sortOrder) {
    const url = new URL(window.location);
    url.searchParams.set('sort', sortOrder);
    window.location.href = url.toString();
}

function toggleFilters() {
    const filtersDiv = document.getElementById('advancedFilters');
    const icon = document.getElementById('filterToggleIcon');
    const text = document.getElementById('filterToggleText');
    const searchIcon = document.getElementById('searchIcon');
    const searchInput = document.querySelector('input[name="search"]');
    const url = new URL(window.location);
    
    if (filtersDiv.classList.contains('hidden')) {
        filtersDiv.classList.remove('hidden');
        icon.style.transform = 'rotate(180deg)';
        text.textContent = '{{ __("Hide Filters") }}';
        if (searchIcon) {
            searchIcon.style.opacity = '0';
            searchIcon.style.pointerEvents = 'none';
        }
        if (searchInput) {
            searchInput.classList.remove('pl-9');
            searchInput.classList.add('pl-3');
        }
        localStorage.setItem('filtersOpen', 'true');
        url.searchParams.set('filters', 'open');
    } else {
        filtersDiv.classList.add('hidden');
        icon.style.transform = 'rotate(0deg)';
        text.textContent = '{{ __("Show Filters") }}';
        if (searchIcon) {
            searchIcon.style.opacity = '1';
            searchIcon.style.pointerEvents = 'auto';
        }
        if (searchInput) {
            searchInput.classList.remove('pl-3');
            searchInput.classList.add('pl-9');
        }
        localStorage.setItem('filtersOpen', 'false');
        url.searchParams.delete('filters');
    }
    window.history.replaceState({}, '', url);
}

document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const filtersOpen = urlParams.get('filters') === 'open' || localStorage.getItem('filtersOpen') === 'true';
    
    if (filtersOpen) {
        const filtersDiv = document.getElementById('advancedFilters');
        const icon = document.getElementById('filterToggleIcon');
        const text = document.getElementById('filterToggleText');
        const searchIcon = document.getElementById('searchIcon');
        const searchInput = document.querySelector('input[name="search"]');
        
        if (filtersDiv && icon && text) {
            filtersDiv.classList.remove('hidden');
            icon.style.transform = 'rotate(180deg)';
            text.textContent = '{{ __("Hide Filters") }}';
            if (searchIcon) {
                searchIcon.style.opacity = '0';
                searchIcon.style.pointerEvents = 'none';
            }
            if (searchInput) {
                searchInput.classList.remove('pl-9');
                searchInput.classList.add('pl-3');
            }
            if (!urlParams.has('filters')) {
                const url = new URL(window.location);
                url.searchParams.set('filters', 'open');
                window.history.replaceState({}, '', url);
            }
        }
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const mainSearchForm = document.getElementById('mainSearchForm');
    if (mainSearchForm) {
        mainSearchForm.addEventListener('submit', function(e) {
            const statusSelect = document.querySelector('#advancedFiltersForm select[name="status"]');
            const patronGroupRadio = document.querySelector('#advancedFiltersForm input[name="patron_group"]:checked');
            const branchSelect = document.querySelector('#advancedFiltersForm select[name="branch"]');
            const perPageSelect = document.querySelector('#advancedFiltersForm select[name="per_page"]');
            const dateFromInput = document.querySelector('#advancedFiltersForm input[name="date_from"]');
            const dateToInput = document.querySelector('#advancedFiltersForm input[name="date_to"]');
            const expiryStatusSelect = document.querySelector('#advancedFiltersForm select[name="expiry_status"]');
            const expiringInDaysInput = document.querySelector('#advancedFiltersForm input[name="expiring_in_days"]');
            
            if (statusSelect) {
                document.querySelector('#mainSearchForm input[name="status"]').value = statusSelect.value;
            }
            if (patronGroupRadio) {
                document.querySelector('#mainSearchForm input[name="patron_group"]').value = patronGroupRadio.value;
            }
            if (branchSelect) {
                document.querySelector('#mainSearchForm input[name="branch"]').value = branchSelect.value;
            }
            if (perPageSelect) {
                document.querySelector('#mainSearchForm input[name="per_page"]').value = perPageSelect.value;
            }
            if (dateFromInput) {
                document.querySelector('#mainSearchForm input[name="date_from"]').value = dateFromInput.value;
            }
            if (dateToInput) {
                document.querySelector('#mainSearchForm input[name="date_to"]').value = dateToInput.value;
            }
            if (expiryStatusSelect) {
                document.querySelector('#mainSearchForm input[name="expiry_status"]').value = expiryStatusSelect.value;
            }
            if (expiringInDaysInput) {
                document.querySelector('#mainSearchForm input[name="expiring_in_days"]').value = expiringInDaysInput.value;
            }
        });
    }
    
    const advancedFiltersForm = document.getElementById('advancedFiltersForm');
    if (advancedFiltersForm) {
        advancedFiltersForm.addEventListener('submit', function(e) {
            const searchFieldSelect = document.querySelector('#mainSearchForm select[name="search_field"]');
            const searchInput = document.querySelector('#mainSearchForm input[name="search"]');
            
            if (searchFieldSelect) {
                document.querySelector('#advancedFiltersForm input[name="search_field"]').value = searchFieldSelect.value;
            }
            if (searchInput) {
                document.querySelector('#advancedFiltersForm input[name="search"]').value = searchInput.value;
            }
        });
    }
});

function clearFilters() {
    const url = new URL(window.location);
    url.searchParams.delete('search');
    url.searchParams.delete('search_field');
    url.searchParams.delete('status');
    url.searchParams.delete('patron_group');
    url.searchParams.delete('branch');
    url.searchParams.delete('date_from');
    url.searchParams.delete('date_to');
    url.searchParams.delete('expiry_status');
    url.searchParams.delete('expiring_in_days');
    url.searchParams.delete('per_page');
    window.location.href = url.toString();
}
</script>

<script>
function changeViewMode(mode) {
    const url = new URL(window.location);
    url.searchParams.set('view_mode', mode);
    window.location.href = url.toString();
}

function changePerPage(count) {
    const url = new URL(window.location);
    url.searchParams.set('per_page', count);
    window.location.href = url.toString();
}

function zoomBarcode(element, patronCode) {
    const modal = document.getElementById('barcodeZoomModal');
    const content = document.getElementById('barcodeZoomContent');
    const container = document.getElementById('zoomedBarcodeContainer');
    const codeTitle = document.getElementById('zoomPatronCode');
    const svg = element.querySelector('svg').cloneNode(true);
    
    container.innerHTML = '';
    svg.setAttribute('width', '100%');
    svg.setAttribute('height', '120');
    container.appendChild(svg);
    codeTitle.textContent = patronCode;
    
    modal.classList.remove('hidden');
    setTimeout(() => {
        content.classList.remove('scale-95', 'opacity-0');
        content.classList.add('scale-100', 'opacity-100');
    }, 10);
    
    document.body.style.overflow = 'hidden';
    
    const escHandler = (e) => {
        if (e.key === 'Escape') {
            closeBarcodeZoom();
            document.removeEventListener('keydown', escHandler);
        }
    };
    document.addEventListener('keydown', escHandler);
}

function closeBarcodeZoom() {
    const modal = document.getElementById('barcodeZoomModal');
    const content = document.getElementById('barcodeZoomContent');
    
    if (content) {
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');
    }
    
    setTimeout(() => {
        if (modal) modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }, 200);
}

function openRenewModal(patron) {
    document.getElementById('renewForm').action = `{{ route('admin.patrons.renew', ['id' => ':id']) }}`.replace(':id', patron.id);
    document.getElementById('renewPatronName').textContent = patron.name;
    document.getElementById('renew_expiry_date').value = patron.expiry;
    document.getElementById('renewModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeRenewModal() {
    const modal = document.getElementById('renewModal');
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
}
</script>
@endsection
