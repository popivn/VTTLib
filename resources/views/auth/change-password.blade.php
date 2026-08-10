<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Đổi mật khẩu') }} - VTTLib</title>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
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
                        destructive: {
                            DEFAULT: 'hsl(var(--destructive))',
                            foreground: 'hsl(var(--destructive-foreground))',
                        },
                        muted: {
                            DEFAULT: 'hsl(var(--muted))',
                            foreground: 'hsl(var(--muted-foreground))',
                        },
                        card: {
                            DEFAULT: 'hsl(var(--card))',
                            foreground: 'hsl(var(--card-foreground))',
                        },
                    },
                },
            },
        }
    </script>

    <style>
        :root {
            --background: 210 40% 96.1%;
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
        [x-cloak] { display: none !important; }
    </style>
</head>

<body class="h-full bg-background text-foreground flex items-center justify-center p-4">
    <div class="w-full max-w-sm bg-card border border-border rounded-md shadow-sm p-4 space-y-4 relative">

        <!-- Header -->
        <div class="text-center space-y-1 py-2">
            <div class="flex justify-center mb-2">
                <span class="w-10 h-10 rounded-full bg-primary/10 text-primary flex items-center justify-center border border-primary/20">
                    <i data-lucide="key-round" class="w-5 h-5"></i>
                </span>
            </div>
            <h1 class="text-xl font-bold tracking-tight text-foreground">{{ __('Đổi mật khẩu') }}</h1>
            <p class="text-xs text-muted-foreground">{{ __('Đây là lần đầu bạn đăng nhập. Vui lòng đổi mật khẩu để tiếp tục.') }}</p>
        </div>

        @if($errors->any())
            <div class="p-3 border border-destructive/20 bg-destructive/10 text-destructive text-xs font-semibold rounded-sm space-y-1">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        @if(session('success'))
            <div class="p-3 border border-green-200 bg-green-50 text-green-700 text-xs font-semibold rounded-sm">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('password.change.update') }}" method="POST" class="space-y-3">
            @csrf

            <div class="space-y-1">
                <label for="new_password" class="block text-[10px] font-bold text-muted-foreground uppercase tracking-wider">{{ __('Mật khẩu mới') }}</label>
                <div class="relative">
                    <input
                        id="new_password"
                        name="new_password"
                        type="password"
                        required
                        class="w-full h-9 pl-8 pr-3 text-xs border border-input rounded-sm bg-background text-foreground placeholder-muted-foreground/60 focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all"
                        placeholder="{{ __('Nhập mật khẩu mới') }}"
                        autocomplete="new-password"
                    >
                    <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-muted-foreground">
                        <i data-lucide="lock" class="w-4 h-4"></i>
                    </div>
                </div>
            </div>

            <div class="space-y-1">
                <label for="new_password_confirmation" class="block text-[10px] font-bold text-muted-foreground uppercase tracking-wider">{{ __('Xác nhận mật khẩu mới') }}</label>
                <div class="relative">
                    <input
                        id="new_password_confirmation"
                        name="new_password_confirmation"
                        type="password"
                        required
                        class="w-full h-9 pl-8 pr-3 text-xs border border-input rounded-sm bg-background text-foreground placeholder-muted-foreground/60 focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all"
                        placeholder="{{ __('Nhập lại mật khẩu mới') }}"
                        autocomplete="new-password"
                    >
                    <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-muted-foreground">
                        <i data-lucide="lock-keyhole" class="w-4 h-4"></i>
                    </div>
                </div>
            </div>

            <button type="submit" class="w-full h-9 flex items-center justify-center text-xs font-bold uppercase rounded-sm bg-primary text-primary-foreground hover:bg-primary/90 shadow-sm transition-all">
                {{ __('Đổi mật khẩu') }}
            </button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
</body>
</html>
