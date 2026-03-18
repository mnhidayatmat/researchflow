@php
    $role = auth()->user()->role;
    $effectiveRole = session()->get('admin_role_switch', $role);
    $isRoleSwitched = $role === 'admin' && $effectiveRole !== $role;

    // Use effective role for navigation when switched
    $displayRole = $isRoleSwitched ? $effectiveRole : $role;

    $navGroups = [
        'admin' => [
            'main' => [
                ['route' => 'admin.dashboard', 'icon' => 'home', 'label' => 'Dashboard'],
                ['route' => 'admin.students.index', 'icon' => 'users', 'label' => 'Students'],
                ['route' => 'admin.programmes.index', 'icon' => 'folder', 'label' => 'Programmes'],
                ['route' => 'admin.templates.index', 'icon' => 'map', 'label' => 'Journey Templates'],
            ],
            'settings' => [
                ['route' => 'admin.settings.storage', 'icon' => 'hard-drive', 'label' => 'Storage'],
                ['route' => 'admin.settings.ai', 'icon' => 'cpu', 'label' => 'AI Providers'],
                ['route' => 'admin.settings.users', 'icon' => 'shield', 'label' => 'Users'],
            ],
        ],
        'supervisor' => [
            'main' => [
                ['route' => 'supervisor.dashboard', 'icon' => 'home', 'label' => 'Dashboard'],
                ['route' => 'supervisor.students.index', 'icon' => 'users', 'label' => 'My Students'],
            ],
        ],
        'cosupervisor' => [
            'main' => [
                ['route' => 'supervisor.dashboard', 'icon' => 'home', 'label' => 'Dashboard'],
                ['route' => 'supervisor.students.index', 'icon' => 'users', 'label' => 'My Students'],
            ],
        ],
        'student' => [
            'main' => [
                ['route' => 'student.dashboard', 'icon' => 'home', 'label' => 'Dashboard'],
            ],
        ],
    ];

    $studentId = match($displayRole) {
        'student' => auth()->user()->student?->id,
        default => \App\Models\Student::where('supervisor_id', auth()->id())
            ->orWhere('cosupervisor_id', auth()->id())
            ->where('status', 'active')
            ->first()?->id,
    };

    if ($studentId && in_array($displayRole, ['supervisor', 'cosupervisor'])) {
        $navGroups[$displayRole]['supervision'] = [
            ['route' => ['tasks.index', $studentId], 'icon' => 'check-square', 'label' => 'Tasks'],
            ['route' => ['tasks.kanban', $studentId], 'icon' => 'columns', 'label' => 'Kanban'],
            ['route' => ['reports.index', $studentId], 'icon' => 'file-text', 'label' => 'Reports'],
            ['route' => ['meetings.index', $studentId], 'icon' => 'calendar', 'label' => 'Meetings'],
        ];
    }

    if ($studentId && $displayRole === 'student') {
        $navGroups[$displayRole]['research'] = [
            ['route' => ['tasks.index', $studentId], 'icon' => 'check-square', 'label' => 'Tasks'],
            ['route' => ['tasks.kanban', $studentId], 'icon' => 'columns', 'label' => 'Kanban'],
            ['route' => ['tasks.gantt', $studentId], 'icon' => 'bar-chart', 'label' => 'Timeline'],
            ['route' => ['reports.index', $studentId], 'icon' => 'file-text', 'label' => 'Reports'],
            ['route' => ['meetings.index', $studentId], 'icon' => 'calendar', 'label' => 'Meetings'],
            ['route' => ['files.index', $studentId], 'icon' => 'archive', 'label' => 'Vault'],
        ];
    }

    $icons = [
        'home' => 'M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2V9z',
        'users' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
        'folder' => 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z',
        'map' => 'M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7',
        'hard-drive' => 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4',
        'cpu' => 'M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z',
        'shield' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
        'check-square' => 'M9 11l3 3L22 4m1 12a9 9 0 11-18 0 9 9 0 0118 0z',
        'columns' => 'M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2',
        'file-text' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        'calendar' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
        'bar-chart' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
        'archive' => 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4',
        'message-circle' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
    ];
@endphp

<div class="flex flex-col h-full" x-data="{
    collapsed: localStorage.getItem('sidebarCollapsed') === 'true'
}" x-cloak>
    {{-- Logo --}}
    <div class="flex items-center h-14 px-4 border-b border-border shrink-0">
        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-accent to-amber-600 flex items-center justify-center shrink-0 shadow-sm">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
        </div>
        <span x-show="!collapsed" x-transition.opacity.duration.200ms class="ml-2 font-semibold text-sm text-primary">ResearchFlow</span>
    </div>

    {{-- Role Switcher Banner (shown when admin has switched roles) --}}
    @if($isRoleSwitched)
        <div class="mx-3 mt-3 p-3 bg-gradient-to-r from-accent/10 to-amber-500/5 border border-accent/20 rounded-lg">
            <form method="POST" action="{{ route('admin.switch-role-reset') }}">
                @csrf
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full bg-accent animate-pulse"></div>
                    <span class="text-xs font-medium text-accent">Viewing as {{ ucfirst($effectiveRole) }}</span>
                    <button type="submit" class="ml-auto text-xs text-accent hover:text-amber-700 underline">
                        Exit
                    </button>
                </div>
            </form>
        </div>
    @endif

    {{-- Role Switcher (Admin only, shown when not switched) --}}
    @if($role === 'admin' && !$isRoleSwitched)
        <div class="mx-3 mt-3">
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" @click.outside="open = false"
                        class="w-full flex items-center gap-2 px-3 py-2 text-xs font-medium text-secondary hover:text-primary hover:bg-surface/50 rounded-lg transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <span>Switch View</span>
                    <svg class="w-3 h-3 ml-auto transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open" x-transition
                     class="absolute left-0 right-0 mt-1 bg-card border border-border rounded-lg shadow-lg z-50 overflow-hidden">
                    @foreach(['student', 'supervisor', 'cosupervisor'] as $switchRole)
                        <form method="POST" action="{{ route('admin.switch-role') }}" class="border-b border-border last:border-0">
                            @csrf
                            <input type="hidden" name="role" value="{{ $switchRole }}">
                            <button type="submit"
                                    class="w-full text-left px-3 py-2 text-xs text-secondary hover:bg-surface hover:text-primary transition-colors flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full @if($switchRole === 'student') bg-blue-500 @elseif($switchRole === 'supervisor') bg-emerald-500 @else bg-purple-500 @endif"></span>
                                View as {{ ucfirst($switchRole) }}
                            </button>
                        </form>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- Navigation --}}
    <nav class="flex-1 overflow-y-auto scrollbar-thin py-3">
        @foreach(($navGroups[$displayRole] ?? []) as $group => $items)
            @if(!empty($items))
                {{-- Group header --}}
                @if($group !== 'main')
                    <div x-show="!collapsed" x-transition.opacity class="px-4 pt-4 pb-2">
                        <span class="text-[10px] font-semibold uppercase tracking-wider text-tertiary">{{ $group }}</span>
                    </div>
                @endif

                <div class="space-y-0.5 px-2">
                    @foreach($items as $item)
                        @php
                            $itemRoute = is_array($item['route']) ? route($item['route'][0], $item['route'][1] ?? []) : route($item['route']);
                            $isActive = is_array($item['route'])
                                ? request()->routeIs($item['route'][0] . '*')
                                : request()->routeIs($item['route'] . '*');
                        @endphp

                        <a href="{{ $itemRoute }}"
                           class="group flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-all duration-150 relative
                                  {{ $isActive
                                      ? 'bg-accent/10 text-accent font-medium shadow-sm'
                                      : 'text-secondary hover:bg-surface hover:text-primary' }}">
                            {{-- Icon --}}
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icons[$item['icon']] ?? '' }}"/>
                            </svg>

                            {{-- Label --}}
                            <span x-show="!collapsed" x-transition.opacity.duration.150ms class="truncate flex-1">{{ $item['label'] }}</span>

                            {{-- Tooltip for collapsed state --}}
                            <div x-show="collapsed" x-transition.opacity.duration.150ms
                                 class="absolute left-full ml-2 px-2.5 py-1 bg-primary text-white text-xs rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity z-50 shadow-lg">
                                {{ $item['label'] }}
                                <div class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-1 w-2 h-2 bg-primary rotate-45"></div>
                            </div>

                            {{-- Active indicator --}}
                            @if($isActive)
                                <span x-show="!collapsed" x-transition.duration.200ms class="ml-auto w-1.5 h-1.5 rounded-full bg-accent"></span>
                            @endif
                        </a>
                    @endforeach
                </div>
            @endif
        @endforeach

        {{-- AI Assistant section --}}
        <div x-show="!collapsed" x-transition.opacity.duration.200ms class="px-4 pt-4 pb-2">
            <span class="text-[10px] font-semibold uppercase tracking-wider text-tertiary flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
                AI Assistant
            </span>
        </div>
        <div class="px-2">
            <a href="{{ route('ai.chat') }}"
               class="group flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-all duration-150 relative
                      {{ request()->routeIs('ai.*')
                          ? 'bg-gradient-to-r from-accent/10 to-accent/5 text-accent font-medium shadow-sm'
                          : 'text-secondary hover:bg-surface hover:text-primary' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icons['message-circle'] }}"/>
                </svg>
                <span x-show="!collapsed" x-transition.opacity.duration.150ms class="truncate flex-1">AI Chat</span>

                {{-- Tooltip for collapsed state --}}
                <div x-show="collapsed" x-transition.opacity.duration.150ms
                     class="absolute left-full ml-2 px-2.5 py-1 bg-primary text-white text-xs rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity z-50 shadow-lg">
                    AI Chat
                    <div class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-1 w-2 h-2 bg-primary rotate-45"></div>
                </div>

                @if(request()->routeIs('ai.*'))
                    <span x-show="!collapsed" x-transition.duration.200ms class="ml-auto w-1.5 h-1.5 rounded-full bg-accent animate-pulse"></span>
                @endif
            </a>
        </div>
    </nav>

    {{-- Collapse toggle (desktop only) --}}
    <button @click="collapsed = !collapsed; localStorage.setItem('sidebarCollapsed', collapsed); $dispatch('sidebar-toggled', { collapsed })"
            class="hidden lg:flex items-center justify-center h-12 border-t border-border text-tertiary hover:text-primary hover:bg-surface/50 transition-all duration-200 group">
        <svg class="w-4 h-4 transition-transform duration-300 ease-out" :class="{ 'rotate-180': collapsed }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
        </svg>
    </button>
</div>
