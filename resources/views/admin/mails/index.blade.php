@extends('layouts.admin')

@section('content')
<div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100 dark:border-slate-800">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-[#680102] to-[#450001] flex items-center justify-center text-white shadow-lg shadow-red-950/20">
                <i data-lucide="mail" class="w-5 h-5"></i>
            </div>
            <div>
                <h1 class="text-xl font-extrabold text-slate-800 dark:text-slate-100 tracking-tight">{{ __('Quản Lý Mail') }}</h1>
                <p class="text-xs font-medium text-slate-400 dark:text-slate-500">{{ __('Hệ thống quản lý, theo dõi gửi email và xem trước nội dung thư.') }}</p>
            </div>
        </div>
        
        <!-- Back Button -->
        <a href="{{ url('/topsecret') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-semibold transition-all duration-200 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            {{ __('Quay về') }}
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mb-1">Tổng Email</p>
                    <p class="text-2xl font-bold text-slate-800 dark:text-slate-100">{{ $stats['total'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-950/30 flex items-center justify-center text-blue-600 dark:text-blue-400">
                    <i data-lucide="mail" class="w-5 h-5"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mb-1">Đã Gửi</p>
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $stats['sent'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-green-100 dark:bg-green-950/30 flex items-center justify-center text-green-600 dark:text-green-400">
                    <i data-lucide="check-circle" class="w-5 h-5"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mb-1">Thất Bại</p>
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $stats['failed'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-red-100 dark:bg-red-950/30 flex items-center justify-center text-red-600 dark:text-red-400">
                    <i data-lucide="alert-circle" class="w-5 h-5"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mb-1">Đang Chờ</p>
                    <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $stats['pending'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-amber-100 dark:bg-amber-950/30 flex items-center justify-center text-amber-600 dark:text-amber-400">
                    <i data-lucide="clock" class="w-5 h-5"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-lg">
        <div class="flex gap-0 border-b border-slate-100 dark:border-slate-800">
            <a href="{{ route('admin.mails.index', ['tab' => 'list']) }}" class="px-6 py-3 text-sm font-semibold transition-colors border-b-2 {{ $tab === 'list' ? 'text-[#680102] dark:text-[#ef4444] border-[#680102] dark:border-[#ef4444]' : 'text-slate-500 dark:text-slate-400 border-transparent hover:text-slate-700 dark:hover:text-slate-300' }}">
                <i data-lucide="list" class="w-4 h-4 inline mr-2"></i>
                Danh Sách Email
            </a>
            <a href="{{ route('admin.mails.index', ['tab' => 'create']) }}" class="px-6 py-3 text-sm font-semibold transition-colors border-b-2 {{ $tab === 'create' ? 'text-[#680102] dark:text-[#ef4444] border-[#680102] dark:border-[#ef4444]' : 'text-slate-500 dark:text-slate-400 border-transparent hover:text-slate-700 dark:hover:text-slate-300' }}">
                <i data-lucide="layout-template" class="w-4 h-4 inline mr-2"></i>
                Template Mail
            </a>
            <a href="{{ route('admin.mails.index', ['tab' => 'send']) }}" class="px-6 py-3 text-sm font-semibold transition-colors border-b-2 {{ $tab === 'send' ? 'text-[#680102] dark:text-[#ef4444] border-[#680102] dark:border-[#ef4444]' : 'text-slate-500 dark:text-slate-400 border-transparent hover:text-slate-700 dark:hover:text-slate-300' }}">
                <i data-lucide="send" class="w-4 h-4 inline mr-2"></i>
                Gửi Mail
            </a>
        </div>

        <!-- Tab Content -->
        <div class="p-6">
            @if($tab === 'list')
                @include('admin.mails.tabs.list')
            @elseif($tab === 'create')
                @include('admin.mails.tabs.create')
            @elseif($tab === 'send')
                @include('admin.mails.tabs.send')
            @endif
        </div>
    </div>
</div>

<style>
    [data-lucide] {
        display: inline;
    }
</style>
@endsection
