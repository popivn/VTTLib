@extends('layouts.admin')

@section('title', __('Sidebar Management'))

@push('styles')
<style>
    /* ============================================
       THEME VARIABLES MAPPED TO TAILWIND TOKENS
       ============================================ */
    :root, .dark {
        --clr-bg:             transparent;
        --clr-surface:        hsl(var(--card));
        --clr-glass:          hsl(var(--card));
        --clr-border:         hsl(var(--border));
        --clr-border-hl:      hsl(var(--primary));
        --clr-violet:         hsl(var(--primary));
        --clr-indigo:         hsl(var(--primary));
        --clr-pink:           hsl(var(--primary));
        --clr-text:           hsl(var(--foreground));
        --clr-text-sub:       hsl(var(--muted-foreground));
        --clr-text-dim:       hsl(var(--muted-foreground));
        --shadow-sm:          0 1px 2px 0 rgba(0,0,0,0.05);
        --shadow-lg:          0 10px 15px -3px rgba(0,0,0,0.1);
        --shadow-glow:        0 0 10px hsl(var(--primary) / 0.1);
        --radius-xl:          6px;
        --radius-lg:          4px;
        --radius-md:          4px;
        --transition:         0.2s ease;
    }

    /* ============================================
       HEADER
       ============================================ */
    .sbm-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.75rem;
        padding: 0.75rem 1rem;
        margin-bottom: 1rem;
        background: var(--clr-glass);
        border: 1px solid var(--clr-border);
        border-radius: var(--radius-xl);
    }

    .sbm-header-left h1 {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--clr-text);
        letter-spacing: -.02em;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .sbm-header-left p {
        color: var(--clr-text-sub);
        margin: 0.25rem 0 0 0;
        font-size: 0.75rem;
    }

    .sbm-actions { display: flex; gap: 0.5rem; flex-wrap: wrap; }

    /* ============================================
       BUTTONS & TOASTS
       ============================================ */
    .btn-sbm {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-weight: 600;
        font-size: 0.75rem;
        padding: 0.4rem 0.75rem;
        border-radius: var(--radius-md);
        border: 1px solid transparent;
        cursor: pointer;
        transition: all var(--transition);
        white-space: nowrap;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }

    /* ============================================
       STATS BAR
       ============================================ */
    .stats-bar {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    @media (max-width: 768px) {
        .stats-bar { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 480px) {
        .stats-bar { grid-template-columns: 1fr; }
    }

    .stat-card {
        padding: 0.75rem 1rem;
        border-radius: var(--radius-xl);
        border: 1px solid var(--clr-border);
        background: var(--clr-glass);
        display: flex;
        align-items: center;
        gap: 0.75rem;
        transition: all var(--transition);
    }

    .stat-card:hover {
        border-color: var(--clr-border-hl);
        box-shadow: var(--shadow-glow);
    }

    .stat-icon {
        width: 32px; height: 32px;
        border-radius: var(--radius-md);
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }

    .stat-icon.violet  { background: hsl(var(--primary) / 0.1); color: hsl(var(--primary)); }
    .stat-icon.emerald { background: rgba(16,185,129,0.1);  color: #10b981; }
    .stat-icon.amber   { background: rgba(245,158,11,0.1);  color: #f59e0b; }
    .stat-icon.cyan    { background: rgba(6,182,212,0.1);   color: #06b6d4; }

    .stat-info { min-width: 0; }

    .stat-value {
        font-size: 1.15rem;
        font-weight: 700;
        color: var(--clr-text);
        line-height: 1;
    }

    .stat-label {
        font-size: 0.65rem;
        font-weight: 600;
        color: var(--clr-text-sub);
        text-transform: uppercase;
        letter-spacing: .02em;
        margin-top: 0.15rem;
    }

    /* ============================================
       HINT BOX
       ============================================ */
    .hint-box {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        background: hsl(var(--primary) / 0.05);
        border: 1px solid hsl(var(--primary) / 0.15);
        border-radius: var(--radius-xl);
        padding: 0.75rem 1rem;
        margin-bottom: 1rem;
    }

    .hint-box-icon {
        width: 28px; height: 28px;
        border-radius: 50%;
        background: hsl(var(--primary) / 0.1);
        color: hsl(var(--primary));
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }

    .hint-box-content { flex: 1; }

    .hint-box-content h6 {
        font-size: 0.75rem;
        font-weight: 700;
        color: var(--clr-text);
        text-transform: uppercase;
        margin: 0 0 0.35rem 0;
    }

    .hint-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
    }

    .hint-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        background: hsl(var(--muted) / 0.3);
        border: 1px solid var(--clr-border);
        padding: 0.15rem 0.5rem;
        border-radius: 999px;
        font-size: 0.7rem;
        color: var(--clr-text-sub);
    }

    /* ============================================
       TREE PANEL
       ============================================ */
    .tree-panel {
        background: var(--clr-glass);
        border: 1px solid var(--clr-border);
        border-radius: var(--radius-xl);
        overflow: hidden;
        margin-bottom: 1rem;
    }

    .tree-panel-header {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid var(--clr-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: hsl(var(--muted) / 0.2);
    }

    .tree-panel-header h2 {
        font-size: 0.75rem;
        font-weight: 700;
        color: var(--clr-text-sub);
        text-transform: uppercase;
        letter-spacing: .02em;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .tree-panel-badge {
        background: hsl(var(--primary) / 0.1);
        color: hsl(var(--primary));
        border: 1px solid hsl(var(--primary) / 0.2);
        border-radius: 999px;
        font-size: 0.65rem;
        font-weight: 700;
        padding: 0.1rem 0.5rem;
    }

    .tree-body {
        padding: 0.75rem;
    }

    /* ============================================
       SORTABLE NESTED TREE
       ============================================ */
    .sortable-root {
        min-height: 40px;
        position: relative;
    }

    .sortable-ghost {
        opacity: 0;
    }

    .sortable-chosen {
        opacity: 1 !important;
    }

    .drop-indicator {
        height: 2px;
        border-radius: 2px;
        background: hsl(var(--primary));
        margin: 4px 0;
        pointer-events: none;
    }

    /* ============================================
       TREE ITEM CARD
       ============================================ */
    .tree-item {
        margin-bottom: 0.4rem;
    }

    .tree-card {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.4rem 0.6rem;
        border-radius: var(--radius-lg);
        border: 1px solid var(--clr-border);
        background: hsl(var(--card));
        transition: all var(--transition);
        position: relative;
    }

    .tree-card::before {
        content: '';
        position: absolute;
        left: 0; top: 0; bottom: 0;
        width: 2px;
        background: hsl(var(--primary));
        opacity: 0;
        transition: opacity var(--transition);
    }

    .tree-card:hover {
        background: hsl(var(--muted) / 0.3);
        border-color: var(--clr-border-hl);
    }

    .tree-card:hover::before { opacity: 1; }

    .tree-item.dragging-active .tree-card {
        background: hsl(var(--primary) / 0.1);
        border-color: hsl(var(--primary));
        box-shadow: var(--shadow-glow);
    }

    .tree-children {
        margin-left: 1.25rem;
        margin-top: 0.25rem;
        padding-left: 0.75rem;
        border-left: 1px solid var(--clr-border);
        min-height: 10px;
        position: relative;
    }

    /* Drag Handle */
    .drag-handle {
        cursor: grab;
        padding: 0.25rem;
        color: var(--clr-text-dim);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .drag-handle:active { cursor: grabbing; }

    /* Order Badge */
    .order-badge {
        font-size: 0.65rem;
        font-weight: 700;
        background: hsl(var(--muted));
        color: var(--clr-text-sub);
        padding: 0.1rem 0.35rem;
        border-radius: 99px;
        min-width: 1.25rem;
        text-align: center;
    }

    /* Icon Wrap */
    .item-icon-wrap {
        width: 24px; height: 24px;
        display: flex; align-items: center; justify-content: center;
        color: var(--clr-text-sub);
        font-size: 0.85rem;
    }

    /* Item Info */
    .item-info {
        flex: 1;
        min-width: 0;
    }

    .item-name {
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--clr-text);
        text-transform: uppercase;
        letter-spacing: .01em;
    }

    .item-route {
        font-size: 0.65rem;
        color: var(--clr-text-dim);
        font-family: monospace;
        margin-top: 1px;
    }

    /* Parent Selector */
    .parent-select-wrap {
        width: 130px;
        margin-right: 0.5rem;
    }

    .parent-select {
        width: 100%;
        height: 26px;
        font-size: 0.7rem;
        padding: 2px 20px 2px 6px;
        border: 1px solid var(--clr-border);
        background-color: hsl(var(--background));
        color: var(--clr-text);
        border-radius: var(--radius-md);
        outline: none;
        cursor: pointer;
        -webkit-appearance: none;
        appearance: none;
        background-image: url("data:image/svg+xml;charset=utf-8,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 4px center;
        background-repeat: no-repeat;
        background-size: 1rem 1rem;
    }

    .dark .parent-select {
        background-image: url("data:image/svg+xml;charset=utf-8,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%239ca3af' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
    }

    /* Actions and Chip */
    .item-actions {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .id-chip {
        font-size: 0.65rem;
        font-weight: 600;
        color: var(--clr-text-dim);
        font-family: monospace;
        background: hsl(var(--muted));
        padding: 0.1rem 0.35rem;
        border-radius: var(--radius-md);
    }

    /* Toggle Switch */
    .toggle-pill {
        position: relative;
        display: inline-flex;
        width: 32px; height: 18px;
        cursor: pointer;
    }

    .toggle-pill input { opacity: 0; width: 0; height: 0; }

    .toggle-track {
        position: absolute;
        inset: 0;
        background-color: hsl(var(--muted));
        border-radius: 99px;
        transition: background-color var(--transition);
        border: 1px solid var(--clr-border);
    }

    .toggle-thumb {
        position: absolute;
        top: 2px; left: 2px;
        width: 14px; height: 14px;
        background-color: hsl(var(--foreground));
        border-radius: 50%;
        transition: transform var(--transition);
        box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }

    .toggle-pill input:checked + .toggle-track {
        background-color: hsl(var(--primary));
        border-color: hsl(var(--primary));
    }

    .toggle-pill input:checked ~ .toggle-thumb {
        transform: translateX(14px);
        background-color: hsl(var(--primary-foreground));
    }

    /* ============================================
       DRAG OVERLAYS & HINTS
       ============================================ */
    .root-drop-hint {
        padding: 0.75rem;
        margin-top: 0.5rem;
        border: 2px dashed hsl(var(--primary) / 0.3);
        border-radius: var(--radius-lg);
        text-align: center;
        font-size: 0.75rem;
        color: hsl(var(--primary));
        font-weight: 600;
        background: hsl(var(--primary) / 0.02);
        opacity: 0;
        pointer-events: none;
        transition: opacity var(--transition);
    }

    .root-drop-hint.active { opacity: 1; }

    .drop-zone-active {
        border-color: hsl(var(--primary) / 0.5) !important;
        background: hsl(var(--primary) / 0.02) !important;
    }

    /* Save Overlay Spinner */
    .save-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.4);
        backdrop-filter: blur(4px);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s ease;
    }

    .save-overlay.active { opacity: 1; pointer-events: auto; }

    .save-spinner {
        background: hsl(var(--card));
        border: 1px solid var(--clr-border);
        padding: 1.25rem 2rem;
        border-radius: var(--radius-xl);
        text-align: center;
        box-shadow: var(--shadow-lg);
    }

    .spinner-ring {
        width: 28px; height: 28px;
        border: 3px solid hsl(var(--primary) / 0.1);
        border-top-color: hsl(var(--primary));
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
        margin: 0 auto 0.75rem;
    }

    .save-spinner p {
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--clr-text);
        margin: 0;
    }

    @keyframes spin { to { transform: rotate(360deg); } }

    /* ============================================
       TOAST STACK
       ============================================ */
    .toast-stack {
        position: fixed;
        bottom: 1.25rem;
        right: 1.25rem;
        z-index: 9999;
        display: flex;
        flex-col: column;
        gap: 0.5rem;
    }

    .toast-msg {
        background: hsl(var(--card));
        border: 1px solid var(--clr-border);
        color: var(--clr-text);
        padding: 0.5rem 1rem;
        border-radius: var(--radius-md);
        font-size: 0.75rem;
        font-weight: 600;
        box-shadow: var(--shadow-lg);
        display: flex;
        align-items: center;
        gap: 0.5rem;
        animation: toastEnter 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    @keyframes toastEnter {
        from { opacity: 0; transform: translateY(1rem) scale(0.9); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    .toast-msg.leaving {
        animation: toastLeave 0.3s ease forwards;
    }

    @keyframes toastLeave {
        to { opacity: 0; transform: translateY(-0.5rem) scale(0.95); }
    }

    .toast-msg.success i { color: #10b981; }
    .toast-msg.error i { color: #ef4444; }
    .toast-msg.info i { color: hsl(var(--primary)); }

    /* ============================================
       MODAL DIALOG
       ============================================ */
    .modal {
        position: fixed;
        inset: 0;
        z-index: 1000;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        opacity: 0;
        pointer-events: none;
        transition: opacity var(--transition);
    }

    .modal.active { opacity: 1; pointer-events: auto; }

    .modal-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(0,0,0,0.5);
        backdrop-filter: blur(2px);
    }

    .modal-content {
        position: relative;
        background: hsl(var(--card));
        border: 1px solid var(--clr-border);
        border-radius: var(--radius-xl);
        padding: 1rem;
        width: 100%;
        max-width: 450px;
        box-shadow: var(--shadow-lg);
    }

    .empty-state {
        text-align: center;
        padding: 2rem 0;
        color: var(--clr-text-dim);
    }

    .input-field {
        background: hsl(var(--background));
        border: 1px solid var(--clr-border);
        color: var(--clr-text);
        padding: 0.4rem 0.6rem;
        border-radius: var(--radius-md);
        font-size: 0.8rem;
        transition: all var(--transition);
        outline: none;
    }

    .input-field:focus {
        border-color: var(--clr-border-hl);
        box-shadow: 0 0 0 2px hsl(var(--primary) / 0.15);
    }

    select.input-field {
        background-image: url("data:image/svg+xml;charset=utf-8,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 0.5rem center;
        background-repeat: no-repeat;
        background-size: 1.25em 1.25em;
        padding-right: 2rem;
        -webkit-appearance: none;
        appearance: none;
    }
</style>
@endpush

@section('content')

{{-- Add New Item Modal --}}
<div id="addItemModal" class="modal" style="display: none;">
    <div class="modal-backdrop" onclick="closeAddModal()"></div>
    <div class="modal-content">
        <h3 class="text-sm font-bold border-b border-border pb-2 mb-3 uppercase text-foreground tracking-wider">{{ __('Thêm mục Menu mới') }}</h3>
        <form id="addItemForm" method="POST">
            @csrf
            <div class="space-y-3">
                <div class="flex flex-col">
                    <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider mb-1">{{ __('Tên mục') }} *</label>
                    <input type="text" name="name" required class="input-field" placeholder="{{ __('Nhập tên mục') }}">
                </div>
                <div class="flex flex-col">
                    <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider mb-1">{{ __('Biểu tượng (FontAwesome class)') }}</label>
                    <input type="text" name="icon" class="input-field" placeholder="fas fa-home" value="fas fa-circle">
                </div>
                <div class="flex flex-col">
                    <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider mb-1">{{ __('Tên Route') }}</label>
                    <input type="text" name="route_name" class="input-field" placeholder="admin.dashboard">
                </div>
                <div class="flex flex-col">
                    <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider mb-1">{{ __('Mục cha') }}</label>
                    <select name="parent_id" class="input-field">
                        <option value="">{{ __('— Root Level —') }}</option>
                        @foreach($sidebarItems as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center gap-2 mt-1">
                    <input type="checkbox" name="is_active" id="newItemActive" value="1" checked class="rounded-sm border-input bg-background text-primary focus:ring-primary h-4 w-4">
                    <label for="newItemActive" class="text-xs font-semibold text-foreground cursor-pointer">{{ __('Đang hoạt động') }}</label>
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-4 pt-2 border-t border-border">
                <button type="button" onclick="closeAddModal()" class="btn-compact-secondary px-3 py-1">{{ __('Hủy') }}</button>
                <button type="submit" class="btn-compact-primary px-3 py-1">{{ __('Thêm mục') }}</button>
            </div>
        </form>
    </div>
</div>

{{-- Save overlay spinner --}}
<div class="save-overlay" id="saveOverlay">
    <div class="save-spinner">
        <div class="spinner-ring"></div>
        <p>{{ __('Saving changes…') }}</p>
    </div>
</div>

{{-- Toast container --}}
<div class="toast-stack" id="toastStack"></div>

<div class="sbm-header">
    <div class="sbm-header-left">
        <h1><i data-lucide="layers" class="w-5 h-5 inline-block align-text-bottom"></i>{{ __('Sidebar Management') }}</h1>
        <p>{{ __('Drag and drop to reorder menu items and hierarchy') }}</p>
    </div>
    <div class="sbm-actions">
        <button class="btn-compact-secondary text-xs uppercase" onclick="openAddModal()">
            <i data-lucide="plus" class="w-3.5 h-3.5 mr-1"></i> {{ __('Add new item') }}
        </button>
        <button class="btn-compact-secondary text-xs uppercase" onclick="handleReset()">
            <i data-lucide="undo-2" class="w-3.5 h-3.5 mr-1"></i> {{ __('Reset') }}
        </button>
        <button class="btn-compact-primary text-xs uppercase" id="btnSave" onclick="handleSave()">
            <i data-lucide="cloud-upload" class="w-3.5 h-3.5 mr-1"></i> {{ __('Save changes') }}
        </button>
    </div>
</div>

{{-- STATS BAR --}}
<div class="stats-bar">
    <div class="stat-card">
        <div class="stat-icon violet"><i data-lucide="menu" class="w-4 h-4"></i></div>
        <div class="stat-info">
            <div class="stat-value">{{ $sidebarItems->count() }}</div>
            <div class="stat-label">{{ __('Total items') }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon emerald"><i data-lucide="check-circle-2" class="w-4 h-4"></i></div>
        <div class="stat-info">
            <div class="stat-value">{{ $sidebarItems->where('is_active', true)->count() }}</div>
            <div class="stat-label">{{ __('Active items') }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon amber"><i data-lucide="eye-off" class="w-4 h-4"></i></div>
        <div class="stat-info">
            <div class="stat-value">{{ $sidebarItems->where('is_active', false)->count() }}</div>
            <div class="stat-label">{{ __('Hidden items') }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon cyan"><i data-lucide="git-branch" class="w-4 h-4"></i></div>
        <div class="stat-info">
            <div class="stat-value">{{ $rootItems->count() }}</div>
            <div class="stat-label">{{ __('Root tabs') }}</div>
        </div>
    </div>
</div>

{{-- HINT BOX --}}
<div class="hint-box">
    <div class="hint-box-icon"><i data-lucide="wand-2" class="w-4 h-4"></i></div>
    <div class="hint-box-content">
        <h6>{{ __('How to use') }}</h6>
        <div class="hint-pills">
            <span class="hint-pill"><i data-lucide="grip-vertical" class="w-3.5 h-3.5 text-primary"></i> {{ __('Drag ☰ to reorder') }}</span>
            <span class="hint-pill"><i data-lucide="move" class="w-3.5 h-3.5 text-primary"></i> {{ __('Drag in/out to change hierarchy') }}</span>
            <span class="hint-pill"><i data-lucide="list" class="w-3.5 h-3.5 text-primary"></i> {{ __('Select dropdown to assign parent manually') }}</span>
            <span class="hint-pill"><i data-lucide="toggle-right" class="w-3.5 h-3.5 text-primary"></i> {{ __('Toggle to show/hide') }}</span>
            <span class="hint-pill"><i data-lucide="cloud-upload" class="w-3.5 h-3.5 text-primary"></i> {{ __('Click "Save changes" to apply') }}</span>
        </div>
    </div>
</div>

{{-- TREE PANEL --}}
<div class="tree-panel">
    <div class="tree-panel-header">
        <h2><i data-lucide="git-branch" class="w-4 h-4"></i> {{ __('Navigation menu tree') }}</h2>
        <span class="tree-panel-badge">Sortable + Nested</span>
    </div>
    <div class="tree-body">
        <div class="sortable-root" id="sortable-root">

            @forelse($rootItems->sortBy('order') as $item)
            <div class="tree-item" data-id="{{ $item->id }}" data-parent="">
                <div class="tree-card">
                    {{-- Drag handle --}}
                    <div class="drag-handle" title="{{ __('Drag to reorder') }}">
                        <i data-lucide="grip-vertical" class="w-4 h-4 text-muted-foreground"></i>
                    </div>

                    {{-- Order badge --}}
                    <span class="order-badge" title="Thứ tự">{{ $loop->index }}</span>



                    {{-- Name / Route --}}
                    <div class="item-info">
                        <div class="item-name">{{ $item->name }}</div>
                        @if($item->route_name)
                            <div class="item-route">{{ $item->route_name }}</div>
                        @endif
                    </div>

                    {{-- Parent selector --}}
                    <div class="parent-select-wrap">
                        <select class="parent-select"
                                title="{{ __('Assign parent tab') }}"
                                onchange="handleParentChange({{ $item->id }}, this.value)">
                            <option value="" selected>— Root —</option>
                            @foreach($rootItems as $p)
                                @if($p->id !== $item->id)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    {{-- Item actions --}}
                    <div class="item-actions">
                        <label class="toggle-pill" title="{{ $item->is_active ? __('Active') : __('Inactive') }}">
                            <input type="checkbox"
                                   id="toggle-{{ $item->id }}"
                                   {{ $item->is_active ? 'checked' : '' }}
                                   onchange="handleToggle({{ $item->id }})">
                            <span class="toggle-track"></span>
                            <span class="toggle-thumb"></span>
                        </label>
                        <span class="id-chip">#{{ $item->id }}</span>
                    </div>
                </div>

                {{-- Children --}}
                @if($item->children && $item->children->count() > 0)
                <div class="tree-children sortable-children" data-parent="{{ $item->id }}">
                    @foreach($item->children->sortBy('order') as $child)
                    <div class="tree-item" data-id="{{ $child->id }}" data-parent="{{ $child->parent_id }}">
                        <div class="tree-card">
                            <div class="drag-handle" title="{{ __('Drag to reorder') }}">
                                <i data-lucide="grip-vertical" class="w-4 h-4 text-muted-foreground"></i>
                            </div>
                            <span class="order-badge">{{ $loop->index }}</span>
                            <div class="item-info">
                                <div class="item-name">{{ $child->name }}</div>
                                @if($child->route_name)
                                    <div class="item-route">{{ $child->route_name }}</div>
                                @endif
                            </div>
                            <div class="parent-select-wrap">
                                <select class="parent-select"
                                        title="{{ __('Assign parent tab') }}"
                                        onchange="handleParentChange({{ $child->id }}, this.value)">
                                    <option value="">— Root —</option>
                                    @foreach($rootItems as $p)
                                        <option value="{{ $p->id }}"
                                                {{ $child->parent_id == $p->id ? 'selected' : '' }}>
                                            {{ $p->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="item-actions">
                                <label class="toggle-pill" title="{{ $child->is_active ? __('Active') : __('Inactive') }}">
                                    <input type="checkbox"
                                           id="toggle-{{ $child->id }}"
                                           {{ $child->is_active ? 'checked' : '' }}
                                           onchange="handleToggle({{ $child->id }})">
                                    <span class="toggle-track"></span>
                                    <span class="toggle-thumb"></span>
                                </label>
                                <span class="id-chip">#{{ $child->id }}</span>
                            </div>
                        </div>

                        {{-- Level 3: grandchildren --}}
                        @if($child->children && $child->children->count() > 0)
                        <div class="tree-children sortable-children" data-parent="{{ $child->id }}">
                            @foreach($child->children->sortBy('order') as $grandchild)
                            <div class="tree-item" data-id="{{ $grandchild->id }}" data-parent="{{ $grandchild->parent_id }}">
                                <div class="tree-card">
                                    <div class="drag-handle" title="{{ __('Drag to reorder') }}"><i data-lucide="grip-vertical" class="w-4 h-4 text-muted-foreground"></i></div>
                                    <span class="order-badge">{{ $loop->index }}</span>
                                    <div class="item-icon-wrap">{!! $grandchild->icon !!}</div>
                                    <div class="item-info">
                                        <div class="item-name">{{ $grandchild->name }}</div>
                                        @if($grandchild->route_name)
                                            <div class="item-route">{{ $grandchild->route_name }}</div>
                                        @endif
                                    </div>
                                    <div class="parent-select-wrap">
                                        <select class="parent-select"
                                                title="{{ __('Assign parent tab') }}"
                                                onchange="handleParentChange({{ $grandchild->id }}, this.value)">
                                            <option value="">— Root —</option>
                                            @foreach($rootItems as $p)
                                                <option value="{{ $p->id }}"
                                                        {{ $grandchild->parent_id == $p->id ? 'selected' : '' }}>
                                                    {{ $p->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="item-actions">
                                        <label class="toggle-pill" title="{{ $grandchild->is_active ? __('Active') : __('Inactive') }}">
                                            <input type="checkbox"
                                                   id="toggle-{{ $grandchild->id }}"
                                                   {{ $grandchild->is_active ? 'checked' : '' }}
                                                   onchange="handleToggle({{ $grandchild->id }})">
                                            <span class="toggle-track"></span>
                                            <span class="toggle-thumb"></span>
                                        </label>
                                        <span class="id-chip">#{{ $grandchild->id }}</span>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="tree-children sortable-children" data-parent="{{ $child->id }}"></div>
                        @endif

                    </div>
                    @endforeach
                </div>
                @else
                <div class="tree-children sortable-children" data-parent="{{ $item->id }}"></div>
                @endif

            </div>
            @empty
            <div class="empty-state">
                <i data-lucide="inbox" class="w-8 h-8 text-muted-foreground mb-2"></i>
                <p class="text-xs text-muted-foreground">{{ __('No menu items yet') }}</p>
            </div>
            @endforelse

            <div class="root-drop-hint" id="rootDropHint">
                <i data-lucide="corner-right-down" class="w-4 h-4 inline-block mr-1"></i> {{ __('Kéo vào đây để đặt ở Root Level') }}
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js"></script>

<script>
const ROUTES = {
    order:  '{{ route("admin.sidebar.order") }}',
    parent: '{{ route("admin.sidebar.parent") }}',
    toggle: '{{ route("admin.sidebar.toggle-active") }}',
};
const CSRF = '{{ csrf_token() }}';

let pendingChanges = false;
let allSortables   = [];

function initAllSortables() {
    allSortables.forEach(s => s.destroy());
    allSortables = [];

    const rootEl = document.getElementById('sortable-root');
    createSortable(rootEl, null);

    document.querySelectorAll('.sortable-children').forEach(el => {
        const parentId = el.dataset.parent || null;
        createSortable(el, parentId);
    });
}

function createSortable(el, parentId) {
    const s = Sortable.create(el, {
        group: 'sidebar-tree',
        animation: 200,
        handle: '.drag-handle',
        ghostClass: 'sortable-ghost',
        chosenClass: 'dragging-active',
        dragClass: 'sortable-drag',
        fallbackTolerance: 5,
        easing: 'cubic-bezier(0.25, 1, 0.5, 1)',

        onStart(evt) {
            document.querySelectorAll('.sortable-children, #sortable-root')
                    .forEach(z => z.classList.add('drop-zone-active'));
            document.getElementById('rootDropHint').classList.add('active');
        },

        onEnd(evt) {
            document.querySelectorAll('.sortable-children, #sortable-root')
                    .forEach(z => z.classList.remove('drop-zone-active'));
            document.getElementById('rootDropHint').classList.remove('active');

            const itemEl   = evt.item;
            const targetList = evt.to;
            const newParentId = targetList.dataset.parent || null;
            const itemId   = parseInt(itemEl.dataset.id);
            const oldParentId = itemEl.dataset.parent || null;

            if (String(newParentId) !== String(oldParentId)) {
                itemEl.dataset.parent = newParentId || '';

                const sel = itemEl.querySelector('.parent-select');
                if (sel) {
                    sel.value = newParentId || '';
                }

                pushParentChange(itemId, newParentId);
            }

            updateBadgeNumbers();
            markPendingChange();
        },
    });

    allSortables.push(s);
}

function updateBadgeNumbers() {
    document.querySelectorAll('#sortable-root > .tree-item').forEach((item, i) => {
        const badge = item.querySelector(':scope > .tree-card .order-badge');
        if (badge) badge.textContent = i;

        item.querySelectorAll(':scope > .sortable-children > .tree-item').forEach((child, j) => {
            const cb = child.querySelector(':scope > .tree-card .order-badge');
            if (cb) cb.textContent = j;
        });
    });
}

function handleParentChange(itemId, parentId) {
    pushParentChange(itemId, parentId || null, true);
}

function pushParentChange(itemId, parentId, reloadAfter = false) {
    showToast('{{ __("Updating structure...") }}', 'info');

    fetch(ROUTES.parent, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ id: itemId, parent_id: parentId || null }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('{{ __("Hierarchy updated!") }}', 'success');
            if (reloadAfter) {
                setTimeout(() => location.reload(), 900);
            } else {
                setTimeout(initAllSortables, 300);
            }
        } else {
            showToast(data.message || 'Lỗi cập nhật parent', 'error');
        }
    })
    .catch(() => showToast('Lỗi kết nối server', 'error'));
}

function handleSave() {
    const orders = collectOrders();

    document.getElementById('saveOverlay').classList.add('active');

    fetch(ROUTES.order, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ orders }),
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('saveOverlay').classList.remove('active');
        if (data.success) {
            showToast('{{ __("Order saved successfully!") }}', 'success');
            pendingChanges = false;
            
            const btn = document.getElementById('btnSave');
            btn.innerHTML = '<i data-lucide="check" class="w-4 h-4 mr-1"></i> ' + '{{ __("Saved") }}';
            lucide.createIcons({ parent: btn });
            
            setTimeout(() => {
                btn.innerHTML = '<i data-lucide="cloud-upload" class="w-4 h-4 mr-1"></i> ' + '{{ __("Save changes") }}';
                lucide.createIcons({ parent: btn });
            }, 2500);
        } else {
            showToast(data.message || 'Lỗi lưu thứ tự', 'error');
        }
    })
    .catch(() => {
        document.getElementById('saveOverlay').classList.remove('active');
        showToast('Lỗi kết nối server', 'error');
    });
}

function collectOrders() {
    const orders = [];
    let idx = 0;

    document.querySelectorAll('#sortable-root > .tree-item').forEach(rootItem => {
        orders.push({ id: parseInt(rootItem.dataset.id), order: idx++ });

        rootItem.querySelectorAll(':scope > .sortable-children > .tree-item').forEach(child => {
            orders.push({ id: parseInt(child.dataset.id), order: idx++ });

            child.querySelectorAll(':scope > .sortable-children > .tree-item').forEach(gc => {
                orders.push({ id: parseInt(gc.dataset.id), order: idx++ });
            });
        });
    });

    return orders;
}

function handleToggle(itemId) {
    fetch(ROUTES.toggle, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ id: itemId }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const msg = data.is_active ? 'Đã bật hiển thị' : 'Đã ẩn mục';
            showToast(msg, 'success');
        } else {
            showToast(data.message || 'Lỗi cập nhật', 'error');
        }
    })
    .catch(() => showToast('Lỗi kết nối', 'error'));
}

function handleReset() {
    if (!pendingChanges && !confirm('{{ __("Reload page to discard all changes?") }}')) return;
    location.reload();
}

function markPendingChange() {
    pendingChanges = true;
    const btn = document.getElementById('btnSave');
    if (!btn.classList.contains('pending')) {
        btn.classList.add('pending');
        btn.innerHTML = '<i data-lucide="alert-circle" class="w-4 h-4 mr-1"></i> ' + '{{ __("Save changes") }}';
        lucide.createIcons({ parent: btn });
    }
}

function showToast(message, type = 'info') {
    const stack = document.getElementById('toastStack');
    const icons = { success: 'check-circle-2', error: 'alert-triangle', info: 'info' };

    const el = document.createElement('div');
    el.className = `toast-msg ${type}`;
    el.innerHTML = `<i data-lucide="${icons[type] || 'info'}" class="w-4 h-4 mr-1"></i> ${message}`;
    stack.appendChild(el);
    lucide.createIcons({ parent: el });

    setTimeout(() => {
        el.classList.add('leaving');
        setTimeout(() => el.remove(), 350);
    }, 4000);
}

document.addEventListener('DOMContentLoaded', () => {
    initAllSortables();
    updateBadgeNumbers();
});

function openAddModal() {
    const modal = document.getElementById('addItemModal');
    modal.style.display = 'flex';
    setTimeout(() => modal.classList.add('active'), 10);
}

function closeAddModal() {
    const modal = document.getElementById('addItemModal');
    modal.classList.remove('active');
    setTimeout(() => { modal.style.display = 'none'; }, 200);
    document.getElementById('addItemForm').reset();
}

document.getElementById('addItemForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    
    showToast('{{ __("Adding new item...") }}', 'info');
    
    fetch('{{ route("admin.sidebar.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('{{ __("New item added successfully!") }}', 'success');
            closeAddModal();
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.message || 'Lỗi thêm mục mới', 'error');
        }
    })
    .catch(() => showToast('Lỗi kết nối server', 'error'));
});
</script>
@endsection
