<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'ResearchFlow' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        surface: '#FAFAF9',
                        surface: '#F7F7F5',
                        card: '#FFFFFF',
                        border: '#E5E5E4',
                        'border-light': '#F5F5F4',
                        primary: '#1C1917',
                        secondary: '#78716C',
                        tertiary: '#A8A29E',
                        accent: '#D97706',
                        'accent-light': '#FEF3C7',
                        success: '#059669',
                        'success-light': '#D1FAE5',
                        warning: '#F59E0B',
                        'warning-light': '#FEF3C7',
                        danger: '#DC2626',
                        'danger-light': '#FEE2E2',
                        info: '#2563EB',
                        'info-light': '#DBEAFE',
                    },
                    fontFamily: {
                        sans: ['-apple-system', 'BlinkMacSystemFont', 'Inter', 'Segoe UI', 'sans-serif'],
                    },
                    boxShadow: {
                        'soft': '0 1px 3px rgba(0,0,0,0.04), 0 1px 2px rgba(0,0,0,0.06)',
                        'medium': '0 4px 6px -1px rgba(0,0,0,0.04), 0 2px 4px -1px rgba(0,0,0,0.02)',
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        * { -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; }
        .scrollbar-thin::-webkit-scrollbar { width: 6px; height: 6px; }
        .scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
        .scrollbar-thin::-webkit-scrollbar-thumb { background: #E5E5E4; border-radius: 3px; }
        .scrollbar-thin::-webkit-scrollbar-thumb:hover { background: #D4D4D4; }
    </style>
    @stack('styles')
</head>
<body class="h-full bg-surface text-primary font-sans">
    <div class="min-h-full" x-data="{
        sidebarOpen: false,
        sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true'
    }" x-init="
        $watch('sidebarCollapsed', val => localStorage.setItem('sidebarCollapsed', val));
        $el.addEventListener('sidebar-toggled', e => { sidebarCollapsed = e.detail.collapsed; });
    ">
        {{-- Mobile sidebar backdrop --}}
        <div x-show="sidebarOpen" x-cloak
             x-transition:enter="transition-opacity ease-out duration-200"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-150"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 lg:hidden">
            <div class="fixed inset-0 bg-primary/20 backdrop-blur-sm" @click="sidebarOpen = false"></div>
            <div class="fixed inset-y-0 left-0 w-72 bg-white shadow-2xl"
                 x-transition:enter="transition-transform ease-out duration-200"
                 x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition-transform ease-in duration-150"
                 x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full">
                @include('layouts.sidebar')
            </div>
        </div>

        {{-- Desktop sidebar --}}
        <aside :class="sidebarCollapsed ? 'w-16' : 'w-64'"
               class="hidden lg:fixed lg:inset-y-0 lg:flex lg:flex-col bg-white border-r border-border transition-all duration-200 ease-in-out z-40">
            @include('layouts.sidebar')
        </aside>

        {{-- Main content area --}}
        <div :class="sidebarCollapsed ? 'lg:pl-16' : 'lg:pl-64'" class="min-h-screen transition-all duration-200 ease-in-out">
            {{-- Top bar --}}
            @include('layouts.topbar')

            {{-- Flash notifications --}}
            <div class="px-4 sm:px-6 lg:px-8 pt-4 space-y-2">
                @if(session('success'))
                    <div x-data="{ show: true }" x-show="show"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 translate-y-2"
                         class="bg-success-light border border-success/20 text-success text-sm px-4 py-3 rounded-lg flex items-start gap-3">
                        <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="flex-1">{{ session('success') }}</span>
                        <button @click="show = false" class="text-success/60 hover:text-success">&times;</button>
                    </div>
                @endif
                @if(session('error'))
                    <div x-data="{ show: true }" x-show="show"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 translate-y-2"
                         class="bg-danger-light border border-danger/20 text-danger text-sm px-4 py-3 rounded-lg flex items-start gap-3">
                        <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="flex-1">{{ session('error') }}</span>
                        <button @click="show = false" class="text-danger/60 hover:text-danger">&times;</button>
                    </div>
                @endif
                @if(session('warning'))
                    <div x-data="{ show: true }" x-show="show"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 translate-y-2"
                         class="bg-warning-light border border-warning/20 text-warning text-sm px-4 py-3 rounded-lg flex items-start gap-3">
                        <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span class="flex-1">{{ session('warning') }}</span>
                        <button @click="show = false" class="text-warning/60 hover:text-warning">&times;</button>
                    </div>
                @endif
                @if(session('info'))
                    <div x-data="{ show: true }" x-show="show"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 translate-y-2"
                         class="bg-info-light border border-info/20 text-info text-sm px-4 py-3 rounded-lg flex items-start gap-3">
                        <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="flex-1">{{ session('info') }}</span>
                        <button @click="show = false" class="text-info/60 hover:text-info">&times;</button>
                    </div>
                @endif
            </div>

            {{-- Page content --}}
            <main class="px-4 sm:px-6 lg:px-8 py-6">
                {{ $slot }}
            </main>
        </div>
    </div>

    <script>
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content;
        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        axios.defaults.withCredentials = true;

        document.addEventListener('keydown', (e) => {
            // Global keyboard shortcuts
            if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                e.preventDefault();
                // Focus search or open command palette
                document.querySelector('[placeholder*="Search"]')?.focus();
            }
            if (e.key === 'Escape') {
                document.dispatchEvent(new CustomEvent('escape-key'));
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
