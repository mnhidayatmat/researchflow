@php
    $role = auth()->user()->role;
    $effectiveRole = session()->get('admin_role_switch', $role);
    $isRoleSwitched = $role === 'admin' && $effectiveRole !== $role;
    $selectedStudentId = session()->get('admin_view_as_student_id');

    // Use effective role for navigation when switched
    $displayRole = $isRoleSwitched ? $effectiveRole : $role;
    $useDirectStudentLinks = $isRoleSwitched && in_array($displayRole, ['supervisor', 'cosupervisor'], true) && $selectedStudentId;

    $navGroups = [
        'admin' => [
            'main' => [
                ['route' => 'admin.dashboard', 'icon' => 'home', 'label' => 'Dashboard'],
                ['route' => 'admin.students.index', 'icon' => 'users', 'label' => 'Students'],
                ['route' => 'admin.programmes.index', 'icon' => 'folder', 'label' => 'Programmes'],
                ['route' => 'ai.chat', 'icon' => 'message-circle', 'label' => 'AI Assistant'],
                ['route' => 'timeline.index', 'icon' => 'gantt-chart', 'label' => 'Gantt Chart'],
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
                ['route' => 'supervisor.grants.index', 'icon' => 'briefcase', 'label' => 'Grant'],
                ['route' => 'supervisor.collaborators.index', 'icon' => 'user-plus', 'label' => 'Collaborators'],
                ['route' => 'ai.chat', 'icon' => 'message-circle', 'label' => 'AI Assistant'],
                ['route' => 'supervisor.storage.edit', 'icon' => 'hard-drive', 'label' => 'Storage'],
            ],
        ],
        'cosupervisor' => [
            'main' => [
                ['route' => 'supervisor.dashboard', 'icon' => 'home', 'label' => 'Dashboard'],
                ['route' => 'supervisor.students.index', 'icon' => 'users', 'label' => 'My Students'],
                ['route' => 'supervisor.grants.index', 'icon' => 'briefcase', 'label' => 'Grant'],
                ['route' => 'supervisor.collaborators.index', 'icon' => 'user-plus', 'label' => 'Collaborators'],
                ['route' => 'ai.chat', 'icon' => 'message-circle', 'label' => 'AI Assistant'],
                ['route' => 'supervisor.storage.edit', 'icon' => 'hard-drive', 'label' => 'Storage'],
            ],
        ],
        'student' => [
            'main' => [
                ['route' => 'student.dashboard', 'icon' => 'home', 'label' => 'Dashboard'],
                ['route' => 'ai.chat', 'icon' => 'message-circle', 'label' => 'AI Assistant'],
            ],
        ],
    ];

    $studentId = match($displayRole) {
        'student' => session()->has('admin_view_as_student_id')
            ? session()->get('admin_view_as_student_id')
            : auth()->user()->student?->id,
        'supervisor', 'cosupervisor' => $isRoleSwitched && $selectedStudentId
            ? $selectedStudentId
            : \App\Models\Student::where('supervisor_id', auth()->id())
                ->orWhere('cosupervisor_id', auth()->id())
                ->where('status', 'active')
                ->first()?->id,
        'admin' => \App\Models\Student::where('status', 'active')->first()?->id,
        default => null,
    };

    if ($displayRole === 'student' && $isRoleSwitched && !$selectedStudentId) {
        $navGroups['student']['main'][0]['route'] = 'admin.role-switch-select-student';
        $navGroups['student']['main'][0]['query'] = ['role' => 'student'];
    }

    if (in_array($displayRole, ['supervisor', 'cosupervisor'], true)) {
        array_splice($navGroups[$displayRole]['main'], 2, 0, [[
            'route' => 'supervisor.publications.index',
            'icon' => 'journal',
            'label' => 'Publications',
        ]]);
    }

    // Supervisor and co-supervisor navigation
    if (in_array($displayRole, ['supervisor', 'cosupervisor'])) {
        $navGroups[$displayRole]['supervision'] = [
            ['route' => $useDirectStudentLinks ? ['tasks.index', $studentId] : 'supervisor.students.index', 'query' => $useDirectStudentLinks ? [] : ['target' => 'tasks'], 'icon' => 'check-square', 'label' => 'Tasks'],
            ['route' => $useDirectStudentLinks ? ['reports.index', $studentId] : 'supervisor.students.index', 'query' => $useDirectStudentLinks ? [] : ['target' => 'reports'], 'icon' => 'file-text', 'label' => 'Reports'],
            ['route' => $useDirectStudentLinks ? ['meetings.index', $studentId] : 'supervisor.students.index', 'query' => $useDirectStudentLinks ? [] : ['target' => 'meetings'], 'icon' => 'calendar', 'label' => 'Meetings'],
        ];
    }

    if ($studentId && $displayRole === 'student') {
        $navGroups[$displayRole]['research'] = [
            ['route' => ['tasks.index', $studentId], 'icon' => 'check-square', 'label' => 'Tasks'],
            ['route' => ['reports.index', $studentId], 'icon' => 'file-text', 'label' => 'Reports'],
            ['route' => ['meetings.index', $studentId], 'icon' => 'calendar', 'label' => 'Meetings'],
            ['route' => ['publications.index', $studentId], 'icon' => 'journal', 'label' => 'Publications'],
            ['route' => ['literature.index', $studentId], 'icon' => 'table', 'label' => 'Lit. Matrix'],
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
        'gantt-chart' => 'M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2',
        'briefcase' => 'M9 6V4a3 3 0 013-3h0a3 3 0 013 3v2m-9 0h10a2 2 0 012 2v9a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2h0z',
        'journal' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
        'user-plus' => 'M18 9a3 3 0 100-6 3 3 0 000 6zm-8 11a4 4 0 014-4h4a4 4 0 014 4v1H10v-1zm-6-7h4m-2-2v4',
        'table' => 'M3 10h18M3 14h18M10 3v18M3 6a3 3 0 013-3h12a3 3 0 013 3v12a3 3 0 01-3 3H6a3 3 0 01-3-3V6z',
    ];
@endphp

<div class="flex h-full flex-col" x-cloak>
    {{-- Logo --}}
    <div class="flex items-center h-14 px-4 border-b border-border dark:border-dark-border shrink-0">
        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-accent to-amber-600 flex items-center justify-center shrink-0 shadow-sm">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
        </div>
        <span x-show="!sidebarCollapsed" x-transition.opacity.duration.200ms class="ml-2 truncate font-semibold text-sm text-primary dark:text-dark-primary">ResearchFlow</span>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 overflow-y-auto scrollbar-thin py-3">
        @foreach(($navGroups[$displayRole] ?? []) as $group => $items)
            @if(!empty($items))
                {{-- Group header --}}
                @if($group !== 'main')
                    <div x-show="!sidebarCollapsed" x-transition.opacity class="px-4 pt-4 pb-2">
                        <span class="text-[10px] font-semibold uppercase tracking-wider text-tertiary dark:text-dark-tertiary">{{ $group }}</span>
                    </div>
                @endif

                <div class="space-y-0.5 px-2">
                    @foreach($items as $item)
                        @php
                            $itemRoute = is_array($item['route'])
                                ? route($item['route'][0], $item['route'][1] ?? [])
                                : route($item['route'], $item['query'] ?? []);
                            $routeName = is_array($item['route']) ? $item['route'][0] : $item['route'];
                            $targetQuery = $item['query']['target'] ?? null;
                            $isStudentsRoute = $routeName === 'supervisor.students.index';
                            $currentTarget = request('target');

                            if ($isStudentsRoute && $targetQuery !== null) {
                                $isActive = request()->routeIs('supervisor.students.index') && $currentTarget === $targetQuery;
                            } elseif ($isStudentsRoute) {
                                $isActive = request()->routeIs('supervisor.students.index', 'supervisor.students.show') && $currentTarget === null;
                            } elseif ($routeName === 'supervisor.grants.index') {
                                $isActive = request()->routeIs('supervisor.grants.*');
                            } elseif ($routeName === 'supervisor.publications.index') {
                                $isActive = request()->routeIs('supervisor.publications.*');
                            } elseif ($routeName === 'supervisor.collaborators.index') {
                                $isActive = request()->routeIs('supervisor.collaborators.*');
                            } elseif ($routeName === 'literature.index') {
                                $isActive = request()->routeIs('literature.*');
                            } else {
                                $isActive = request()->routeIs($routeName . '*');
                            }
                        @endphp

                        <a href="{{ $itemRoute }}" @click="if (window.innerWidth < 1024) { $dispatch('close-mobile-sidebar'); }"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200
                                  {{ $isActive ? 'bg-accent/10 text-accent dark:bg-accent/20 dark:text-dark-accent' : 'text-secondary dark:text-dark-secondary hover:text-primary dark:hover:text-dark-primary hover:bg-surface/50 dark:hover:bg-dark-surface/50' }}">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $icons[$item['icon']] ?? '' }}"/>
                            </svg>
                            <span x-show="!sidebarCollapsed" x-transition.opacity.duration.200ms class="text-sm font-medium">{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        @endforeach
    </nav>

    {{-- Collapse Toggle --}}
    <div class="p-3 border-t border-border dark:border-dark-border">
        <button @click="sidebarCollapsed = !sidebarCollapsed; localStorage.setItem('sidebarCollapsed', sidebarCollapsed)"
                class="flex items-center justify-center w-full p-2 rounded-xl text-secondary dark:text-dark-secondary hover:text-primary dark:hover:text-dark-primary hover:bg-surface/50 dark:hover:bg-dark-surface/50 transition-all">
            <svg class="w-5 h-5 transition-transform duration-200" :class="{ 'rotate-180': sidebarCollapsed }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
            </svg>
        </button>
    </div>
</div>
