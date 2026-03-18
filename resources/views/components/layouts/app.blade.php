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
                    colors: {
                        surface: '#F7F7F5',
                        card: '#FFFFFF',
                        border: '#E5E7EB',
                        primary: '#1F2937',
                        secondary: '#6B7280',
                        accent: '#D97706',
                        'accent-light': '#FEF3C7',
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Inter', 'Segoe UI', sans-serif; }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="h-full bg-surface text-primary">
    <div class="min-h-full" x-data="{ sidebarOpen: false }">
        {{-- Mobile sidebar overlay --}}
        <div x-show="sidebarOpen" x-cloak class="fixed inset-0 z-40 lg:hidden">
            <div class="fixed inset-0 bg-gray-900/50" @click="sidebarOpen = false"></div>
            <div class="fixed inset-y-0 left-0 w-64 bg-white shadow-xl">
                @include('layouts.sidebar')
            </div>
        </div>

        {{-- Desktop sidebar --}}
        <div class="hidden lg:fixed lg:inset-y-0 lg:flex lg:w-60 lg:flex-col">
            <div class="flex flex-col flex-grow bg-white border-r border-border">
                @include('layouts.sidebar')
            </div>
        </div>

        {{-- Main content --}}
        <div class="lg:pl-60">
            {{-- Top bar --}}
            <header class="sticky top-0 z-30 bg-white/80 backdrop-blur-sm border-b border-border">
                <div class="flex items-center justify-between h-14 px-4 sm:px-6">
                    <div class="flex items-center gap-3">
                        <button @click="sidebarOpen = true" class="lg:hidden text-secondary hover:text-primary">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </button>
                        <h1 class="text-sm font-medium text-primary">{{ $header ?? '' }}</h1>
                    </div>
                    <div class="flex items-center gap-3">
                        {{-- Notifications --}}
                        <div x-data="notifications()" class="relative">
                            <button @click="toggle()" class="relative p-1.5 text-secondary hover:text-primary rounded-md hover:bg-gray-100">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                <span x-show="unreadCount > 0" class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-accent text-white text-[10px] font-bold rounded-full flex items-center justify-center" x-text="unreadCount"></span>
                            </button>
                        </div>

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
