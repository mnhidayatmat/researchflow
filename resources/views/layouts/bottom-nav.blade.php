@php
    $role = auth()->user()->role;
    $effectiveRole = session()->get('admin_role_switch', $role);
    $isRoleSwitched = $role === 'admin' && $effectiveRole !== $role;
    $displayRole = $isRoleSwitched ? $effectiveRole : $role;

    $studentId = match($displayRole) {
        'student' => session()->has('admin_view_as_student_id')
            ? session()->get('admin_view_as_student_id')
            : auth()->user()->student?->id,
        'supervisor', 'cosupervisor' => $isRoleSwitched && session()->get('admin_view_as_student_id')
            ? session()->get('admin_view_as_student_id')
            : \App\Models\Student::where('supervisor_id', auth()->id())
                ->orWhere('cosupervisor_id', auth()->id())
                ->where('status', 'active')
                ->first()?->id,
        'admin' => \App\Models\Student::where('status', 'active')->first()?->id,
        default => null,
    };

    // Bottom nav items per role (max 5)
    $bottomNavItems = match($displayRole) {
        'admin' => [
            [
                'route' => 'admin.dashboard',
                'label' => 'Home',
                'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
                'match' => 'admin.dashboard',
            ],
            [
                'route' => 'admin.students.index',
                'label' => 'Students',
                'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
                'match' => 'admin.students.*',
            ],
            [
                'route' => 'admin.programmes.index',
                'label' => 'Programmes',
                'icon' => 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z',
                'match' => 'admin.programmes.*',
            ],
            [
                'route' => 'ai.chat',
                'label' => 'AI Chat',
                'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
                'match' => 'ai.chat*',
            ],
            [
                'type' => 'more',
                'label' => 'More',
                'icon' => 'M4 6h16M4 12h16M4 18h16',
            ],
        ],
        'supervisor', 'cosupervisor' => [
            [
                'route' => 'supervisor.dashboard',
                'label' => 'Home',
                'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
                'match' => 'supervisor.dashboard',
            ],
            [
                'route' => 'supervisor.students.index',
                'label' => 'Students',
                'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
                'match' => 'supervisor.students.*',
            ],
            [
                'route' => 'ai.chat',
                'label' => 'AI Chat',
                'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
                'match' => 'ai.chat*',
            ],
            [
                'route' => 'supervisor.grants.index',
                'label' => 'Grants',
                'icon' => 'M9 6V4a3 3 0 013-3h0a3 3 0 013 3v2m-9 0h10a2 2 0 012 2v9a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2h0z',
                'match' => 'supervisor.grants.*',
            ],
            [
                'type' => 'more',
                'label' => 'More',
                'icon' => 'M4 6h16M4 12h16M4 18h16',
            ],
        ],
        'student' => [
            [
                'route' => 'student.dashboard',
                'label' => 'Home',
                'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
                'match' => 'student.dashboard',
            ],
            [
                'route' => $studentId ? ['tasks.index', $studentId] : 'student.dashboard',
                'label' => 'Tasks',
                'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
                'match' => 'tasks.*',
            ],
            [
                'route' => $studentId ? ['reports.index', $studentId] : 'student.dashboard',
                'label' => 'Reports',
                'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                'match' => 'reports.*',
            ],
            [
                'route' => $studentId ? ['meetings.index', $studentId] : 'student.dashboard',
                'label' => 'Meetings',
                'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
                'match' => 'meetings.*',
            ],
            [
                'route' => 'ai.chat',
                'label' => 'AI Chat',
                'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
                'match' => 'ai.chat*',
            ],
        ],
        default => [],
    };
@endphp

{{-- Mobile Bottom Navigation Bar --}}
<nav class="fixed bottom-0 inset-x-0 z-50 lg:hidden" x-data="{ moreOpen: false }">
    {{-- More menu overlay --}}
    <div x-show="moreOpen" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="moreOpen = false"
         class="fixed inset-0 bg-primary/20 dark:bg-black/40 backdrop-blur-sm z-40"></div>

    {{-- More menu sheet --}}
    <div x-show="moreOpen" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="translate-y-full opacity-0"
         x-transition:enter-end="translate-y-0 opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="translate-y-0 opacity-100"
         x-transition:leave-end="translate-y-full opacity-0"
         class="fixed bottom-0 inset-x-0 z-50 bg-white dark:bg-dark-card rounded-t-3xl shadow-2xl border-t border-border dark:border-dark-border"
         style="padding-bottom: env(safe-area-inset-bottom, 0px);">
        {{-- Handle bar --}}
        <div class="flex justify-center pt-3 pb-1">
            <div class="w-10 h-1 rounded-full bg-border dark:bg-dark-border"></div>
        </div>

        {{-- Sheet header --}}
        <div class="px-6 py-3 border-b border-border dark:border-dark-border">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold text-primary dark:text-dark-primary">Menu</h3>
                <button @click="moreOpen = false" class="p-1.5 rounded-lg text-secondary hover:text-primary hover:bg-surface dark:hover:bg-dark-surface transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Menu items --}}
        <div class="px-4 py-3 max-h-[60vh] overflow-y-auto">
            @php
                $moreIcons = [
                    'home' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
                    'users' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
                    'folder' => 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z',
                    'hard-drive' => 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4',
                    'cpu' => 'M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z',
                    'shield' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                    'message-circle' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
                    'check-square' => 'M9 11l3 3L22 4m1 12a9 9 0 11-18 0 9 9 0 0118 0z',
                    'file-text' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                    'calendar' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
                    'archive' => 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4',
                    'gantt-chart' => 'M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2',
                    'briefcase' => 'M9 6V4a3 3 0 013-3h0a3 3 0 013 3v2m-9 0h10a2 2 0 012 2v9a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2h0z',
                    'journal' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
                    'user-plus' => 'M18 9a3 3 0 100-6 3 3 0 000 6zm-8 11a4 4 0 014-4h4a4 4 0 014 4v1H10v-1zm-6-7h4m-2-2v4',
                    'settings' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
                    'logout' => 'M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1',
                ];

                // Build the "more" menu items based on role - these are the items NOT in the bottom nav
                $moreMenuItems = match($displayRole) {
                    'admin' => [
                        ['label' => 'Navigation', 'type' => 'header'],
                        ['route' => 'timeline.index', 'label' => 'Gantt Chart', 'icon' => 'gantt-chart'],
                        ['label' => 'Settings', 'type' => 'header'],
                        ['route' => 'admin.settings.storage', 'label' => 'Storage', 'icon' => 'hard-drive'],
                        ['route' => 'admin.settings.ai', 'label' => 'AI Providers', 'icon' => 'cpu'],
                        ['route' => 'admin.settings.users', 'label' => 'Users & Profile', 'icon' => 'shield'],
                    ],
                    'supervisor', 'cosupervisor' => [
                        ['label' => 'Navigation', 'type' => 'header'],
                        ['route' => 'supervisor.publications.index', 'label' => 'Publications', 'icon' => 'journal'],
                        ['route' => 'supervisor.collaborators.index', 'label' => 'Collaborators', 'icon' => 'user-plus'],
                        ['route' => 'supervisor.storage.edit', 'label' => 'Storage', 'icon' => 'hard-drive'],
                        ['label' => 'Supervision', 'type' => 'header'],
                        ['route' => 'supervisor.students.index', 'query' => ['target' => 'tasks'], 'label' => 'Tasks', 'icon' => 'check-square'],
                        ['route' => 'supervisor.students.index', 'query' => ['target' => 'reports'], 'label' => 'Reports', 'icon' => 'file-text'],
                        ['route' => 'supervisor.students.index', 'query' => ['target' => 'meetings'], 'label' => 'Meetings', 'icon' => 'calendar'],
                    ],
                    'student' => [
                        ['label' => 'Research', 'type' => 'header'],
                        ['route' => $studentId ? ['publications.index', $studentId] : 'student.dashboard', 'label' => 'Publications', 'icon' => 'journal'],
                        ['route' => $studentId ? ['files.index', $studentId] : 'student.dashboard', 'label' => 'Vault', 'icon' => 'archive'],
                    ],
                    default => [],
                };
            @endphp

            @foreach($moreMenuItems as $menuItem)
                @if(($menuItem['type'] ?? null) === 'header')
                    <div class="px-2 pt-3 pb-1.5 first:pt-0">
                        <span class="text-[10px] font-semibold uppercase tracking-wider text-tertiary dark:text-dark-tertiary">{{ $menuItem['label'] }}</span>
                    </div>
                @else
                    @php
                        $menuRoute = is_array($menuItem['route'] ?? null)
                            ? route($menuItem['route'][0], $menuItem['route'][1] ?? [])
                            : route($menuItem['route'], $menuItem['query'] ?? []);
                        $menuRouteName = is_array($menuItem['route'] ?? null) ? $menuItem['route'][0] : $menuItem['route'];
                        $menuIsActive = request()->routeIs($menuRouteName . '*');
                    @endphp
                    <a href="{{ $menuRoute }}"
                       @click="moreOpen = false"
                       class="flex items-center gap-3 px-3 py-3 rounded-xl transition-all
                              {{ $menuIsActive ? 'bg-accent/10 text-accent dark:text-dark-accent' : 'text-secondary dark:text-dark-secondary hover:text-primary dark:hover:text-dark-primary hover:bg-surface dark:hover:bg-dark-surface' }}">
                        <div class="w-10 h-10 rounded-xl {{ $menuIsActive ? 'bg-accent/15' : 'bg-surface dark:bg-dark-surface' }} flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 {{ $menuIsActive ? 'text-accent' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $moreIcons[$menuItem['icon']] ?? '' }}"/>
                            </svg>
                        </div>
                        <span class="text-sm font-medium">{{ $menuItem['label'] }}</span>
                        <svg class="w-4 h-4 text-tertiary dark:text-dark-tertiary ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                @endif
            @endforeach

            {{-- User section --}}
            <div class="mt-3 pt-3 border-t border-border dark:border-dark-border">
                <div class="flex items-center gap-3 px-3 py-2">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-accent/10 to-amber-100 dark:from-dark-accent/20 dark:to-dark-accent/10 text-accent dark:text-dark-accent flex items-center justify-center text-sm font-semibold border border-accent/10 dark:border-dark-accent/20">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-primary dark:text-dark-primary truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-secondary dark:text-dark-secondary truncate">{{ auth()->user()->email }}</p>
                    </div>
                </div>

                {{-- Theme Toggle --}}
                <button x-data @click="
                    const html = document.documentElement;
                    const isDark = html.classList.contains('dark');
                    if (isDark) { html.classList.remove('dark'); localStorage.setItem('theme', 'light'); }
                    else { html.classList.add('dark'); localStorage.setItem('theme', 'dark'); }
                    axios.post('/settings/theme', { theme: isDark ? 'light' : 'dark' }).catch(() => {});
                " class="w-full flex items-center gap-3 px-3 py-3 rounded-xl text-secondary dark:text-dark-secondary hover:text-primary dark:hover:text-dark-primary hover:bg-surface dark:hover:bg-dark-surface transition-colors mt-1">
                    <div class="w-10 h-10 rounded-xl bg-surface dark:bg-dark-surface flex items-center justify-center shrink-0">
                        {{-- Sun (shown in dark mode) --}}
                        <svg class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        {{-- Moon (shown in light mode) --}}
                        <svg class="w-5 h-5 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                        </svg>
                    </div>
                    <span class="text-sm font-medium">
                        <span class="dark:hidden">Dark Mode</span>
                        <span class="hidden dark:inline">Light Mode</span>
                    </span>
                </button>

                <form method="POST" action="{{ route('logout') }}" class="mt-1">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-3 py-3 rounded-xl text-secondary dark:text-dark-secondary hover:text-danger dark:hover:text-dark-danger hover:bg-danger-light/50 dark:hover:bg-dark-danger-light/30 transition-colors">
                        <div class="w-10 h-10 rounded-xl bg-surface dark:bg-dark-surface flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $moreIcons['logout'] }}"/>
                            </svg>
                        </div>
                        <span class="text-sm font-medium">Sign Out</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Bottom Tab Bar --}}
    <div class="relative bg-white/90 dark:bg-dark-card/90 backdrop-blur-xl border-t border-border/80 dark:border-dark-border/80 shadow-[0_-1px_3px_rgba(0,0,0,0.05)]"
         style="padding-bottom: env(safe-area-inset-bottom, 0px);">
        <div class="flex items-stretch justify-around px-2">
            @foreach($bottomNavItems as $item)
                @if(($item['type'] ?? null) === 'more')
                    {{-- More button --}}
                    <button @click="moreOpen = !moreOpen"
                            class="flex flex-col items-center justify-center gap-0.5 py-2 px-1 min-w-[56px] max-w-[80px] flex-1 group relative"
                            :class="moreOpen ? 'text-accent dark:text-dark-accent' : 'text-secondary dark:text-dark-secondary'">
                        <div class="relative flex items-center justify-center w-7 h-7">
                            <svg class="w-[22px] h-[22px] transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/>
                            </svg>
                        </div>
                        <span class="text-[10px] font-medium leading-tight transition-colors duration-200"
                              :class="moreOpen ? 'text-accent dark:text-dark-accent' : ''">{{ $item['label'] }}</span>
                    </button>
                @else
                    @php
                        $navRoute = is_array($item['route'])
                            ? route($item['route'][0], $item['route'][1] ?? [])
                            : route($item['route']);
                        $isActive = request()->routeIs($item['match']);
                    @endphp
                    <a href="{{ $navRoute }}"
                       class="flex flex-col items-center justify-center gap-0.5 py-2 px-1 min-w-[56px] max-w-[80px] flex-1 group relative
                              {{ $isActive ? 'text-accent dark:text-dark-accent' : 'text-secondary dark:text-dark-secondary active:text-primary dark:active:text-dark-primary' }}">
                        {{-- Active indicator pill --}}
                        @if($isActive)
                        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-12 h-[3px] rounded-b-full bg-accent dark:bg-dark-accent"></div>
                        @endif

                        <div class="relative flex items-center justify-center w-7 h-7 {{ $isActive ? 'scale-105' : 'group-active:scale-95' }} transition-transform duration-150">
                            <svg class="w-[22px] h-[22px] transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                 stroke-width="{{ $isActive ? '2' : '1.8' }}">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/>
                            </svg>
                        </div>
                        <span class="text-[10px] font-medium leading-tight transition-colors duration-200
                                     {{ $isActive ? 'text-accent dark:text-dark-accent' : '' }}">{{ $item['label'] }}</span>
                    </a>
                @endif
            @endforeach
        </div>
    </div>
</nav>
