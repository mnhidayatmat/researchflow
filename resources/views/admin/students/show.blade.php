<x-layouts.app :title="$student->user->name">
    <x-slot:header>Student Profile</x-slot:header>

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-1.5 text-xs text-secondary dark:text-dark-secondary mb-4 sm:mb-5">
        <a href="{{ route('admin.students.index') }}" class="hover:text-primary dark:hover:text-dark-primary transition-colors">Students</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-primary dark:text-dark-primary truncate">{{ $student->user->name }}</span>
    </nav>

    {{-- Profile header --}}
    <div class="bg-card dark:bg-dark-card rounded-2xl border border-border dark:border-dark-border p-4 sm:p-6 mb-5 sm:mb-6">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div class="flex items-center gap-3 sm:gap-4">
                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-full bg-accent/10 text-accent flex items-center justify-center text-lg sm:text-xl font-semibold shrink-0">
                    {{ substr($student->user->name, 0, 1) }}
                </div>
                <div class="min-w-0">
                    <h2 class="text-base sm:text-lg font-semibold text-primary dark:text-dark-primary truncate">{{ $student->user->name }}</h2>
                    <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 mt-1">
                        <span class="text-xs text-secondary dark:text-dark-secondary font-mono">{{ $student->user->matric_number ?? 'No matric' }}</span>
                        <span class="text-secondary dark:text-dark-secondary text-xs hidden sm:inline">&middot;</span>
                        <span class="text-xs text-secondary dark:text-dark-secondary hidden sm:inline">{{ $student->user->email }}</span>
                        <x-status-badge :status="$student->status" />
                    </div>
                    <p class="text-xs text-secondary dark:text-dark-secondary mt-1 sm:hidden truncate">{{ $student->user->email }}</p>
                </div>
            </div>
            <x-button href="{{ route('admin.students.edit', $student) }}" variant="secondary" size="sm" class="w-full justify-center sm:w-auto shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit
            </x-button>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-4 sm:gap-5">
        {{-- Left column --}}
        <div class="space-y-4">
            {{-- Student info --}}
            <x-card title="Student Information">
                <dl class="space-y-3">
                    <div>
                        <dt class="text-xs font-medium text-secondary dark:text-dark-secondary uppercase tracking-wide">Programme</dt>
                        <dd class="mt-0.5 text-sm text-primary dark:text-dark-primary">{{ $student->programme->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-secondary dark:text-dark-secondary uppercase tracking-wide">Research Title</dt>
                        <dd class="mt-0.5 text-sm text-primary dark:text-dark-primary">{{ $student->research_title ?? '—' }}</dd>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <dt class="text-xs font-medium text-secondary dark:text-dark-secondary uppercase tracking-wide">Supervisor</dt>
                            <dd class="mt-0.5 text-sm text-primary dark:text-dark-primary">{{ $student->supervisor?->name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-secondary dark:text-dark-secondary uppercase tracking-wide">Co-Supervisor</dt>
                            <dd class="mt-0.5 text-sm text-primary dark:text-dark-primary">{{ $student->cosupervisor?->name ?? '—' }}</dd>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <dt class="text-xs font-medium text-secondary dark:text-dark-secondary uppercase tracking-wide">Intake</dt>
                            <dd class="mt-0.5 text-sm text-primary dark:text-dark-primary">{{ $student->intake ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-secondary dark:text-dark-secondary uppercase tracking-wide">Start</dt>
                            <dd class="mt-0.5 text-sm text-primary dark:text-dark-primary">{{ $student->start_date?->format('M Y') ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-secondary dark:text-dark-secondary uppercase tracking-wide">End</dt>
                            <dd class="mt-0.5 text-sm text-primary dark:text-dark-primary">{{ $student->expected_completion?->format('M Y') ?? '—' }}</dd>
                        </div>
                    </div>
                </dl>
            </x-card>

            {{-- Overall progress --}}
            <x-card title="Overall Progress">
                <div class="text-center py-2">
                    <div class="text-2xl sm:text-3xl font-semibold text-primary dark:text-dark-primary">{{ $student->overall_progress ?? 0 }}%</div>
                    <div class="mt-2 w-full bg-gray-100 dark:bg-dark-border rounded-full h-2">
                        <div class="bg-accent h-2 rounded-full transition-all" style="width: {{ $student->overall_progress ?? 0 }}%"></div>
                    </div>
                    <p class="text-xs text-secondary dark:text-dark-secondary mt-2">Overall progress</p>
                </div>
            </x-card>

            <x-card title="Publication Tracking">
                <div class="text-center py-2">
                    <div class="text-2xl sm:text-3xl font-semibold text-primary dark:text-dark-primary">{{ $student->publicationTracks->count() }}</div>
                    <p class="text-xs text-secondary dark:text-dark-secondary mt-2">Journal records tracked</p>
                    <div class="mt-4">
                        <x-button :href="route('publications.index', $student)" variant="secondary" size="sm" class="w-full justify-center sm:w-auto">Open Tracker</x-button>
                    </div>
                </div>
            </x-card>
        </div>

        {{-- Right column --}}
        <div class="lg:col-span-2 space-y-4">
            {{-- Recent tasks --}}
            <x-card title="Recent Tasks" :padding='false'>
                <div class="divide-y divide-border dark:divide-dark-border">
                    @forelse($student->tasks->take(5) as $task)
                        <div class="flex items-center justify-between gap-3 px-4 sm:px-5 py-3">
                            <div class="min-w-0">
                                <a href="{{ route('tasks.show', [$student, $task]) }}" class="text-sm font-medium text-primary dark:text-dark-primary hover:text-accent transition-colors truncate block">{{ $task->title }}</a>
                                @if($task->due_date)
                                    <p class="text-xs text-secondary dark:text-dark-secondary mt-0.5">Due {{ $task->due_date->format('d M Y') }}</p>
                                @endif
                            </div>
                            <x-status-badge :status="$task->status" />
                        </div>
                    @empty
                        <div class="px-4 sm:px-5 py-6 text-center text-sm text-secondary dark:text-dark-secondary">No tasks yet</div>
                    @endforelse
                </div>
                @if($student->tasks->count() > 5)
                    <div class="px-4 sm:px-5 py-3 border-t border-border dark:border-dark-border">
                        <a href="{{ route('tasks.index', $student) }}" class="text-xs text-accent hover:underline">View all {{ $student->tasks->count() }} tasks</a>
                    </div>
                @endif
            </x-card>

            {{-- Recent progress reports --}}
            <x-card title="Recent Progress Reports" :padding='false'>
                <div class="divide-y divide-border dark:divide-dark-border">
                    @forelse($student->progressReports->take(5) as $report)
                        <div class="flex items-center justify-between gap-3 px-4 sm:px-5 py-3">
                            <div class="min-w-0">
                                <a href="{{ route('reports.show', [$student, $report]) }}" class="text-sm font-medium text-primary dark:text-dark-primary hover:text-accent transition-colors truncate block">{{ $report->title }}</a>
                                <p class="text-xs text-secondary dark:text-dark-secondary mt-0.5">{{ $report->submitted_at?->format('d M Y') ?? 'Not submitted' }}</p>
                            </div>
                            <x-status-badge :status="$report->status" />
                        </div>
                    @empty
                        <div class="px-4 sm:px-5 py-6 text-center text-sm text-secondary dark:text-dark-secondary">No reports submitted</div>
                    @endforelse
                </div>
                @if($student->progressReports->count() > 5)
                    <div class="px-4 sm:px-5 py-3 border-t border-border dark:border-dark-border">
                        <a href="{{ route('reports.index', $student) }}" class="text-xs text-accent hover:underline">View all {{ $student->progressReports->count() }} reports</a>
                    </div>
                @endif
            </x-card>

            <x-card title="Publication Track" :padding='false'>
                <div class="divide-y divide-border dark:divide-dark-border">
                    @forelse($student->publicationTracks->take(5) as $publication)
                        <div class="flex items-start justify-between gap-3 px-4 sm:px-5 py-3">
                            <div class="min-w-0">
                                <a href="{{ route('publications.index', $student) }}" class="text-sm font-medium text-primary dark:text-dark-primary hover:text-accent transition-colors truncate block">{{ $publication->title }}</a>
                                <p class="text-xs text-secondary dark:text-dark-secondary mt-0.5">{{ $publication->journal }}</p>
                            </div>
                            <span class="inline-flex rounded-full bg-accent/10 px-2.5 py-1 text-xs font-medium text-accent shrink-0">{{ $publication->stage_label }}</span>
                        </div>
                    @empty
                        <div class="px-4 sm:px-5 py-6 text-center text-sm text-secondary dark:text-dark-secondary">No publication records yet</div>
                    @endforelse
                </div>
                <div class="px-4 sm:px-5 py-3 border-t border-border dark:border-dark-border">
                    <a href="{{ route('publications.index', $student) }}" class="text-xs text-accent hover:underline">
                        @if($student->publicationTracks->count() > 5)
                            View all {{ $student->publicationTracks->count() }} publication records
                        @else
                            Open publication tracker
                        @endif
                    </a>
                </div>
            </x-card>
        </div>
    </div>
</x-layouts.app>
