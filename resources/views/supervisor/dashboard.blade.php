<x-layouts.app title="Supervisor Dashboard" :header="'Dashboard'">
    <div class="space-y-6">
        {{-- Welcome --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-base font-semibold text-primary">Welcome back, {{ auth()->user()->name }}</h2>
                <p class="text-xs text-secondary mt-0.5">You have {{ $stats['pending_reviews'] }} item{{ $stats['pending_reviews'] !== 1 ? 's' : '' }} awaiting your review.</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs text-tertiary">{{ now()->format('l, F j, Y') }}</span>
            </div>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Total Students --}}
            <div class="group relative bg-card rounded-2xl p-6 border border-border hover:border-accent/30 hover:shadow-soft transition-all duration-300">
                <div class="flex items-start justify-between">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-accent/20 to-accent/10 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <p class="text-lg font-bold text-primary">{{ $stats['total_students'] }}</p>
                    <p class="text-xs text-secondary">Total Students</p>
                </div>
            </div>

            {{-- Active Students --}}
            <div class="group relative bg-card rounded-2xl p-6 border border-border hover:border-success/30 hover:shadow-soft transition-all duration-300">
                <div class="flex items-start justify-between">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-success/20 to-success/10 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <p class="text-lg font-bold text-primary">{{ $stats['active_students'] }}</p>
                    <p class="text-xs text-secondary">Active Students</p>
                </div>
            </div>

            {{-- Pending Reviews --}}
            <div class="group relative bg-card rounded-2xl p-6 border border-border hover:border-warning/30 hover:shadow-soft transition-all duration-300">
                <div class="flex items-start justify-between">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-warning/20 to-warning/10 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    @if($stats['pending_reviews'] > 0)
                    <div class="w-3 h-3 rounded-full bg-warning animate-pulse"></div>
                    @endif
                </div>
                <div class="mt-4">
                    <p class="text-lg font-bold text-primary">{{ $stats['pending_reviews'] }}</p>
                    <p class="text-xs text-secondary">Pending Reviews</p>
                </div>
            </div>

            {{-- Tasks to Review --}}
            <div class="group relative bg-card rounded-2xl p-6 border border-border hover:border-info/30 hover:shadow-soft transition-all duration-300">
                <div class="flex items-start justify-between">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-info/20 to-info/10 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <p class="text-lg font-bold text-primary">{{ $stats['tasks_waiting_review'] }}</p>
                    <p class="text-xs text-secondary">Tasks to Review</p>
                </div>
            </div>
        </div>

    <div class="grid lg:grid-cols-3 gap-6">
        {{-- Students list --}}
        <div class="lg:col-span-2 bg-card rounded-2xl border border-border overflow-hidden">
            <div class="px-6 py-5 border-b border-border flex items-center justify-between">
                <div>
                    <h3 class="text-base font-semibold text-primary">My Students</h3>
                    <p class="text-xs text-secondary mt-0.5">Students under your supervision</p>
                </div>
                @if($students->count() > 0)
                <a href="{{ route('supervisor.students.index') }}" class="text-xs text-accent hover:text-amber-700 font-medium inline-flex items-center gap-1">
                    View all
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </a>
                @endif
            </div>
            <div class="divide-y divide-border">
                @forelse($students as $s)
                    <a href="{{ route('supervisor.students.show', $s) }}" class="flex items-center gap-4 p-5 hover:bg-surface transition-colors group">
                        <div class="relative">
                            <x-avatar :name="$s->user->name" size="md" />
                            <span class="absolute -bottom-0.5 -right-0.5 w-4 h-4 rounded-full border-2 border-card {{ $s->status === 'active' ? 'bg-success' : 'bg-tertiary' }}"></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <p class="text-sm font-medium text-primary group-hover:text-accent transition-colors truncate">{{ $s->user->name }}</p>
                                <x-status-badge :status="$s->status" size="sm" />
                            </div>
                            <p class="text-xs text-secondary">{{ $s->programme->name }}</p>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="text-right hidden sm:block">
                                <p class="text-xs text-tertiary mb-1">Progress</p>
                                <div class="flex items-center gap-2">
                                    <div class="w-20 h-2 bg-border-light rounded-full overflow-hidden">
                                        <div class="h-full bg-accent rounded-full" style="width: {{ $s->overall_progress ?? 0 }}%"></div>
                                    </div>
                                    <span class="text-xs font-medium text-secondary w-8">{{ $s->overall_progress ?? 0 }}%</span>
                                </div>
                            </div>
                            <svg class="w-5 h-5 text-tertiary group-hover:text-accent transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </a>
                @empty
                    <div class="p-12">
                        <div class="flex flex-col items-center justify-center text-center">
                            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-accent/15 to-accent/5 flex items-center justify-center mb-4">
                                <svg class="w-8 h-8 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                            </div>
                            <h3 class="text-base font-semibold text-primary mb-1">No students assigned</h3>
                            <p class="text-sm text-secondary">Students will appear here once they are assigned to you.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Pending reports --}}
            <div class="bg-card rounded-2xl border border-border overflow-hidden">
                <div class="px-6 py-5 border-b border-border">
                    <h3 class="text-base font-semibold text-primary">Reports to Review</h3>
                    <p class="text-xs text-secondary mt-0.5">{{ $pendingReports->count() }} pending</p>
                </div>
                <div class="p-4">
                    <div class="space-y-2 max-h-[280px] overflow-y-auto">
                        @forelse($pendingReports as $report)
                            <a href="{{ route('reports.show', [$report->student_id, $report]) }}" class="flex items-start gap-3 p-3 rounded-xl hover:bg-surface transition-colors border border-transparent hover:border-border">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-warning/20 to-warning/10 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-5 h-5 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-primary truncate">{{ $report->title }}</p>
                                    <p class="text-xs text-secondary">{{ $report->student->user->name }}</p>
                                    <p class="text-[10px] text-tertiary mt-0.5">{{ $report->submitted_at?->diffForHumans() ?? 'Recently' }}</p>
                                </div>
                            </a>
                        @empty
                            <p class="text-sm text-secondary text-center py-4">No reports pending review</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Quick actions --}}
            <div class="bg-card rounded-2xl border border-border overflow-hidden">
                <div class="px-6 py-5 border-b border-border">
                    <h3 class="text-base font-semibold text-primary">Quick Actions</h3>
                </div>
                <div class="p-2">
                    <a href="{{ route('supervisor.students.index') }}" class="flex items-center gap-3 px-4 py-3 text-sm text-secondary hover:text-primary hover:bg-surface rounded-xl transition-colors">
                        <div class="w-8 h-8 rounded-lg bg-accent/10 flex items-center justify-center">
                            <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                        <span>View All Students</span>
                        <svg class="w-4 h-4 text-tertiary ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                    <a href="{{ route('ai.chat') }}" class="flex items-center gap-3 px-4 py-3 text-sm text-secondary hover:text-primary hover:bg-surface rounded-xl transition-colors">
                        <div class="w-8 h-8 rounded-lg bg-success/10 flex items-center justify-center">
                            <svg class="w-4 h-4 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                            </svg>
                        </div>
                        <span>AI Assistant</span>
                        <svg class="w-4 h-4 text-tertiary ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Tasks waiting review --}}
    @if($tasksForReview->count() > 0)
    <div class="bg-card rounded-2xl border border-border overflow-hidden">
        <div class="px-6 py-5 border-b border-border flex items-center justify-between">
            <div>
                <h3 class="text-base font-semibold text-primary">Tasks Waiting Review</h3>
                <p class="text-xs text-secondary mt-0.5">Requiring your attention</p>
            </div>
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-warning/10 text-warning text-xs font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ $tasksForReview->count() }} due
            </div>
        </div>
        <div class="divide-y divide-border">
            @forelse($tasksForReview as $task)
                <a href="{{ route('tasks.show', [$task->student_id, $task]) }}" class="flex items-center gap-4 p-5 hover:bg-surface transition-colors group">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-warning/20 to-warning/10 flex items-center justify-center">
                        <svg class="w-6 h-6 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-primary group-hover:text-accent transition-colors truncate">{{ $task->title }}</p>
                        <div class="flex items-center gap-3 mt-1">
                            <p class="text-xs text-secondary">{{ $task->student->user->name }}</p>
                            <span class="text-tertiary">•</span>
                            <p class="text-xs text-tertiary">Due {{ $task->due_date?->format('M j') ?? '—' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <x-status-badge :status="$task->status" size="sm" />
                        <svg class="w-5 h-5 text-tertiary group-hover:text-accent transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </a>
            @empty
                <div class="p-12">
                    <div class="flex flex-col items-center justify-center text-center">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-success/15 to-success/5 flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-primary mb-1">All caught up</h3>
                        <p class="text-sm text-secondary">No tasks are currently waiting for your review.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
    @endif
</x-layouts.app>
