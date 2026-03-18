<x-layouts.app title="Supervisor Dashboard" :header="'Dashboard'">
    {{-- Welcome --}}
    <div class="mb-8">
        <h1 class="text-xl font-semibold text-primary">Welcome back, {{ auth()->user()->name }}</h1>
        <p class="text-sm text-secondary mt-1">You have {{ $stats['pending_reviews'] }} item{{ $stats['pending_reviews'] !== 1 ? 's' : '' }} awaiting your review.</p>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <x-stat-card
            title="Total Students"
            :value="$stats['total_students']"
            icon="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
            variant="default"
        />
        <x-stat-card
            title="Active Students"
            :value="$stats['active_students']"
            icon="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
            variant="success"
        />
        <x-stat-card
            title="Pending Reviews"
            :value="$stats['pending_reviews']"
            icon="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
            variant="warning"
        />
        <x-stat-card
            title="Tasks to Review"
            :value="$stats['tasks_waiting_review']"
            icon="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"
            variant="accent"
        />
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        {{-- Students list --}}
        <x-card title="My Students" class="lg:col-span-2" :action="'View All'">
            <div class="divide-y divide-border">
                @forelse($students as $s)
                    <a href="{{ route('supervisor.students.show', $s) }}" class="flex items-center justify-between py-4 hover:bg-surface -mx-5 px-5 transition-colors">
                        <div class="flex items-center gap-3">
                            <x-avatar :name="$s->user->name" size="md" :status="$s->status === 'active' ? 'online' : 'offline'" />
                            <div>
                                <p class="text-sm font-medium text-primary">{{ $s->user->name }}</p>
                                <p class="text-xs text-secondary">{{ $s->programme->name }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="hidden sm:block text-right">
                                @if($s->research_title)
                                    <p class="text-xs text-primary max-w-[150px] truncate">{{ $s->research_title }}</p>
                                @else
                                    <p class="text-xs text-tertiary">No title yet</p>
                                @endif
                                <p class="text-xs text-tertiary">{{ $s->matric_number ?? 'N/A' }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <x-progress :value="$s->overall_progress ?? 0" size="sm" />
                                <span class="text-xs font-medium text-secondary w-8">{{ $s->overall_progress ?? 0 }}%</span>
                            </div>
                        </div>
                    </a>
                @empty
                    <x-empty-state
                        title="No students assigned"
                        description="Students will appear here once they are assigned to you."
                        icon="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
                    />
                @endforelse
            </div>
        </x-card>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Pending reports --}}
            <x-card title="Reports to Review" :subtitle="$pendingReports->count() . ' pending'">
                <div class="space-y-2 max-h-[280px] overflow-y-auto -mx-2">
                    @forelse($pendingReports as $report)
                        <a href="{{ route('reports.show', [$report->student_id, $report]) }}" class="flex items-start gap-3 p-2 rounded-lg hover:bg-surface transition-colors">
                            <div class="w-8 h-8 rounded-lg bg-warning-light text-warning flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-primary truncate">{{ $report->title }}</p>
                                <p class="text-xs text-secondary">{{ $report->student->user->name }}</p>
                                <p class="text-[10px] text-tertiary mt-0.5">{{ $report->submitted_at?->diffForHumans() ?? 'Recently' }}</p>
                            </div>
                        </a>
                    @empty
                        <p class="text-sm text-secondary text-center py-4">No reports pending review</p>
                    @endforelse
                </div>
            </x-card>

            {{-- Quick actions --}}
            <x-card title="Quick Actions" :padding="'tight'">
                <div class="space-y-1">
                    <a href="{{ route('supervisor.students.index') }}" class="flex items-center gap-2 px-3 py-2 text-sm text-secondary hover:text-primary hover:bg-surface rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        View All Students
                    </a>
                    <a href="{{ route('ai.chat') }}" class="flex items-center gap-2 px-3 py-2 text-sm text-secondary hover:text-primary hover:bg-surface rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                        AI Assistant
                    </a>
                </div>
            </x-card>
        </div>
    </div>

    {{-- Tasks waiting review --}}
    <x-card title="Tasks Waiting Review" class="mt-6" :padding='false'>
        @if($stats['tasks_waiting_review'] > 0)
            <div class="px-5 py-3 bg-warning-light border-b border-border flex items-center justify-between">
                <span class="text-sm font-medium text-warning">{{ $stats['tasks_waiting_review'] }} task{{ $stats['tasks_waiting_review'] !== 1 ? 's' : '' }} need your review</span>
            </div>
        @endif
        <x-table :headers="['Task', 'Student', 'Due Date', 'Submitted', '']" :striped='false'>
            @forelse($tasksForReview as $task)
                <tr class="hover:bg-surface/50 cursor-pointer" @click="window.location='{{ route('tasks.show', [$task->student_id, $task]) }}'">
                    <td>
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-accent/10 text-accent flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-primary">{{ $task->title }}</span>
                        </div>
                    </td>
                    <td class="text-sm text-secondary">{{ $task->student->user->name }}</td>
                    <td class="text-sm text-secondary">{{ $task->due_date?->format('M d, Y') ?? '—' }}</td>
                    <td class="text-sm text-secondary">{{ $task->created_at->diffForHumans() }}</td>
                    <td class="text-right"><x-status-badge :status="$task->status" size="sm" /></td>
                </tr>
            @empty
            <tr>
                <td colspan="5">
                    <div class="py-8">
                        <x-empty-state
                            title="All caught up"
                            description="No tasks are currently waiting for your review."
                        />
                    </div>
                </td>
            </tr>
            @endforelse
        </x-table>
    </x-card>
</x-layouts.app>
