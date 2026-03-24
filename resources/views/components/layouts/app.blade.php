<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'ResearchFlow' }} — ResearchFlow</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        serif: ['"Playfair Display"', 'Georgia', 'serif'],
                        sans: ['"DM Sans"', '-apple-system', 'BlinkMacSystemFont', '"Segoe UI"', 'sans-serif'],
                    },
                    colors: {
                        surface: '#F7F7F5',
                        card: '#FFFFFF',
                        border: '#E5E7EB',
                        primary: '#1F2937',
                        secondary: '#6B7280',
                        accent: '#D97706',
                        'accent-light': '#FEF3C7',
                        ink: '#1a1a2e',
                        paper: '#faf9f7',
                        cream: '#f5f3ef',
                        sage: {
                            50: '#f7f9f7',
                            100: '#e8ede9',
                            200: '#d5e0d8',
                            300: '#b8c9b9',
                            400: '#9aab9b',
                            500: '#7d8a7e',
                            600: '#5a6a5c',
                        },
                        teal: {
                            50: '#f0fdfa',
                            100: '#ccfbf1',
                            200: '#99f6e4',
                            300: '#5eead4',
                            400: '#2dd4bf',
                            500: '#14b8a6',
                            600: '#0d9488',
                        },
                        rose: {
                            50: '#fff1f2',
                            100: '#ffe4e6',
                            200: '#fecdd3',
                            300: '#fda4af',
                            400: '#fb7185',
                            500: '#f43f5e',
                            600: '#e11d48',
                        },
                    }
                }
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Inter', 'Segoe UI', sans-serif; }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    <!-- Alpine.js loaded after Vite to ensure globals are available -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="h-full bg-surface text-primary">
    <div class="min-h-full" x-data="{ sidebarOpen: false, sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true' }">
        {{-- Mobile sidebar overlay --}}
        <div x-show="sidebarOpen" x-cloak class="fixed inset-0 z-40 lg:hidden">
            <div class="fixed inset-0 bg-gray-900/50" @click="sidebarOpen = false"></div>
            <div class="fixed inset-y-0 left-0 w-64 bg-white shadow-xl">
                @include('layouts.sidebar')
            </div>
        </div>

        {{-- Desktop sidebar --}}
        <div class="hidden lg:fixed lg:inset-y-0 lg:flex lg:flex-col transition-all duration-200"
             :class="sidebarCollapsed ? 'lg:w-20' : 'lg:w-60'">
            <div class="flex flex-col flex-grow bg-white border-r border-border">
                @include('layouts.sidebar')
            </div>
        </div>

        {{-- Main content --}}
        <div class="transition-all duration-200" :class="sidebarCollapsed ? 'lg:pl-20' : 'lg:pl-60'">
            {{-- Top bar --}}
            @php
                $role = auth()->user()->role;
                $effectiveRole = session()->get('admin_role_switch', $role);
                $isRoleSwitched = $role === 'admin' && $effectiveRole !== $role;
            @endphp
            <header class="sticky top-0 z-30 bg-white/80 backdrop-blur-sm border-b border-border @if($isRoleSwitched) border-t-4 border-t-accent @endif">
                <div class="flex items-center justify-between h-14 px-4 sm:px-6">
                    <div class="flex items-center gap-3">
                        <button @click="sidebarOpen = true" class="lg:hidden text-secondary hover:text-primary">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </button>
                        <h1 class="text-sm font-medium text-primary">{{ $header ?? '' }}</h1>
                    </div>
                    <div class="flex items-center gap-3">
                        {{-- Role Switcher (Admin only) --}}
                        @if(auth()->check() && auth()->user()->role === 'admin')
                            @php
                                $role = auth()->user()->role;
                                $effectiveRole = session()->get('admin_role_switch', $role);
                                $isRoleSwitched = $role === 'admin' && $effectiveRole !== $role;
                            @endphp
                            <div x-data="{ open: false }" class="relative">
                                <button @click="open = !open" @click.outside="open = false"
                                        class="flex items-center gap-2 px-3 py-1.5 text-sm rounded-xl border border-border hover:border-accent/30 hover:bg-surface transition-all @if($isRoleSwitched) bg-accent/5 border-accent/30 @endif">
                                    @if($isRoleSwitched)
                                        <span class="w-2 h-2 rounded-full bg-accent animate-pulse"></span>
                                        <span class="text-secondary hover:text-primary">{{ ucfirst($effectiveRole) }}</span>
                                    @else
                                        <span class="text-secondary hover:text-primary">View as:</span>
                                        <span class="text-primary font-medium">{{ ucfirst($effectiveRole) }}</span>
                                    @endif
                                    <svg class="w-4 h-4 text-tertiary" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>

                                <div x-show="open" x-cloak
                                     x-transition:enter="transition ease-out duration-150"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-100"
                                     x-transition:leave-start="opacity-100 scale-100"
                                     x-transition:leave-end="opacity-0 scale-95"
                                     class="absolute right-0 top-12 w-48 bg-white dark:bg-dark-card rounded-xl shadow-medium dark:shadow-dark-medium border border-border dark:border-dark-border overflow-hidden z-50">
                                    <div class="p-2">
                                        {{-- Admin Role --}}
                                        <form method="POST" action="{{ route('admin.switch-role-reset') }}" class="block">
                                            @csrf
                                            <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm @if(!$isRoleSwitched) bg-accent/10 text-accent @else text-secondary hover:bg-surface hover:text-primary @endif transition-colors">
                                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/30 dark:to-purple-800/20 flex items-center justify-center">
                                                    <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                                    </svg>
                                                </div>
                                                <div class="text-left">
                                                    <p class="font-medium text-primary">Admin</p>
                                                    <p class="text-xs text-secondary">Full access</p>
                                                </div>
                                            </button>
                                        </form>

                                        <div class="my-1 border-t border-border dark:border-dark-border"></div>

                                        {{-- Student Role --}}
                                        <form method="POST" action="{{ route('admin.switch-role') }}" class="block">
                                            @csrf
                                            <input type="hidden" name="role" value="student">
                                            <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm @if($effectiveRole === 'student') bg-accent/10 text-accent @else text-secondary hover:bg-surface hover:text-primary @endif transition-colors">
                                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-800/20 flex items-center justify-center">
                                                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14.9c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5zm2.5-10c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8z"/>
                                                    </svg>
                                                </div>
                                                <div class="text-left">
                                                    <p class="font-medium text-primary">Student</p>
                                                    <p class="text-xs text-secondary">Student view</p>
                                                </div>
                                            </button>
                                        </form>

                                        {{-- Supervisor Role --}}
                                        <form method="POST" action="{{ route('admin.switch-role') }}" class="block">
                                            @csrf
                                            <input type="hidden" name="role" value="supervisor">
                                            <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm @if($effectiveRole === 'supervisor') bg-accent/10 text-accent @else text-secondary hover:bg-surface hover:text-primary @endif transition-colors">
                                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/30 dark:to-green-800/20 flex items-center justify-center">
                                                    <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                                    </svg>
                                                </div>
                                                <div class="text-left">
                                                    <p class="font-medium text-primary">Supervisor</p>
                                                    <p class="text-xs text-secondary">Supervisor view</p>
                                                </div>
                                            </button>
                                        </form>

                                        {{-- Co-Supervisor Role --}}
                                        <form method="POST" action="{{ route('admin.switch-role') }}" class="block">
                                            @csrf
                                            <input type="hidden" name="role" value="cosupervisor">
                                            <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm @if($effectiveRole === 'cosupervisor') bg-accent/10 text-accent @else text-secondary hover:bg-surface hover:text-primary @endif transition-colors">
                                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/30 dark:to-orange-800/20 flex items-center justify-center">
                                                    <svg class="w-4 h-4 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                                    </svg>
                                                </div>
                                                <div class="text-left">
                                                    <p class="font-medium text-primary">Co-Supervisor</p>
                                                    <p class="text-xs text-secondary">Co-supervisor view</p>
                                                </div>
                                            </button>
                                        </form>

                                        @if($isRoleSwitched)
                                            <div class="mt-1 pt-2 border-t border-border dark:border-dark-border">
                                                <form method="POST" action="{{ route('admin.switch-role-reset') }}" class="block">
                                                    @csrf
                                                    <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 text-sm text-danger hover:bg-danger/5 rounded-lg transition-colors">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                        </svg>
                                                        Exit Role View
                                                    </button>
                                                </form>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Notifications --}}
                        <div x-data="notifications()" class="relative">

                        {{-- User menu --}}
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="flex items-center gap-2 text-sm text-secondary hover:text-primary">
                                <div class="w-7 h-7 rounded-full bg-accent/10 text-accent flex items-center justify-center text-xs font-semibold">
                                    {{ substr(auth()->user()->name, 0, 1) }}
                                </div>
                                <span class="hidden sm:block">{{ auth()->user()->name }}</span>
                            </button>
                            <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-border py-1 z-50">
                                <div class="px-3 py-2 border-b border-border">
                                    <p class="text-xs text-secondary">{{ ucfirst(auth()->user()->role) }}</p>
                                    <p class="text-xs text-secondary truncate">{{ auth()->user()->email }}</p>
                                </div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-3 py-2 text-sm text-secondary hover:bg-gray-50 hover:text-primary">Sign out</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Flash messages --}}
            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" class="mx-4 sm:mx-6 mt-4">
                    <div class="bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg flex justify-between">
                        <span>{{ session('success') }}</span>
                        <button @click="show = false" class="text-green-500 hover:text-green-700">&times;</button>
                    </div>
                </div>
            @endif
            @if(session('error'))
                <div x-data="{ show: true }" x-show="show" class="mx-4 sm:mx-6 mt-4">
                    <div class="bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg flex justify-between">
                        <span>{{ session('error') }}</span>
                        <button @click="show = false" class="text-red-500 hover:text-red-700">&times;</button>
                    </div>
                </div>
            @endif

            {{-- Page content --}}
            <main class="p-4 sm:p-6">
                {{ $slot }}
            </main>
        </div>
    </div>

    <script>
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content;
        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        axios.defaults.withCredentials = true;

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
