<x-layouts.app title="My Dashboard" :header="'Dashboard'">
    <div class="space-y-6">
        {{-- Welcome Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-primary">Welcome back, {{ auth()->user()->name }}</h1>
                <p class="text-sm text-secondary mt-1">Your research progress: {{ $student->overall_progress }}% complete</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs text-tertiary">{{ now()->format('l, F j, Y') }}</span>
                <button class="relative p-2.5 text-secondary hover:text-primary hover:bg-surface rounded-xl transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    {{-- @if($tasksByStatus->get('waiting_review', collect())->count() > 0)
                    <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-warning rounded-full ring-2 ring-card"></span>
                    @endif --}}
                </button>
            </div>
        </div>

        {{-- KPI Stats --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Programme --}}
            <div class="group relative bg-card rounded-2xl p-6 border border-border hover:border-info/30 hover:shadow-soft transition-all duration-300">
                <div class="flex items-start justify-between">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-info/20 to-info/10 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <p class="text-lg font-bold text-primary">{{ $student->programme->code }}</p>
                    <p class="text-xs text-secondary mt-1">Programme</p>
                </div>
            </div>

            {{-- Overall Progress --}}
            <div class="group relative bg-card rounded-2xl p-6 border border-border hover:border-accent/30 hover:shadow-soft transition-all duration-300">
                <div class="flex items-start justify-between">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-accent/20 to-accent/10 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    @if($student->overall_progress >= 75)
                    <div class="flex items-center gap-1 px-2 py-1 rounded-full bg-success/10 text-success text-xs font-semibold">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                        </svg>
                        <span>On track</span>
                    </div>
                    @endif
                </div>
                <div class="mt-4">
                    <div class="flex items-baseline gap-2">
                        <p class="text-lg font-bold text-primary">{{ $student->overall_progress }}%</p>
                        <p class="text-xs text-secondary">complete</p>
                    </div>
                    <div class="w-full h-2 bg-border-light rounded-full overflow-hidden mt-2">
                        <div class="h-full {{ $student->overall_progress >= 75 ? 'bg-success' : ($student->overall_progress >= 40 ? 'bg-accent' : 'bg-warning') }} rounded-full transition-all duration-500" style="width: {{ $student->overall_progress }}%"></div>
                    </div>
                </div>
            </div>

            {{-- Total Tasks --}}
            <a href="{{ route('tasks.index', $student) }}" class="group relative bg-card rounded-2xl p-6 border border-border hover:border-success/30 hover:shadow-soft transition-all duration-300 block">
                <div class="flex items-start justify-between">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-success/20 to-success/10 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="flex items-baseline gap-2">
                        <p class="text-lg font-bold text-primary">{{ $tasks->count() }}</p>
                        <p class="text-xs text-secondary">total</p>
                    </div>
                    <p class="text-xs text-secondary mt-1">Tasks</p>
                </div>
            </a>

            {{-- Completed --}}
            <div class="group relative bg-card rounded-2xl p-6 border border-border hover:border-success/30 hover:shadow-soft transition-all duration-300">
                <div class="flex items-start justify-between">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-success/20 to-success/10 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    @if($tasks->where('status', 'completed')->count() > 0)
                    <div class="w-3 h-3 rounded-full bg-success animate-pulse"></div>
                    @endif
                </div>
                <div class="mt-4">
                    <div class="flex items-baseline gap-2">
                        <p class="text-lg font-bold text-primary">{{ $tasks->where('status', 'completed')->count() }}</p>
                        <p class="text-xs text-secondary">done</p>
                    </div>
                    <p class="text-xs text-secondary mt-1">Completed</p>
                </div>
            </div>
        </div>

        {{-- Main Content Grid --}}
        <div class="grid lg:grid-cols-3 gap-6">
            {{-- Left Column (2 columns) --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Research Info Card --}}
                <div class="bg-card rounded-2xl border border-border overflow-hidden">
                    <div class="px-6 py-5 border-b border-border">
                        <h2 class="text-base font-semibold text-primary">Research Overview</h2>
                        <p class="text-xs text-secondary mt-0.5">Your research journey details</p>
                    </div>
                    <div class="p-6">
                        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                            <div>
                                <p class="text-xs font-medium text-secondary uppercase tracking-wide">Research Title</p>
                                <p class="text-sm font-medium text-primary mt-1">{{ $student->research_title ?? 'Not assigned yet' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-secondary uppercase tracking-wide">Supervisor</p>
                                <div class="flex items-center gap-2 mt-1">
                                    @if($student->supervisor)
                                        <x-avatar :name="$student->supervisor->name" size="sm" />
                                        <p class="text-sm font-medium text-primary">{{ $student->supervisor->name }}</p>
                                    @else
                                        <p class="text-sm text-secondary">Not assigned</p>
                                    @endif
                                </div>
                                @if($student->cosupervisor)
                                    <div class="flex items-center gap-2 mt-1">
                                        <x-avatar :name="$student->cosupervisor->name" size="xs" />
                                        <p class="text-xs text-secondary">Co: {{ $student->cosupervisor->name }}</p>
                                    </div>
                                @endif
                            </div>
                            <div>
                                <p class="text-xs font-medium text-secondary uppercase tracking-wide">Start Date</p>
                                <p class="text-sm text-primary mt-1">{{ $student->start_date?->format('M d, Y') ?? '—' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-secondary uppercase tracking-wide">Expected Completion</p>
                                <p class="text-sm text-primary mt-1">{{ $student->expected_completion?->format('M d, Y') ?? '—' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Upcoming Tasks --}}
                <div class="bg-card rounded-2xl border border-border overflow-hidden">
                    <div class="px-6 py-5 border-b border-border flex items-center justify-between">
                        <div>
                            <h2 class="text-base font-semibold text-primary">Upcoming Tasks</h2>
                            <p class="text-xs text-secondary mt-0.5">Tasks requiring your attention</p>
                        </div>
                        @if($upcomingTasks->count() > 0)
                        <a href="{{ route('tasks.index', $student) }}" class="text-xs text-accent hover:text-amber-700 font-medium inline-flex items-center gap-1">
                            View all
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                            </svg>
                        </a>
                        @endif
                    </div>
                    @if($upcomingTasks->count() === 0)
                        <div class="p-12">
                            <div class="flex flex-col items-center justify-center text-center">
                                <div class="w-20 h-20 rounded-3xl bg-gradient-to-br from-accent/15 to-accent/5 flex items-center justify-center mb-6">
                                    <svg class="w-10 h-10 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-semibold text-primary mb-2">No upcoming tasks</h3>
                                <p class="text-sm text-secondary max-w-sm mb-8">You're all caught up! Enjoy your free time or check out your completed tasks.</p>
                                <a href="{{ route('tasks.index', $student) }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold bg-accent text-white hover:bg-amber-700 transition-all shadow-sm hover:shadow">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    View All Tasks
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="divide-y divide-border">
                            @foreach($upcomingTasks as $task)
                            <a href="{{ route('tasks.show', [$student, $task]) }}" class="flex items-center gap-4 p-5 hover:bg-surface transition-colors group">
                                <div class="w-12 h-12 rounded-xl @if($task->status === 'in_progress') bg-gradient-to-br from-accent/20 to-accent/10 @elseif($task->status === 'waiting_review') bg-gradient-to-br from-warning/20 to-warning/10 @else bg-gradient-to-br from-tertiary/10 to-tertiary/5 @endif flex items-center justify-center">
                                    <svg class="w-6 h-6 @if($task->status === 'in_progress') text-accent @elseif($task->status === 'waiting_review') text-warning @else text-secondary @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-primary group-hover:text-accent transition-colors truncate">{{ $task->title }}</p>
                                    <div class="flex items-center gap-3 mt-1">
                                        <p class="text-xs text-secondary">Due {{ $task->due_date?->format('M d, Y') ?? 'No date' }}</p>
                                        @if($task->milestone)
                                        <span class="text-tertiary">•</span>
                                        <p class="text-xs text-tertiary">{{ $task->milestone->title }}</p>
                                        @endif
                                    </div>
                                </div>
                                <x-status-badge :status="$task->status" size="sm" />
                                <svg class="w-5 h-5 text-tertiary group-hover:text-accent transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Recent Reports --}}
                <div class="bg-card rounded-2xl border border-border overflow-hidden">
                    <div class="px-6 py-5 border-b border-border flex items-center justify-between">
                        <div>
                            <h2 class="text-base font-semibold text-primary">Progress Reports</h2>
                            <p class="text-xs text-secondary mt-0.5">Your submitted progress reports</p>
                        </div>
                        @if($recentReports->count() > 0)
                        <a href="{{ route('reports.index', $student) }}" class="text-xs text-accent hover:text-amber-700 font-medium inline-flex items-center gap-1">
                            View all
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                            </svg>
                        </a>
                        @endif
                    </div>
                    @if($recentReports->count() === 0)
                        <div class="p-12">
                            <div class="flex flex-col items-center justify-center text-center">
                                <div class="w-20 h-20 rounded-3xl bg-gradient-to-br from-info/15 to-info/5 flex items-center justify-center mb-6">
                                    <svg class="w-10 h-10 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-semibold text-primary mb-2">No reports yet</h3>
                                <p class="text-sm text-secondary max-w-sm mb-8">Start documenting your research progress by submitting your first report.</p>
                                <a href="{{ route('reports.create', $student) }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold bg-info text-white hover:bg-blue-700 transition-all shadow-sm hover:shadow">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Create Report
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="divide-y divide-border">
                            @foreach($recentReports as $report)
                            <a href="{{ route('reports.show', [$student, $report]) }}" class="flex items-center justify-between p-5 hover:bg-surface transition-colors group">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-info/20 to-info/10 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-primary group-hover:text-accent transition-colors">{{ $report->title }}</p>
                                        <p class="text-xs text-secondary mt-1">{{ $report->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                                <x-status-badge :status="$report->status" size="sm" />
                            </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Right Sidebar --}}
            <div class="space-y-6">
                {{-- Quick Stats --}}
                <div class="bg-card rounded-2xl border border-border overflow-hidden">
                    <div class="px-6 py-5 border-b border-border">
                        <h2 class="text-base font-semibold text-primary">Quick Stats</h2>
                    </div>
                    <div class="p-6 space-y-5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-warning/10 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-lg font-bold text-primary">{{ $tasksByStatus->get('in_progress', collect())->count() }}</p>
                                    <p class="text-xs text-secondary">In Progress</p>
                                </div>
                            </div>
                        </div>
                        <div class="h-px bg-border"></div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-accent/10 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-lg font-bold text-primary">{{ $tasksByStatus->get('waiting_review', collect())->count() }}</p>
                                    <p class="text-xs text-secondary">In Review</p>
                                </div>
                            </div>
                        </div>
                        <div class="h-px bg-border"></div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-tertiary/10 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-tertiary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-lg font-bold text-primary">{{ $tasksByStatus->get('planned', collect())->count() }}</p>
                                    <p class="text-xs text-secondary">Planned</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Upcoming Meetings --}}
                <div class="bg-card rounded-2xl border border-border overflow-hidden">
                    <div class="px-6 py-5 border-b border-border flex items-center justify-between">
                        <h2 class="text-base font-semibold text-primary">Upcoming Meetings</h2>
                        @if($upcomingMeetings->count() > 0)
                        <a href="{{ route('meetings.index', $student) }}" class="text-xs text-accent hover:text-amber-700 font-medium">View all</a>
                        @else
                        <a href="{{ route('meetings.create', $student) }}" class="text-xs text-accent hover:text-amber-700 font-medium">Schedule</a>
                        @endif
                    </div>
                    <div class="p-6">
                        @if($upcomingMeetings->count() === 0)
                            <div class="text-center py-8">
                                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-info/15 to-info/5 flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <p class="text-sm text-secondary mb-4">No upcoming meetings</p>
                                <a href="{{ route('meetings.create', $student) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium bg-info text-white hover:bg-blue-700 transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Schedule Meeting
                                </a>
                            </div>
                        @else
                            <div class="space-y-3">
                                @foreach($upcomingMeetings as $meeting)
                                <a href="{{ route('meetings.show', [$student, $meeting]) }}" class="flex items-start gap-3 p-3 rounded-xl hover:bg-surface transition-all group">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-info/20 to-info/10 flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-primary group-hover:text-accent transition-colors truncate">{{ $meeting->title }}</p>
                                        <p class="text-xs text-secondary mt-1">{{ $meeting->scheduled_at->format('M d, Y · g:i A') }}</p>
                                    </div>
                                </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Quick Actions --}}
                <div class="bg-card rounded-2xl border border-border overflow-hidden">
                    <div class="px-6 py-5 border-b border-border">
                        <h2 class="text-base font-semibold text-primary">Quick Actions</h2>
                    </div>
                    <div class="p-3">
                        <a href="{{ route('tasks.index', $student) }}" class="flex items-center gap-3 p-3 rounded-xl text-secondary hover:bg-surface hover:text-primary transition-all group">
                            <div class="w-10 h-10 rounded-xl bg-accent/10 flex items-center justify-center group-hover:bg-accent/20 transition-colors">
                                <svg class="w-5 h-5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium">View Tasks</p>
                                <p class="text-xs text-tertiary">Manage your tasks</p>
                            </div>
                            <svg class="w-5 h-5 text-tertiary group-hover:text-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                        <a href="{{ route('reports.create', $student) }}" class="flex items-center gap-3 p-3 rounded-xl text-secondary hover:bg-surface hover:text-primary transition-all group">
                            <div class="w-10 h-10 rounded-xl bg-info/10 flex items-center justify-center group-hover:bg-info/20 transition-colors">
                                <svg class="w-5 h-5 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium">New Report</p>
                                <p class="text-xs text-tertiary">Submit progress</p>
                            </div>
                            <svg class="w-5 h-5 text-tertiary group-hover:text-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                        <a href="{{ route('meetings.create', $student) }}" class="flex items-center gap-3 p-3 rounded-xl text-secondary hover:bg-surface hover:text-primary transition-all group">
                            <div class="w-10 h-10 rounded-xl bg-success/10 flex items-center justify-center group-hover:bg-success/20 transition-colors">
                                <svg class="w-5 h-5 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium">Schedule Meeting</p>
                                <p class="text-xs text-tertiary">Book with supervisor</p>
                            </div>
                            <svg class="w-5 h-5 text-tertiary group-hover:text-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                        <a href="{{ route('publications.index', $student) }}" class="flex items-center gap-3 p-3 rounded-xl text-secondary hover:bg-surface hover:text-primary transition-all group">
                            <div class="w-10 h-10 rounded-xl bg-warning/10 flex items-center justify-center group-hover:bg-warning/20 transition-colors">
                                <svg class="w-5 h-5 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium">Publication Track</p>
                                <p class="text-xs text-tertiary">Monitor submissions and rejections</p>
                            </div>
                            <svg class="w-5 h-5 text-tertiary group-hover:text-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                        <a href="{{ route('ai.chat') }}" class="flex items-center gap-3 p-3 rounded-xl text-secondary hover:bg-surface hover:text-primary transition-all group">
                            <div class="w-10 h-10 rounded-xl bg-info/10 flex items-center justify-center group-hover:bg-info/20 transition-colors">
                                <svg class="w-5 h-5 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium">AI Assistant</p>
                                <p class="text-xs text-tertiary">Get research help</p>
                            </div>
                            <svg class="w-5 h-5 text-tertiary group-hover:text-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>

                <div class="bg-card rounded-2xl border border-border overflow-hidden">
                    <div class="px-6 py-5 border-b border-border flex items-center justify-between">
                        <div>
                            <h2 class="text-base font-semibold text-primary">Recent Publications</h2>
                            <p class="text-xs text-secondary mt-0.5">Latest journal submission updates</p>
                        </div>
                        <a href="{{ route('publications.index', $student) }}" class="text-xs text-accent hover:text-amber-700 font-medium">View all</a>
                    </div>
                    <div class="p-6">
                        @if($recentPublications->count() === 0)
                            <p class="text-sm text-secondary">No publication records yet.</p>
                        @else
                            <div class="space-y-3">
                                @foreach($recentPublications as $publication)
                                    <a href="{{ route('publications.index', $student) }}" class="flex items-start gap-3 p-3 rounded-xl hover:bg-surface transition-all group">
                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-warning/20 to-warning/10 flex items-center justify-center shrink-0">
                                            <svg class="w-5 h-5 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                            </svg>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-medium text-primary group-hover:text-accent transition-colors truncate">{{ $publication->title }}</p>
                                            <p class="text-xs text-secondary mt-1">{{ $publication->journal }}</p>
                                        </div>
                                        <span class="inline-flex rounded-full bg-accent/10 px-2.5 py-1 text-xs font-medium text-accent">{{ $publication->stage_label }}</span>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
