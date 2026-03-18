{{-- Student Workspace Template --}}
<x-layouts.app title="My Workspace" :header="'My Workspace'">
    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-xs text-secondary mb-6">
        <a href="{{ route('student.dashboard') }}" class="hover:text-primary">Dashboard</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-primary">Workspace</span>
    </div>

    {{-- Tabs --}}
    <x-tabs :tabs="[
        'overview' => 'Overview',
        'tasks' => ['label' => 'Tasks', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
        'timeline' => ['label' => 'Timeline', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        'reports' => ['label' => 'Reports', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
        'meetings' => ['label' => 'Meetings', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
        'files' => ['label' => 'Vault', 'icon' => 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4'],
    ]" :active="'overview'" variant="pills" />

    {{-- Overview content --}}
    <div class="mt-6 grid lg:grid-cols-3 gap-6">
        {{-- Main area --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Current progress --}}
            <x-card title="Research Progress" :padding="'loose'">
                <div class="mb-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-primary">Overall Completion</span>
                        <span class="text-sm font-semibold text-accent">{{ $student->overall_progress }}%</span>
                    </div>
                    <x-progress :value="$student->overall_progress" size="lg" />
                </div>

                <div class="grid sm:grid-cols-3 gap-4 mt-6">
                    <div class="text-center p-4 bg-surface rounded-xl">
                        <p class="text-2xl font-semibold text-primary">{{ $tasks->where('status', 'completed')->count() }}</p>
                        <p class="text-xs text-secondary mt-1">Tasks Done</p>
                    </div>
                    <div class="text-center p-4 bg-surface rounded-xl">
                        <p class="text-2xl font-semibold text-warning">{{ $tasks->where('status', 'in_progress')->count() }}</p>
                        <p class="text-xs text-secondary mt-1">In Progress</p>
                    </div>
                    <div class="text-center p-4 bg-surface rounded-xl">
                        <p class="text-2xl font-semibold text-info">{{ $tasks->where('status', 'waiting_review')->count() }}</p>
                        <p class="text-xs text-secondary mt-1">Under Review</p>
                    </div>
                </div>
            </x-card>

            {{-- Recent activity --}}
            <x-card title="Recent Activity">
                <div class="space-y-3">
                    @foreach($recentActivity ?? [] as $activity)
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-full @if($activity['type'] === 'task') bg-accent/10 text-accent @elseif($activity['type'] === 'report') bg-info/10 text-info @else bg-success/10 text-success @endif flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm text-primary">{{ $activity['title'] }}</p>
                                <p class="text-xs text-secondary">{{ $activity['time'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-card>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Quick actions --}}
            <x-card title="Quick Actions" :padding="'tight'">
                <div class="space-y-1">
                    <a href="{{ route('tasks.create', $student) }}" class="flex items-center gap-2 px-3 py-2 text-sm text-secondary hover:text-primary hover:bg-surface rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add New Task
                    </a>
                    <a href="{{ route('reports.create', $student) }}" class="flex items-center gap-2 px-3 py-2 text-sm text-secondary hover:text-primary hover:bg-surface rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Submit Report
                    </a>
                    <a href="{{ route('meetings.create', $student) }}" class="flex items-center gap-2 px-3 py-2 text-sm text-secondary hover:text-primary hover:bg-surface rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Schedule Meeting
                    </a>
                    <a href="{{ route('ai.chat') }}" class="flex items-center gap-2 px-3 py-2 text-sm text-secondary hover:text-primary hover:bg-surface rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                        Ask AI Assistant
                    </a>
                </div>
            </x-card>

            {{-- Upcoming deadlines --}}
            <x-card title="Upcoming Deadlines">
                <div class="space-y-3">
                    @foreach($upcomingDeadlines ?? [] as $deadline)
                        <div class="flex items-center gap-3">
                            <div class="text-center">
                                <p class="text-lg font-semibold text-primary">{{ $deadline['day'] }}</p>
                                <p class="text-[10px] text-secondary uppercase">{{ $deadline['month'] }}</p>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-primary truncate">{{ $deadline['title'] }}</p>
                                <p class="text-xs text-secondary">{{ $deadline['type'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-card>
        </div>
    </div>
</x-layouts.app>
