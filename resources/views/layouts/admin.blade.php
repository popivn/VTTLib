<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ __('Admin Dashboard') }} - {{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Scripts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Configure Tailwind with custom config
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Instrument Sans', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        border: 'hsl(var(--border))',
                        input: 'hsl(var(--input))',
                        ring: 'hsl(var(--ring))',
                        background: 'hsl(var(--background))',
                        foreground: 'hsl(var(--foreground))',
                        primary: {
                            DEFAULT: 'hsl(var(--primary))',
                            foreground: 'hsl(var(--primary-foreground))',
                        },
                        secondary: {
                            DEFAULT: 'hsl(var(--secondary))',
                            foreground: 'hsl(var(--secondary-foreground))',
                        },
                        destructive: {
                            DEFAULT: 'hsl(var(--destructive))',
                            foreground: 'hsl(var(--destructive-foreground))',
                        },
                        muted: {
                            DEFAULT: 'hsl(var(--muted))',
                            foreground: 'hsl(var(--muted-foreground))',
                        },
                        accent: {
                            DEFAULT: 'hsl(var(--accent))',
                            foreground: 'hsl(var(--accent-foreground))',
                        },
                        card: {
                            DEFAULT: 'hsl(var(--card))',
                            foreground: 'hsl(var(--card-foreground))',
                        },
                    },
                },
            },
            plugins: [],
        }
    </script>
    <style>
        :root {
            --background: 0 0% 100%;
            --foreground: 222.2 84% 4.9%;
            --card: 0 0% 100%;
            --card-foreground: 222.2 84% 4.9%;
            --primary: 221.2 83.2% 53.3%;
            --primary-foreground: 210 40% 98%;
            --secondary: 210 40% 96.1%;
            --secondary-foreground: 222.2 47.4% 11.2%;
            --muted: 210 40% 96.1%;
            --muted-foreground: 215.4 16.3% 46.9%;
            --accent: 210 40% 96.1%;
            --accent-foreground: 222.2 47.4% 11.2%;
            --destructive: 0 84.2% 60.2%;
            --destructive-foreground: 210 40% 98%;
            --border: 214.3 31.8% 91.4%;
            --input: 214.3 31.8% 91.4%;
            --ring: 221.2 83.2% 53.3%;
        }

        .dark {
            --background: 222.2 84% 4.9%;
            --foreground: 210 40% 98%;
            --card: 222.2 84% 6.9%;
            --card-foreground: 210 40% 98%;
            --primary: 217.2 91.2% 59.8%;
            --primary-foreground: 222.2 47.4% 11.2%;
            --secondary: 217.2 32.6% 17.5%;
            --secondary-foreground: 210 40% 98%;
            --muted: 217.2 32.6% 17.5%;
            --muted-foreground: 215 20.2% 65.1%;
            --accent: 217.2 32.6% 17.5%;
            --accent-foreground: 210 40% 98%;
            --destructive: 0 62.8% 30.6%;
            --destructive-foreground: 210 40% 98%;
            --border: 217.2 32.6% 25%;
            --input: 217.2 32.6% 17.5%;
            --ring: 224.3 76.3% 48%;
        }
    <style>
        @import url('https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700');
    </style>
    <style type="text/tailwindcss">
        [x-cloak] {
            display: none !important;
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 3px;
            height: 3px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #6366f1;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #4f46e5;
        }

        /* Global scrollbar styling */
        ::-webkit-scrollbar {
            width: 3px;
            height: 3px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #6366f1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #4f46e5;
        }

        /* Firefox scrollbar */
        * {
            scrollbar-width: thin;
            scrollbar-color: #6366f1 transparent;
        }

        /* Force remove overflow-hidden from parent container */
        .flex-1.flex.flex-col.min-w-0 {
            overflow: visible !important;
        }
        
        /* Force main element to have limited height for scrolling */
        main {
            height: calc(100vh - 4rem) !important; /* Subtract header height */
            max-height: calc(100vh - 4rem) !important;
        }

        /* Table/Data List: Hover effect */
        .table-row-hover {
            @apply transition-colors duration-150 hover:bg-muted/50 active:bg-muted;
        }

        /* Button: Professional Hover/Active Effects */
        .btn-compact {
            @apply inline-flex items-center justify-center gap-2 px-3 py-1.5 rounded text-xs font-medium transition-all duration-200 active:scale-95 disabled:opacity-50 disabled:pointer-events-none;
        }

        .btn-compact-primary {
            @apply btn-compact bg-primary text-primary-foreground hover:bg-primary/90 shadow-sm;
        }

        .btn-compact-secondary {
            @apply btn-compact bg-secondary text-secondary-foreground hover:bg-secondary/80 border border-border;
        }

        .btn-compact-muted {
            @apply btn-compact bg-muted text-muted-foreground hover:bg-muted/80 border border-border;
        }

        .btn-icon-compact {
            @apply w-7 h-7 flex items-center justify-center rounded bg-background hover:bg-muted text-muted-foreground border border-border transition-all active:scale-90;
        }

        .btn-icon-danger {
            @apply btn-icon-compact hover:bg-destructive hover:text-destructive-foreground;
        }

        .input-field {
            @apply w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:focus:ring-indigo-400 dark:focus:border-indigo-400 transition-colors duration-200;
        }

        /* Overrides to make light mode utility colors more readable */
        .text-blue-400 { color: #3b82f6 !important; }
        .dark .text-blue-400 { color: #60a5fa !important; }

        .text-green-400 { color: #10b981 !important; }
        .dark .text-green-400 { color: #34d399 !important; }

        .text-gray-400 { color: #64748b !important; }
        .dark .text-gray-400 { color: #94a3b8 !important; }

        .text-red-400 { color: #ef4444 !important; }
        .dark .text-red-400 { color: #f87171 !important; }

        .text-yellow-400 { color: #f59e0b !important; }
        .dark .text-yellow-400 { color: #fbbf24 !important; }

        .text-purple-400 { color: #8b5cf6 !important; }
        .dark .text-purple-400 { color: #a78bfa !important; }

        .bg-blue-400 { background-color: #3b82f6 !important; }
        .dark .bg-blue-400 { background-color: #60a5fa !important; }

        .bg-green-400 { background-color: #10b981 !important; }
        .dark .bg-green-400 { background-color: #34d399 !important; }

        .bg-gray-400 { background-color: #64748b !important; }
        .dark .bg-gray-400 { background-color: #94a3b8 !important; }

        .bg-red-400 { background-color: #ef4444 !important; }
        .dark .bg-red-400 { background-color: #f87171 !important; }

        .bg-yellow-400 { background-color: #f59e0b !important; }
        .dark .bg-yellow-400 { background-color: #fbbf24 !important; }

        .bg-purple-400 { background-color: #8b5cf6 !important; }
        .dark .bg-purple-400 { background-color: #a78bfa !important; }

        .bg-blue-900\/30 { background-color: #1e3a8a !important; }
        .dark .bg-blue-900\/30 { background-color: rgba(30, 58, 138, 0.3) !important; }

        .bg-green-900\/30 { background-color: #064e3b !important; }
        .dark .bg-green-900\/30 { background-color: rgba(6, 78, 59, 0.3) !important; }

        .bg-gray-900\/30 { background-color: #0f172a !important; }
        .dark .bg-gray-900\/30 { background-color: rgba(15, 23, 42, 0.3) !important; }

        .bg-red-900\/30 { background-color: #7f1d1d !important; }
        .dark .bg-red-900\/30 { background-color: rgba(127, 29, 29, 0.3) !important; }

        .bg-yellow-900\/30 { background-color: #713f12 !important; }
        .dark .bg-yellow-900\/30 { background-color: rgba(113, 63, 18, 0.3) !important; }

        .bg-purple-900\/30 { background-color: #581c87 !important; }
        .dark .bg-purple-900\/30 { background-color: rgba(88, 28, 135, 0.3) !important; }

        .border-gray-700 { border-color: #334155 !important; }
        .dark .border-gray-700 { border-color: #334155 !important; }

        .border-gray-800 { border-color: #1e293b !important; }
        .dark .border-gray-800 { border-color: #1e293b !important; }

        .hover\:bg-gray-800\/50:hover { background-color: rgba(30, 41, 59, 0.5) !important; }
        .dark .hover\:bg-gray-800\/50:hover { background-color: rgba(30, 41, 59, 0.5) !important; }

        .bg-gray-900\/50 { background-color: rgba(15, 23, 42, 0.5) !important; }
        .dark .bg-gray-900\/50 { background-color: rgba(15, 23, 42, 0.5) !important; }

        /* Dark mode select dropdown styling - Stronger rules */
        select, .input-field {
            background-color: white !important;
            color: #1e293b !important;
            border: 1px solid #cbd5e1 !important;
        }
        
        .dark select, .dark .input-field {
            background-color: #0f172a !important;
            color: #f1f5f9 !important;
            border: 1px solid #475569 !important;
        }
        
        /* Custom dropdown arrow */
        select {
            appearance: none !important;
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e") !important;
            background-position: right 0.75rem center !important;
            background-repeat: no-repeat !important;
            background-size: 1.25em 1.25em !important;
            padding-right: 2.5rem !important;
            color: inherit !important;
            text-indent: 0 !important;
        }
        
        .dark select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%9ca3af' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e") !important;
        }
        
        /* Options styling */
        select option {
            background-color: white !important;
            color: #1e293b !important;
            padding: 0.5rem !important;
        }
        
        .dark select option {
            background-color: #0f172a !important;
            color: #f1f5f9 !important;
        }
        
        /* Add proper padding and border-radius */
        .input-field {
            width: 100% !important;
        }

        select, .input-field {
            padding: 0.5rem 0.75rem !important;
            border-radius: 0.5rem !important;
            font-size: 0.875rem !important;
            line-height: 1.25rem !important;
            transition: all 0.2s ease !important;
        }

        select {
            width: 100%;
        }
        
        select {
            padding-right: 2.5rem !important;
        }
        
        /* Focus states */
        select:focus, .input-field:focus {
            outline: none !important;
            box-shadow: 0 0 0 2px #3b82f6 !important;
            border-color: #3b82f6 !important;
        }
        
        .dark select:focus, .dark .input-field:focus {
            box-shadow: 0 0 0 2px #60a5fa !important;
            border-color: #60a5fa !important;
        }
        
        /* Hover states */
        select:hover, .input-field:hover {
            border-color: #94a3b8 !important;
        }
        
        .dark select:hover, .dark .input-field:hover {
            border-color: #64748b !important;
        }
    </style>

    <script>
        // Set dark mode immediately to avoid flash
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    @stack('styles')
</head>

<body
    class="font-sans antialiased bg-gray-100 dark:bg-slate-900 text-gray-900 dark:text-slate-100 flex min-h-screen transition-colors duration-300"
    x-data="{ 
        sidebarOpen: false,
        darkMode: localStorage.getItem('theme') === 'dark',
        toggleDarkMode() {
            this.darkMode = !this.darkMode;
            if (this.darkMode) {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            } else {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            }
        }
    }">

    <!-- Loading Overlay -->
    <x-admin-loading-overlay />

    <!-- Sidebar -->
    <aside :class="sidebarOpen ? 'w-72' : 'w-14 px-1'"
        class="bg-white dark:bg-slate-950 border-r border-slate-100 dark:border-slate-800 text-slate-800 dark:text-white flex flex-col flex-shrink-0 transition-all duration-300 sticky top-0 h-screen z-50">
        <div :class="sidebarOpen ? 'px-6' : 'px-2 justify-center'"
             class="h-16 flex items-center bg-slate-50/50 dark:bg-slate-950 border-b border-slate-100 dark:border-slate-800 overflow-hidden whitespace-nowrap">
            <span class="text-xl font-black tracking-tighter flex items-center">
                <span class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white text-xs shadow-sm" :class="sidebarOpen ? 'mr-3' : 'mr-0'">V</span>
                <span x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    VTTLib <span class="text-[10px] bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 px-1.5 py-0.5 rounded ml-1 tracking-widest uppercase">Admin</span>
                </span>
            </span>
        </div>

        <nav :class="sidebarOpen ? 'px-4' : 'px-1'"
             class="flex-1 py-8 space-y-2.5 overflow-y-auto custom-scrollbar overflow-x-hidden">
            @php
            $roleIds = Auth::user()->roles->pluck('id')->toArray();
            @endphp
            @foreach(Auth::user()->getSidebarTabs() as $tab)
            @php
            // Using direct query to avoid unknown method lint if model is not inferred
            $assignedChildren = \App\Models\Sidebar::where('parent_id', $tab->id)
            ->where('is_active', true)
            ->whereHas('roles', function ($q) use ($roleIds) {
                $q->whereIn('role_id', $roleIds);
            })->orderBy('order')->get();

            $hasChildren = $assignedChildren->isNotEmpty();
            $isParentActive = false;
            if ($hasChildren) {
            foreach ($assignedChildren as $child) {
            if ($child->route_name != '#' && request()->routeIs($child->route_name . '*')) {
            $isParentActive = true;
            break;
            }
            }
            } else {
            $isParentActive = ($tab->route_name != '#' && request()->routeIs($tab->route_name . '*'));
            }
            @endphp

            @if($hasChildren)
            @php
            $firstChild = $assignedChildren->first();
            $firstChildUrl = ($firstChild && !blank($firstChild->route_name) && $firstChild->route_name !== '#' && Route::has($firstChild->route_name)) ? route($firstChild->route_name) : '#';
            @endphp
            <div class="space-y-1.5 border-t border-slate-100 dark:border-slate-800/50 pt-2.5" x-data="{ open: {{ $isParentActive ? 'true' : 'false' }} }">
                <button @click="
                    if (!sidebarOpen) {
                        if ('{{ $firstChildUrl }}' !== '#') {
                            window.location.href = '{{ $firstChildUrl }}';
                        }
                        return;
                    }
                    open = !open;
                    if (open && '{{ $firstChildUrl }}' !== '#') {
                        window.location.href = '{{ $firstChildUrl }}';
                    }
                "
                    :class="sidebarOpen ? 'justify-between px-4' : 'justify-center px-0'"
                    class="w-full flex items-center py-3.5 {{ $isParentActive ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-500 dark:text-slate-400' }} hover:bg-slate-50 dark:hover:bg-slate-800/50 hover:text-indigo-600 dark:hover:text-white rounded-2xl transition group">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 w-5 h-5 flex items-center justify-center {{ $isParentActive ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400' }} group-hover:text-indigo-500 transition-colors">{!! $tab->icon !!}</div>
                        <span x-show="sidebarOpen" x-cloak
                            class="ml-3 font-bold text-[11px] uppercase tracking-widest whitespace-nowrap">{{ \Illuminate\Support\Str::upper($tab->display_name) }}</span>
                    </div>
                    <svg x-show="sidebarOpen" x-cloak class="w-3.5 h-3.5 transition-transform duration-300 opacity-60"
                        :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                <div x-show="open && sidebarOpen" x-cloak x-collapse class="px-2 pb-2 space-y-1 bg-slate-50/50 dark:bg-slate-950/30 rounded-2xl mt-1">
                    @foreach($assignedChildren as $child)
                        <a href="{{ $child->route_name && $child->route_name !== '#' && Route::has($child->route_name) ? route($child->route_name) : '#' }}"
                            class="group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200 {{ request()->routeIs($child->route_name . '*') ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/20 dark:text-indigo-400' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200' }}">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="{{ $child->icon ?? 'fas fa-circle' }} w-5 h-5 flex items-center justify-center text-[8px] opacity-40 group-hover:opacity-100 transition-all"></i>
                                </div>
                                <span class="ml-3 truncate font-bold text-[10px] uppercase tracking-widest">{{ \Illuminate\Support\Str::upper($child->display_name) }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
            @else
            <a href="{{ (!blank($tab->route_name) && $tab->route_name !== '#' && Route::has($tab->route_name)) ? route($tab->route_name) : '#' }}"
                :class="sidebarOpen ? 'px-4' : 'justify-center px-0'"
                class="flex items-center py-3.5 border-t border-slate-100 dark:border-slate-800/50 {{ $isParentActive ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100 dark:shadow-none' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50 hover:text-indigo-600 dark:hover:text-white' }} rounded-2xl group transition">
                <div class="flex-shrink-0 w-5 h-5 flex items-center justify-center text-slate-400 group-hover:text-indigo-500 transition-colors" :class="sidebarOpen ? '' : 'w-full'">
                    {!! $tab->icon !!}
                </div>
                <span x-show="sidebarOpen" x-cloak
                    class="ml-3 font-bold text-[11px] uppercase tracking-widest whitespace-nowrap">{{ \Illuminate\Support\Str::upper($tab->display_name) }}</span>
            </a>
            @endif
            @endforeach
        </nav>

        <div :class="sidebarOpen ? 'p-4' : 'p-1'" class="border-t border-slate-800 dark:border-slate-800/50 overflow-hidden">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" :class="sidebarOpen ? 'px-4' : 'justify-center px-0'"
                    class="w-full flex items-center py-2 text-sm text-slate-500 dark:text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 group transition">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                        </path>
                    </svg>
                    <span x-show="sidebarOpen" x-cloak class="ml-3 whitespace-nowrap">{{ __('Logout') }}</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col min-w-0">
        <!-- Topbar -->
        <header
            class="h-16 bg-white dark:bg-slate-900 border-b border-gray-100 dark:border-slate-800 shadow-sm flex items-center justify-between px-6 z-10 transition-colors duration-300">
            <div class="flex items-center">
                <button @click="sidebarOpen = !sidebarOpen"
                    class="p-2 rounded-lg text-gray-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-slate-800 focus:outline-none mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path x-show="sidebarOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h7"></path>
                        <path x-show="!sidebarOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-slate-100 leading-tight">{{ __('Dashboard') }}
                </h2>
            </div>
            <div class="flex items-center space-x-2">
                <!-- Theme Toggle -->
                <button @click="toggleDarkMode()"
                    class="p-2 rounded-xl text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all duration-300 group">
                    <svg x-show="!darkMode" class="w-5 h-5 group-hover:rotate-12 transition-transform" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                    <svg x-show="darkMode" x-cloak
                        class="w-5 h-5 group-hover:rotate-90 transition-transform text-amber-400" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 3v1m0 16v1m9-9h-1M4 9H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </button>

                <div class="h-6 w-[1px] bg-slate-200 dark:bg-slate-800 mx-2"></div>

                <div class="flex items-center space-x-2 mr-2">
                    <a href="{{ route('lang.switch', 'vi') }}"
                        class="text-xs font-bold {{ app()->getLocale() == 'vi' ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-400 dark:text-slate-500' }} hover:text-indigo-500 transition">VI</a>
                    <span class="text-gray-300 dark:text-slate-700">|</span>
                    <a href="{{ route('lang.switch', 'en') }}"
                        class="text-xs font-bold {{ app()->getLocale() == 'en' ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-400 dark:text-slate-500' }} hover:text-indigo-500 transition">EN</a>
                </div>

                <div class="h-6 w-[1px] bg-slate-200 dark:bg-slate-800 mx-2"></div>

                <!-- Notification Bell Dropdown -->
                <div x-data="notificationManager" class="relative" @click.away="isOpen = false">
                    <button @click="toggle()" class="relative p-2 rounded-xl text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all duration-300 focus:outline-none">
                        <span x-show="unreadCount > 0"
                            class="absolute top-1.5 right-1.5 block h-2.5 w-2.5 rounded-full ring-2 ring-white dark:ring-slate-900 bg-red-500 animate-pulse"></span>
                        <svg class="h-6 w-6 text-slate-500 dark:text-slate-400 hover:text-indigo-500 transition-colors"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </button>

                    <!-- Dropdown List -->
                    <div x-show="isOpen" x-cloak
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                        x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                        class="absolute right-0 mt-2 w-96 bg-white dark:bg-slate-900 rounded-2xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.15)] border border-slate-100 dark:border-slate-800 py-2 z-50 overflow-hidden">
                        <div class="px-4 py-2 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                            <h3 class="font-bold text-sm text-slate-800 dark:text-slate-200">{{ __('Thông báo xuất file') }}</h3>
                            <div class="flex items-center space-x-2">
                                <template x-if="histories.some(h => h.status === 'completed' || h.status === 'failed')">
                                    <button @click.stop="clearCompletedNotifications()" class="text-xs text-rose-500 hover:text-rose-600 font-semibold focus:outline-none">
                                        {{ __('Xóa nhanh') }}
                                    </button>
                                </template>
                                <template x-if="histories.some(h => h.status === 'completed' || h.status === 'failed')">
                                    <span class="text-slate-200 dark:text-slate-800 text-xs">|</span>
                                </template>
                                <a href="{{ route('admin.export-histories.index') }}" class="text-xs text-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400 font-semibold">{{ __('Xem tất cả') }}</a>
                            </div>
                        </div>
                        <div class="max-h-[350px] overflow-y-auto divide-y divide-slate-50 dark:divide-slate-800/50">
                            <template x-if="histories.length === 0">
                                <div class="px-4 py-8 text-center text-xs text-slate-400 dark:text-slate-500">
                                    {{ __('Chưa có thông báo xuất file nào.') }}
                                </div>
                            </template>
                            <template x-for="item in histories" :key="item.id">
                                <div class="p-3 hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors flex items-start space-x-3 cursor-pointer"
                                     @click="window.location.href = '{{ route('admin.export-histories.index') }}'">
                                    <!-- Status Icon -->
                                    <div class="shrink-0 w-8 h-8 rounded-lg flex items-center justify-center"
                                         :class="{
                                             'bg-amber-50 dark:bg-amber-500/10 text-amber-500': item.status === 'pending' || item.status === 'processing',
                                             'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-500': item.status === 'completed',
                                             'bg-rose-50 dark:bg-rose-500/10 text-rose-500': item.status === 'failed'
                                         }">
                                        <!-- Pending/Processing: Spinner -->
                                        <template x-if="item.status === 'pending' || item.status === 'processing'">
                                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </template>
                                        <!-- Completed: File Excel Icon -->
                                        <template x-if="item.status === 'completed'">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </template>
                                        <!-- Failed: Close Icon -->
                                        <template x-if="item.status === 'failed'">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                            </svg>
                                        </template>
                                    </div>

                                    <!-- Content -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between">
                                            <p class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate pr-2" x-text="item.title"></p>
                                            <button @click.stop="deleteNotification(item.id)" 
                                                    class="text-slate-400 dark:text-slate-500 hover:text-rose-500 dark:hover:text-rose-400 p-1 -mt-1 rounded-md transition-colors"
                                                    title="{{ __('Xóa thông báo') }}">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>
                                        <p class="text-[10px] text-slate-400 mt-0.5 truncate" x-text="item.filename"></p>
                                        
                                        <div class="flex items-center justify-between mt-1">
                                            <span class="text-[9px] text-slate-400" x-text="formatDate(item.created_at)"></span>
                                            
                                            <!-- Status text / action -->
                                            <div @click.stop>
                                                <template x-if="item.status === 'pending' || item.status === 'processing'">
                                                    <div class="flex flex-col items-end space-y-1">
                                                        <span class="text-[9px] font-bold text-amber-500" 
                                                              x-text="item.status === 'processing' ? '{{ __('Đang xử lý...') }} ' + (item.progress || 10) + '%' : '{{ __('Đang chờ...') }}'"></span>
                                                        <template x-if="item.status === 'processing'">
                                                            <div class="w-16 bg-slate-100 dark:bg-slate-800 rounded-full h-1 overflow-hidden">
                                                                <div class="bg-amber-500 h-1 transition-all duration-300" :style="'width: ' + (item.progress || 10) + '%'"></div>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </template>
                                                <template x-if="item.status === 'completed'">
                                                    <a :href="'/topsecret/export-histories/' + item.id + '/download'" 
                                                       class="text-[9px] font-bold text-white bg-emerald-500 hover:bg-emerald-600 px-2 py-0.5 rounded transition">
                                                        {{ __('Tải nhanh') }}
                                                    </a>
                                                </template>
                                                <template x-if="item.status === 'failed'">
                                                    <span class="text-[9px] font-semibold text-rose-500" :title="item.error_message">{{ __('Thất bại') }}</span>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="flex items-center ml-4 pl-4 border-l border-slate-200 dark:border-slate-800">
                    <div class="text-right mr-3 hidden sm:block">
                        <p class="text-sm font-bold text-gray-800 dark:text-slate-100 leading-none mb-1">
                            {{ Auth::user()->full_name ?? Auth::user()->name }}
                        </p>
                        <p
                            class="text-[10px] font-semibold text-indigo-500 dark:text-indigo-400 uppercase tracking-wider">
                            {{ Auth::user()->roles->pluck('display_name')->implode(', ') }}
                        </p>
                    </div>
                    <div class="h-9 w-9 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm shadow-lg shadow-indigo-500/20 ring-2 ring-white dark:ring-slate-900"
                        title="{{ Auth::user()->name ?? '' }}">
                        {{ substr(Auth::user()->name ?? 'A', 0, 1) }}
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main
            class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 dark:bg-slate-900 p-6 transition-colors duration-300">
            <x-breadcrumb />
            @yield('content')
        </main>
    </div>
    <!-- Toast Notifications -->
    <div x-data="toastManager" @toast.window="add($event.detail)"
        class="fixed top-6 right-6 z-[100] flex flex-col items-end space-y-3 pointer-events-none">
        <template x-for="toast in toasts" :key="toast.id">
            <div x-show="toast.visible" x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="translate-x-full opacity-0 scale-95"
                x-transition:enter-end="translate-x-0 opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="translate-x-0 opacity-100 scale-100"
                x-transition:leave-end="translate-x-full opacity-0 scale-95"
                class="pointer-events-auto bg-white dark:bg-slate-900 rounded-2xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.1)] border border-slate-100 dark:border-slate-800 p-4 min-w-[320px] max-w-md flex items-center space-x-4">

                <div class="shrink-0 w-10 h-10 rounded-xl flex items-center justify-center" :class="{
                        'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400': toast.type === 'success',
                        'bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400': toast.type === 'error' || toast.type === 'danger',
                        'bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400': toast.type === 'warning',
                        'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400': toast.type === 'info'
                     }">
                    <template x-if="toast.type === 'success'"><svg class="w-6 h-6" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg></template>
                    <template x-if="toast.type === 'error' || toast.type === 'danger'"><svg class="w-6 h-6" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg></template>
                    <template x-if="toast.type === 'warning'"><svg class="w-6 h-6" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                            </path>
                        </svg></template>
                    <template x-if="toast.type === 'info'"><svg class="w-6 h-6" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg></template>
                </div>

                <div class="flex-1">
                    <p class="text-sm font-bold text-slate-900 dark:text-slate-100" x-text="toast.message"></p>
                </div>

                <button @click="remove(toast.id)"
                    class="text-slate-300 dark:text-slate-600 hover:text-slate-500 dark:hover:text-slate-400 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>
        </template>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('toastManager', () => ({
                toasts: [],
                init() {
                    const sessionToasts = [];
                    @if(session('success')) sessionToasts.push({
                        message: @js(session('success')),
                        type: 'success'
                    });
                    @endif
                    @if(session('error')) sessionToasts.push({
                        message: @js(session('error')),
                        type: 'error'
                    });
                    @endif
                    @if(session('warning')) sessionToasts.push({
                        message: @js(session('warning')),
                        type: 'warning'
                    });
                    @endif
                    @if(session('info')) sessionToasts.push({
                        message: @js(session('info')),
                        type: 'info'
                    });
                    @endif

                    @if($errors->any())
                    @foreach($errors->all() as $error)
                    sessionToasts.push({
                        message: @js($error),
                        type: 'error'
                    });
                    @endforeach
                    @endif

                    // Process initial toasts with delay to ensure animations work
                    sessionToasts.forEach((t, i) => {
                        setTimeout(() => this.add(t), i * 200);
                    });
                },
                add(data) {
                    const id = Math.random().toString(36).substr(2, 9);
                    this.toasts.push({
                        id: id,
                        message: data.message,
                        type: data.type || 'info',
                        visible: false
                    });

                    setTimeout(() => {
                        const toast = this.toasts.find(t => t.id === id);
                        if (toast) toast.visible = true;
                    }, 100);

                    setTimeout(() => this.remove(id), 5000);
                },
                remove(id) {
                    const toast = this.toasts.find(t => t.id === id);
                    if (toast) {
                        toast.visible = false;
                        setTimeout(() => {
                            this.toasts = this.toasts.filter(t => t.id !== id);
                        }, 300);
                    }
                }
            }));

            // Register notificationManager for export history bell dropdown
            Alpine.data('notificationManager', () => ({
                isOpen: false,
                unreadCount: 0,
                histories: [],
                pollingTimer: null,
                init() {
                    this.fetchHistories(true); // silent load on start
                    
                    // Lắng nghe sự kiện click xuất file từ trang con để bắt đầu polling
                    window.addEventListener('export-started', () => {
                        this.startPolling();
                    });
                },
                startPolling() {
                    if (this.pollingTimer) return; // Đang chạy rồi thì không tạo thêm
                    
                    this.pollingTimer = setInterval(() => {
                        this.fetchHistories();
                    }, 5000);
                },
                stopPolling() {
                    if (this.pollingTimer) {
                        clearInterval(this.pollingTimer);
                        this.pollingTimer = null;
                    }
                },
                fetchHistories(silent = false) {
                    fetch('{{ route("admin.export-histories.list") }}')
                        .then(res => res.json())
                        .then(data => {
                            if (!silent) {
                                // Check for any status changes to trigger toast
                                data.histories.forEach(newItem => {
                                    const oldItem = this.histories.find(h => h.id === newItem.id);
                                    if (oldItem && (oldItem.status === 'pending' || oldItem.status === 'processing') && newItem.status !== oldItem.status) {
                                        if (newItem.status === 'completed') {
                                            window.dispatchEvent(new CustomEvent('toast', { 
                                                detail: { 
                                                    message: `Xuất thành công: ${newItem.title}. Nhấn biểu tượng chuông để tải nhanh!`, 
                                                    type: 'success' 
                                                } 
                                            }));
                                        } else if (newItem.status === 'failed') {
                                            window.dispatchEvent(new CustomEvent('toast', { 
                                                detail: { 
                                                    message: `Xuất thất bại: ${newItem.title}.`, 
                                                    type: 'error' 
                                                } 
                                            }));
                                        }
                                    }
                                });
                            }
                            this.histories = data.histories;
                            this.unreadCount = data.unread_count;

                            // Kiểm tra xem còn tiến trình nào đang chạy nền (pending/processing) không
                            const hasActiveJobs = this.histories.some(h => h.status === 'pending' || h.status === 'processing');
                            if (hasActiveJobs) {
                                this.startPolling();
                            } else {
                                this.stopPolling();
                            }
                        })
                        .catch(err => {
                            console.error('Error fetching export histories:', err);
                            this.stopPolling();
                        });
                },
                toggle() {
                    this.isOpen = !this.isOpen;
                    if (this.isOpen && this.unreadCount > 0) {
                        fetch('{{ route("admin.export-histories.mark-all-read") }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json'
                            }
                        })
                        .then(() => {
                            this.unreadCount = 0;
                        });
                    }
                },
                deleteNotification(id) {
                    fetch(`/topsecret/export-histories/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            this.histories = this.histories.filter(h => h.id !== id);
                            window.dispatchEvent(new CustomEvent('toast', { 
                                detail: { 
                                    message: '{{ __("Đã xóa thông báo thành công.") }}', 
                                    type: 'success' 
                                } 
                            }));
                        }
                    })
                    .catch(err => console.error('Error deleting notification:', err));
                },
                async clearCompletedNotifications() {
                    if (window.SwalHelper) {
                        const confirmed = await window.SwalHelper.showConfirm(
                            '{{ __("Xác nhận xóa") }}',
                            '{{ __("Bạn có chắc chắn muốn xóa nhanh tất cả thông báo đã xuất xong?") }}',
                            '{{ __("Xóa nhanh") }}',
                            '{{ __("Hủy bỏ") }}'
                        );
                        if (!confirmed) return;
                    } else {
                        if (!confirm('{{ __("Bạn có chắc chắn muốn xóa nhanh tất cả thông báo đã xuất xong?") }}')) {
                            return;
                        }
                    }
                    
                    fetch('{{ route("admin.export-histories.clear-completed") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            this.histories = this.histories.filter(h => h.status === 'pending' || h.status === 'processing');
                            window.dispatchEvent(new CustomEvent('toast', { 
                                detail: { 
                                    message: '{{ __("Đã xóa nhanh các thông báo hoàn thành.") }}', 
                                    type: 'success' 
                                } 
                            }));
                        }
                    })
                    .catch(err => console.error('Error clearing notifications:', err));
                },
                formatDate(dateStr) {
                    if (!dateStr) return '';
                    const date = new Date(dateStr);
                    return date.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' }) + ' ' + date.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit' });
                }
            }));
        });
    </script>

    <!-- Scroll To Top/Bottom Button -->
    <div x-data="{
        isAtTop: true,
        mainElement: null,
        init() {
            // Find the main content element specifically
            this.mainElement = document.querySelector('main');
            
            if (!this.mainElement) {
                return;
            }
            
            // Listen to scroll events on main element
            this.mainElement.addEventListener('scroll', () => {
                this.isAtTop = this.mainElement.scrollTop < 100;
            });
            
            // Check initial scroll position
            this.isAtTop = this.mainElement.scrollTop < 100;
        },
        scrollToPosition() {
            if (!this.mainElement) {
                return;
            }
            
            if (this.isAtTop) {
                // Scroll to bottom of main content
                this.mainElement.scrollTo({ 
                    top: this.mainElement.scrollHeight, 
                    behavior: 'smooth' 
                });
            } else {
                // Scroll to top of main content
                this.mainElement.scrollTo({ 
                    top: 0, 
                    behavior: 'smooth' 
                });
            }
        }
    }" 
    class="fixed bottom-6 right-8 z-[90]">
        <button @click="scrollToPosition()"
            class="p-3.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-full shadow-lg shadow-indigo-300/50 dark:shadow-none transition-all duration-300 group flex items-center justify-center focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-slate-900 hover:scale-110"
            :title="isAtTop ? 'Cuộn xuống cuối' : 'Cuộn lên đầu'">

            <!-- Icon Scroll To Top (Up arrow) - Shows when NOT at top -->
            <svg x-show="!isAtTop" x-cloak class="w-5 h-5 transition-transform duration-300 group-hover:-translate-y-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
            </svg>

            <!-- Icon Scroll To Bottom (Down arrow) - Shows when AT top -->
            <svg x-show="isAtTop" class="w-5 h-5 transition-transform duration-300 group-hover:translate-y-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
            </svg>
        </button>
    </div>

    @vite(['resources/js/app.js'])
    <script>
        lucide.createIcons();
    </script>
    @stack('scripts')
</body>

</html>
