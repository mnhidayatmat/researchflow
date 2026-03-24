<!DOCTYPE html>
<html lang="en" class="h-full" x-data="themeManager()" x-init="initTheme()">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'ResearchFlow' }} — ResearchFlow</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['-apple-system', 'BlinkMacSystemFont', 'Inter', 'Segoe UI', 'sans-serif'],
                    },
                    colors: {
                        surface: '#FAFAF9',
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
                        dark: {
                            bg: '#0D0D0D',
                            surface: '#1A1A1A',
                            card: '#1C1C1E',
                            border: '#2C2C2E',
                            'border-light': '#252527',
                            primary: '#F5F5F7',
                            secondary: '#86868B',
                            tertiary: '#636366',
                            accent: '#FF9F0A',
                            'accent-light': 'rgba(255, 159, 10, 0.15)',
                            success: '#30D158',
                            'success-light': 'rgba(48, 209, 88, 0.15)',
                            warning: '#FFD60A',
                            'warning-light': 'rgba(255, 214, 10, 0.15)',
                            danger: '#FF453A',
                            'danger-light': 'rgba(255, 69, 58, 0.15)',
                            info: '#0A84FF',
                            'info-light': 'rgba(10, 132, 255, 0.15)',
                        }
                    },
                    boxShadow: {
                        'soft': '0 1px 3px rgba(0,0,0,0.04), 0 1px 2px rgba(0,0,0,0.06)',
                        'medium': '0 4px 6px -1px rgba(0,0,0,0.04), 0 2px 4px -1px rgba(0,0,0,0.02)',
                        'dark-soft': '0 1px 3px rgba(0,0,0,0.3), 0 1px 2px rgba(0,0,0,0.2)',
                        'dark-medium': '0 4px 6px -1px rgba(0,0,0,0.3), 0 2px 4px -1px rgba(0,0,0,0.2)',
                    }
                }
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        * { -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; }

        .scrollbar-thin::-webkit-scrollbar { width: 6px; height: 6px; }
        .scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
        .scrollbar-thin::-webkit-scrollbar-thumb { background: #E5E5E4; border-radius: 3px; }
        .scrollbar-thin::-webkit-scrollbar-thumb:hover { background: #D4D4D4; }
        .dark .scrollbar-thin::-webkit-scrollbar-thumb { background: #3A3A3C; }
        .dark .scrollbar-thin::-webkit-scrollbar-thumb:hover { background: #4A4A4C; }

        *, *::before, *::after {
            transition-property: color, background-color, border-color;
            transition-duration: 200ms;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
        }
        .no-transition *, .no-transition *::before, .no-transition *::after {
            transition: none !important;
        }

        @media (max-width: 639px) {
            .mobile-safe-px {
                padding-left: max(1rem, env(safe-area-inset-left));
                padding-right: max(1rem, env(safe-area-inset-right));
            }
        }
        @media (max-width: 1023px) {
            .mobile-bottom-spacing {
                padding-bottom: calc(4.5rem + env(safe-area-inset-bottom, 0px));
            }
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="h-full bg-surface dark:bg-dark-bg text-primary dark:text-dark-primary font-sans">
    <div class="min-h-full overflow-x-hidden" x-data="{
        sidebarOpen: false,
        sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true'
    }" x-init="
        $watch('sidebarCollapsed', val => localStorage.setItem('sidebarCollapsed', val));
        $el.addEventListener('close-mobile-sidebar', () => { sidebarOpen = false; });
    ">
        {{-- Mobile sidebar overlay --}}
        <div x-show="sidebarOpen" x-cloak
             x-transition:enter="transition-opacity ease-out duration-200"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-150"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 lg:hidden">
            <div class="fixed inset-0 bg-primary/20 dark:bg-black/50 backdrop-blur-sm" @click="sidebarOpen = false"></div>
            <div class="fixed inset-y-0 left-0 w-[calc(100vw-2.5rem)] max-w-72 bg-white dark:bg-dark-card shadow-2xl"
                 x-transition:enter="transition-transform ease-out duration-200"
                 x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition-transform ease-in duration-150"
                 x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full">
                @include('layouts.sidebar')
            </div>
        </div>

        {{-- Desktop sidebar --}}
        <aside :class="sidebarCollapsed ? 'w-16' : 'w-64'"
               class="hidden lg:fixed lg:inset-y-0 lg:flex lg:flex-col bg-white dark:bg-dark-card border-r border-border dark:border-dark-border transition-all duration-200 ease-in-out z-40">
            @include('layouts.sidebar')
        </aside>

        {{-- Main content --}}
        <div :class="sidebarCollapsed ? 'lg:pl-16' : 'lg:pl-64'" class="min-h-screen transition-all duration-200 ease-in-out">
            {{-- Top bar --}}
            @include('layouts.topbar')

            {{-- Flash messages --}}
            <div class="mobile-safe-px px-4 pt-4 space-y-2 sm:px-6 lg:px-8">
                @if(session('success'))
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 translate-y-2"
                         class="bg-success-light dark:bg-dark-success-light border border-success/20 dark:border-dark-success/30 text-success dark:text-dark-success text-sm px-4 py-3 rounded-lg flex items-start gap-3">
                        <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="flex-1">{{ session('success') }}</span>
                        <button @click="show = false" class="text-success/60 dark:text-dark-success/60 hover:text-success dark:hover:text-dark-success">&times;</button>
                    </div>
                @endif
                @if(session('error'))
                    <div x-data="{ show: true }" x-show="show"
                         class="bg-danger-light dark:bg-dark-danger-light border border-danger/20 dark:border-dark-danger/30 text-danger dark:text-dark-danger text-sm px-4 py-3 rounded-lg flex items-start gap-3">
                        <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="flex-1">{{ session('error') }}</span>
                        <button @click="show = false" class="text-danger/60 dark:text-dark-danger/60 hover:text-danger dark:hover:text-dark-danger">&times;</button>
                    </div>
                @endif
                @if(session('warning'))
                    <div x-data="{ show: true }" x-show="show"
                         class="bg-warning-light dark:bg-dark-warning-light border border-warning/20 dark:border-dark-warning/30 text-warning dark:text-dark-warning text-sm px-4 py-3 rounded-lg flex items-start gap-3">
                        <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span class="flex-1">{{ session('warning') }}</span>
                        <button @click="show = false" class="text-warning/60 dark:text-dark-warning/60 hover:text-warning dark:hover:text-dark-warning">&times;</button>
                    </div>
                @endif
            </div>

            {{-- Page content --}}
            <main class="mobile-safe-px px-4 py-6 sm:px-6 lg:px-8 mobile-bottom-spacing">
                {{ $slot }}
            </main>
        </div>

        {{-- Mobile Bottom Navigation --}}
        @auth
            @include('layouts.bottom-nav')
        @endauth
    </div>

    <script>
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content;
        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        axios.defaults.withCredentials = true;

        document.addEventListener('keydown', (e) => {
            if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                e.preventDefault();
                document.querySelector('[placeholder*="Search"]')?.focus();
            }
            if (e.key === 'Escape') {
                document.dispatchEvent(new CustomEvent('escape-key'));
            }
        });

        function themeManager() {
            return {
                theme: 'light',
                initTheme() {
                    const stored = localStorage.getItem('theme');
                    const system = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                    this.theme = stored || system;
                    this.applyTheme();
                },
                toggleTheme() {
                    this.theme = this.theme === 'dark' ? 'light' : 'dark';
                    localStorage.setItem('theme', this.theme);
                    this.applyTheme();
                    if (document.querySelector('meta[name="csrf-token"]')) {
                        axios.post('/settings/theme', { theme: this.theme }).catch(() => {});
                    }
                },
                applyTheme() {
                    const html = document.documentElement;
                    const body = document.body;
                    body.classList.add('no-transition');
                    if (this.theme === 'dark') {
                        html.classList.add('dark');
                    } else {
                        html.classList.remove('dark');
                    }
                    requestAnimationFrame(() => {
                        requestAnimationFrame(() => {
                            body.classList.remove('no-transition');
                        });
                    });
                }
            }
        }

        function notifications() {
            return {
                open: false,
                unreadCount: 0,
                items: [],
                async init() {
                    try {
                        const res = await axios.get('/api/notifications');
                        this.unreadCount = res.data.unread_count;
                        this.items = res.data.notifications;
                    } catch(e) {}
                },
                toggle() { this.open = !this.open; }
            }
        }
    </script>
    @stack('scripts')
</body>
</html>
