@props(['label' => 'VTTLib Admin'])

<div id="admin-loading-overlay"
     x-data="{ show: true }"
     x-show="show"
     x-transition:leave="transition ease-out duration-300"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     x-init="window.addEventListener('load', () => { setTimeout(() => show = false, 100); })"
     class="fixed inset-0 z-[9999] flex items-center justify-center bg-white dark:bg-slate-950">
    <div class="flex flex-col items-center gap-5">
        <div class="admin-loader-logo">
            <img src="{{ asset('assets/imgs/logo-vttu.png') }}" alt="VTTU" class="w-16 h-16 object-contain">
        </div>
        <div class="admin-loader-text">{{ $label }}</div>
    </div>
</div>

<style>
    .admin-loader-logo {
        perspective: 200px;
    }
    .admin-loader-logo img {
        animation: admin-logo-3d 2s ease-in-out infinite;
        filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.15));
    }
    .dark .admin-loader-logo img {
        filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.4)) brightness(1.1);
    }
    .admin-loader-text {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: hsl(215.4 16.3% 46.9%);
        animation: admin-pulse 1.5s ease-in-out infinite;
    }
    .dark .admin-loader-text {
        color: hsl(215 20.2% 65.1%);
    }
    @keyframes admin-logo-3d {
        0% { transform: rotateY(0deg); }
        25% { transform: rotateY(-15deg); }
        50% { transform: rotateY(0deg); }
        75% { transform: rotateY(15deg); }
        100% { transform: rotateY(0deg); }
    }
    @keyframes admin-pulse {
        0%, 100% { opacity: 0.5; }
        50% { opacity: 1; }
    }
</style>
