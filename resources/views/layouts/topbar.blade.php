@php
    $role = auth()->user()->role;
    $effectiveRole = session()->get('admin_role_switch', $role);
    $isRoleSwitched = $role === 'admin' && $effectiveRole !== $role;
@endphp

{{-- Enhanced topbar with improved visual hierarchy --}}
<div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-border @if($isRoleSwitched) border-t-4 border-t-accent @endif">
    <div class="flex items-center justify-between h-14 px-4 sm:px-6 lg:px-8">
        {{-- Left side --}}
        <div class="flex items-center gap-4">
            <button @click="$parent.sidebarOpen = true" class="lg:hidden -ml-2 p-2 text-secondary hover:text-primary rounded-lg hover:bg-surface transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            @if(isset($header))
                <nav class="hidden sm:flex items-center gap-2 text-sm">
                    <span class="text-primary font-medium">{{ $header }}</span>
                </nav>
            @endif

            {{-- Role switch indicator in header --}}
            @if($isRoleSwitched)
                <form method="POST" action="{{ route('admin.switch-role-reset') }}" class="hidden sm:flex items-center gap-2 px-3 py-1.5 bg-accent/10 border border-accent/20 rounded-lg">
                    @csrf
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-accent animate-pulse"></span>
                        <span class="text-xs font-medium text-accent">Viewing as {{ ucfirst($effectiveRole) }}</span>
                        <button type="submit" class="text-xs text-accent hover:text-amber-700 font-medium ml-1">
                            &times;
                        </button>
                    </div>
                </form>
            @endif
        </div>

        {{-- Right side --}}
        <div class="flex items-center gap-1 sm:gap-2">
            {{-- Search --}}
            <div x-data="{ open: false }" class="hidden md:block">
                <button @click="open = !open" class="p-2 text-secondary hover:text-primary rounded-lg hover:bg-surface transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </button>
                <div x-show="open" @click.outside="open = false" x-cloak
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute right-8 top-14 w-80 bg-white rounded-xl shadow-medium border border-border p-2 z-50">
                    <input type="text" placeholder="Search anything..." class="w-full px-3 py-2 text-sm border-0 bg-surface rounded-lg focus:outline-none focus:ring-2 focus:ring-accent/20">
                    <div class="mt-2 px-2 text-xs text-tertiary flex items-center justify-between">
                        <span>Use</span>
                        <kbd class="px-1.5 py-0.5 text-xs bg-border rounded font-mono">⌘K</kbd>
                    </div>
                </div>
            </div>

            {{-- Divider --}}
            <div class="hidden sm:block w-px h-6 bg-border"></div>

            {{-- Notifications --}}
            <div x-data="notifications()" class="relative">
                <button @click="toggle()" class="relative p-2 text-secondary hover:text-primary rounded-lg hover:bg-surface transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <span x-show="unreadCount > 0" x-cloak
                          class="absolute top-1.5 right-1.5 w-2 h-2 bg-accent rounded-full ring-2 ring-white"></span>
                </button>

                <div x-show="open" @click.away="open = false" x-cloak
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute right-0 top-12 w-80 bg-white rounded-xl shadow-medium border border-border overflow-hidden z-50">
                    <div class="px-4 py-3 border-b border-border">
                        <h3 class="text-sm font-semibold text-primary">Notifications</h3>
                    </div>
                    <div class="max-h-80 overflow-y-auto scrollbar-thin">
                        @if($items && $items->isNotEmpty())
                            @foreach($items as $item)
                                <div class="px-4 py-3 border-b border-border last:border-0 hover:bg-surface cursor-pointer transition-colors">
                                    <p class="text-sm font-medium text-primary">{{ $item['title'] ?? '' }}</p>
                                    <p class="text-xs text-secondary mt-0.5">{{ $item['body'] ?? '' }}</p>
                                </div>
                            @endforeach
                        @else
                            <div class="px-4 py-8 text-center">
                                <div class="w-12 h-12 rounded-full bg-surface text-secondary flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                    </svg>
                                </div>
                                <p class="text-sm text-secondary">No new notifications</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- User menu --}}
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" @click.outside="open = false"
                        class="flex items-center gap-2 -mr-1 p-1.5 text-secondary hover:text-primary rounded-xl hover:bg-surface transition-colors">
                    <div class="w-7 h-7 rounded-full bg-gradient-to-br from-accent/10 to-amber-100 text-accent flex items-center justify-center text-xs font-semibold border border-accent/10">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                    <svg class="hidden sm:block w-4 h-4" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                     class="absolute right-0 top-12 w-56 bg-white rounded-xl shadow-medium border border-border overflow-hidden z-50">
                    <div class="px-4 py-3 border-b border-border bg-surface/50">
                        <p class="text-sm font-semibold text-primary">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-secondary truncate">{{ auth()->user()->email }}</p>
                        @if($isRoleSwitched)
                            <div class="mt-2 space-y-1.5">
                                <div class="inline-flex items-center gap-1.5 px-2 py-0.5 bg-white border border-border rounded-full text-xs font-medium text-secondary">
                                    <span class="w-1.5 h-1.5 rounded-full bg-tertiary"></span>
                                    <span class="capitalize">{{ $role }}</span>
                                </div>
                                <div class="inline-flex items-center gap-1.5 px-2 py-0.5 bg-accent/10 border border-accent/20 rounded-full text-xs font-medium text-accent">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                    </svg>
                                    <span class="capitalize">{{ $effectiveRole }} View</span>
                                </div>
                            </div>
                        @else
                            <div class="mt-2 inline-flex items-center gap-1.5 px-2 py-0.5 bg-white border border-border rounded-full text-xs font-medium text-secondary">
                                <span class="w-1.5 h-1.5 rounded-full @if(in_array(auth()->user()->role, ['admin', 'supervisor', 'cosupervisor'])) bg-success @else bg-tertiary @endif"></span>
                                <span class="capitalize">{{ auth()->user()->role }}</span>
                            </div>
                        @endif
                    </div>
                    @if($isRoleSwitched)
                        <div class="py-1 border-b border-border">
                            <form method="POST" action="{{ route('admin.switch-role-reset') }}" class="block">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-sm text-accent hover:bg-accent/10 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    Exit Role View
                                </button>
                            </form>
                        </div>
                    @endif
                    <div class="py-1">
                        <a href="{{ route('admin.settings.users') }}" class="block px-4 py-2 text-sm text-secondary hover:text-primary hover:bg-surface transition-colors">
                            Your Profile
                        </a>
                    </div>
                    <div class="py-1 border-t border-border">
                        <form method="POST" action="{{ route('logout') }}" class="block">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-sm text-secondary hover:text-danger hover:bg-danger-light/50 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4 4m4-4H3m6 4h.01"/>
                                </svg>
                                Sign out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
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
            toggle() {
                this.open = !this.open;
            }
        }
    }
</script>
