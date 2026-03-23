@php
    $role = auth()->user()->role;
    $effectiveRole = session()->get('admin_role_switch', $role);
    $isRoleSwitched = $role === 'admin' && $effectiveRole !== $role;
    $items = collect($items ?? []);
@endphp

{{-- Enhanced topbar with improved visual hierarchy --}}
<div class="sticky top-0 z-30 bg-white/80 dark:bg-dark-card/80 backdrop-blur-md border-b border-border dark:border-dark-border @if($isRoleSwitched) border-t-4 border-t-accent @endif">
    <div class="flex items-center justify-between h-14 px-4 sm:px-6 lg:px-8">
        {{-- Left side --}}
        <div class="flex items-center gap-4">
            <button @click="$parent.sidebarOpen = true" class="lg:hidden -ml-2 p-2 text-secondary hover:text-primary dark:text-dark-secondary dark:hover:text-dark-primary rounded-lg hover:bg-surface dark:hover:bg-dark-surface transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            @if(isset($header))
                <nav class="hidden sm:flex items-center gap-2 text-sm">
                    <span class="text-primary dark:text-dark-primary font-medium">{{ $header }}</span>
                </nav>
            @endif

            {{-- Role switch indicator in header --}}
            @if($isRoleSwitched)
                <form method="POST" action="{{ route('admin.switch-role-reset') }}" class="hidden sm:flex items-center gap-2 px-3 py-1.5 bg-accent/10 dark:bg-dark-accent/15 border border-accent/20 dark:border-dark-accent/30 rounded-lg">
                    @csrf
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-accent dark:bg-dark-accent animate-pulse"></span>
                        <span class="text-xs font-medium text-accent dark:text-dark-accent">Viewing as {{ ucfirst($effectiveRole) }}</span>
                        <button type="submit" class="text-xs text-accent dark:text-dark-accent hover:text-amber-700 dark:hover:text-amber-400 font-medium ml-1">
                            &times;
                        </button>
                    </div>
                </form>
            @endif
        </div>

        {{-- Right side --}}
        <div class="flex items-center gap-1 sm:gap-2">
            {{-- Role Switcher (Admin only) --}}
            @if(auth()->check() && auth()->user()->role === 'admin')
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

            {{-- Divider --}}
            <div class="hidden sm:block w-px h-6 bg-border dark:bg-dark-border"></div>

            {{-- Search --}}
            <div x-data="{ open: false }" class="hidden md:block">
                <button @click="open = !open" class="p-2 text-secondary hover:text-primary dark:text-dark-secondary dark:hover:text-dark-primary rounded-lg hover:bg-surface dark:hover:bg-dark-surface transition-colors">
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
                     class="absolute right-8 top-14 w-80 bg-white dark:bg-dark-card rounded-xl shadow-medium dark:shadow-dark-medium border border-border dark:border-dark-border p-2 z-50">
                    <input type="text" placeholder="Search anything..." class="w-full px-3 py-2 text-sm border-0 bg-surface dark:bg-dark-surface text-primary dark:text-dark-primary rounded-lg focus:outline-none focus:ring-2 focus:ring-accent/20 dark:focus:ring-dark-accent/20">
                    <div class="mt-2 px-2 text-xs text-tertiary dark:text-dark-tertiary flex items-center justify-between">
                        <span>Use</span>
                        <kbd class="px-1.5 py-0.5 text-xs bg-border dark:bg-dark-border rounded font-mono">⌘K</kbd>
                    </div>
                </div>
            </div>

            {{-- Divider --}}
            <div class="hidden sm:block w-px h-6 bg-border dark:bg-dark-border"></div>

            {{-- Notifications --}}
            <div x-data="notifications()" class="relative">
                <button @click="toggle()" class="relative p-2 text-secondary hover:text-primary dark:text-dark-secondary dark:hover:text-dark-primary rounded-lg hover:bg-surface dark:hover:bg-dark-surface transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <span x-show="unreadCount > 0" x-cloak
                          class="absolute top-1.5 right-1.5 w-2 h-2 bg-accent rounded-full ring-2 ring-white dark:ring-dark-bg"></span>
                </button>

                <div x-show="open" @click.away="open = false" x-cloak
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute right-0 top-12 w-80 bg-white dark:bg-dark-card rounded-xl shadow-medium dark:shadow-dark-medium border border-border dark:border-dark-border overflow-hidden z-50">
                    <div class="px-4 py-3 border-b border-border dark:border-dark-border">
                        <h3 class="text-sm font-semibold text-primary dark:text-dark-primary">Notifications</h3>
                    </div>
                    <div class="max-h-80 overflow-y-auto scrollbar-thin">
                        @if($items && $items->isNotEmpty())
                            @foreach($items as $item)
                                <div class="px-4 py-3 border-b border-border dark:border-dark-border last:border-0 hover:bg-surface dark:hover:bg-dark-surface cursor-pointer transition-colors">
                                    <p class="text-sm font-medium text-primary dark:text-dark-primary">{{ $item['title'] ?? '' }}</p>
                                    <p class="text-xs text-secondary dark:text-dark-secondary mt-0.5">{{ $item['body'] ?? '' }}</p>
                                </div>
                            @endforeach
                        @else
                            <div class="px-4 py-8 text-center">
                                <div class="w-12 h-12 rounded-full bg-surface dark:bg-dark-surface text-secondary dark:text-dark-secondary flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                    </svg>
                                </div>
                                <p class="text-sm text-secondary dark:text-dark-secondary">No new notifications</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Theme Toggle --}}
            <button x-data="themeManager()" x-init="initTheme()" @click="toggleTheme()"
                    class="p-2 text-secondary hover:text-primary dark:text-dark-secondary dark:hover:text-dark-primary rounded-lg hover:bg-surface dark:hover:bg-dark-surface transition-colors"
                    title="Toggle theme">
                <!-- Sun icon (show in dark mode) -->
                <svg x-show="theme === 'dark'" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <!-- Moon icon (show in light mode) -->
                <svg x-show="theme === 'light'" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
            </button>

            {{-- User menu --}}
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" @click.outside="open = false"
                        class="flex items-center gap-2 -mr-1 p-1.5 text-secondary hover:text-primary dark:text-dark-secondary dark:hover:text-dark-primary rounded-xl hover:bg-surface dark:hover:bg-dark-surface transition-colors">
                    <div class="w-7 h-7 rounded-full bg-gradient-to-br from-accent/10 to-amber-100 dark:from-dark-accent/20 dark:to-dark-accent/10 text-accent dark:text-dark-accent flex items-center justify-center text-xs font-semibold border border-accent/10 dark:border-dark-accent/20">
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
                     class="absolute right-0 top-12 w-56 bg-white dark:bg-dark-card rounded-xl shadow-medium dark:shadow-dark-medium border border-border dark:border-dark-border overflow-hidden z-50">
                    <div class="px-4 py-3 border-b border-border dark:border-dark-border bg-surface/50 dark:bg-dark-surface/50">
                        <p class="text-sm font-semibold text-primary dark:text-dark-primary">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-secondary dark:text-dark-secondary truncate">{{ auth()->user()->email }}</p>
                        @if($isRoleSwitched)
                            <div class="mt-2 space-y-1.5">
                                <div class="inline-flex items-center gap-1.5 px-2 py-0.5 bg-white dark:bg-dark-card border border-border dark:border-dark-border rounded-full text-xs font-medium text-secondary dark:text-dark-secondary">
                                    <span class="w-1.5 h-1.5 rounded-full bg-tertiary dark:bg-dark-tertiary"></span>
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
                            <div class="mt-2 inline-flex items-center gap-1.5 px-2 py-0.5 bg-white dark:bg-dark-card border border-border dark:border-dark-border rounded-full text-xs font-medium text-secondary dark:text-dark-secondary">
                                <span class="w-1.5 h-1.5 rounded-full @if(in_array(auth()->user()->role, ['admin', 'supervisor', 'cosupervisor'])) bg-success dark:bg-dark-success @else bg-tertiary dark:bg-dark-tertiary @endif"></span>
                                <span class="capitalize">{{ auth()->user()->role }}</span>
                            </div>
                        @endif
                    </div>
                    @if($isRoleSwitched)
                        <div class="py-1 border-b border-border dark:border-dark-border">
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
                        <a href="{{ route('admin.settings.users') }}" class="block px-4 py-2 text-sm text-secondary dark:text-dark-secondary hover:text-primary dark:hover:text-dark-primary hover:bg-surface dark:hover:bg-dark-surface transition-colors">
                            Your Profile
                        </a>
                    </div>
                    <div class="py-1 border-t border-border dark:border-dark-border">
                        <form method="POST" action="{{ route('logout') }}" class="block">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-sm text-secondary dark:text-dark-secondary hover:text-danger dark:hover:text-dark-danger hover:bg-danger-light/50 dark:hover:bg-dark-danger-light/50 transition-colors">
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
