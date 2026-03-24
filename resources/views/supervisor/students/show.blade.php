<x-layouts.app :title="$student->user->name">
    <x-slot:header>Student Overview</x-slot:header>

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-1.5 text-xs text-secondary dark:text-dark-secondary mb-4 sm:mb-5">
        <a href="{{ route('supervisor.students.index') }}" class="hover:text-primary dark:hover:text-dark-primary transition-colors">My Students</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-primary dark:text-dark-primary truncate">{{ $student->user->name }}</span>
    </nav>

    {{-- Profile header --}}
    <div class="bg-card dark:bg-dark-card rounded-2xl border border-border dark:border-dark-border p-4 sm:p-6 mb-5 sm:mb-6">
        <div class="flex items-center gap-3 sm:gap-4">
            <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-full bg-accent/10 text-accent flex items-center justify-center text-lg sm:text-xl font-semibold shrink-0">
                {{ substr($student->user->name, 0, 1) }}
            </div>
            <div class="min-w-0">
                <h2 class="text-base sm:text-lg font-semibold text-primary dark:text-dark-primary truncate">{{ $student->user->name }}</h2>
                <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 mt-1">
                    <span class="text-xs text-secondary dark:text-dark-secondary font-mono">{{ $student->user->matric_number ?? 'No matric' }}</span>
                    <span class="text-secondary dark:text-dark-secondary text-xs hidden sm:inline">&middot;</span>
                    <span class="text-xs text-secondary dark:text-dark-secondary hidden sm:inline">{{ $student->programme->name ?? '—' }}</span>
                    <x-status-badge :status="$student->status" />
                </div>
                <p class="text-xs text-secondary dark:text-dark-secondary mt-0.5 sm:hidden">{{ $student->programme->name ?? '—' }}</p>
            </div>
        </div>
    </div>

    {{-- Quick action links - horizontal scroll on mobile --}}
    <div class="flex gap-2.5 sm:gap-3 overflow-x-auto pb-1 -mx-4 px-4 sm:mx-0 sm:px-0 sm:overflow-visible mb-5 sm:mb-6 scrollbar-thin sm:grid sm:grid-cols-5">
        <a href="{{ route('tasks.index', $student) }}" class="group flex flex-col items-center gap-1.5 sm:gap-2 p-3 sm:p-4 bg-card dark:bg-dark-card border border-border dark:border-dark-border rounded-xl sm:rounded-lg hover:border-accent/40 hover:shadow-sm transition-all text-center min-w-[80px] shrink-0 active:scale-95">
            <div class="w-9 h-9 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 flex items-center justify-center group-hover:bg-blue-100 dark:group-hover:bg-blue-900/30 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-primary dark:text-dark-primary">Tasks</p>
                <p class="text-[10px] text-secondary dark:text-dark-secondary">{{ $student->tasks->count() }}</p>
            </div>
        </a>
        <a href="{{ route('reports.index', $student) }}" class="group flex flex-col items-center gap-1.5 sm:gap-2 p-3 sm:p-4 bg-card dark:bg-dark-card border border-border dark:border-dark-border rounded-xl sm:rounded-lg hover:border-accent/40 hover:shadow-sm transition-all text-center min-w-[80px] shrink-0 active:scale-95">
            <div class="w-9 h-9 rounded-lg bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400 flex items-center justify-center group-hover:bg-purple-100 dark:group-hover:bg-purple-900/30 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-primary dark:text-dark-primary">Reports</p>
                <p class="text-[10px] text-secondary dark:text-dark-secondary">{{ $student->progressReports->count() }}</p>
            </div>
        </a>
        <a href="{{ route('meetings.index', $student) }}" class="group flex flex-col items-center gap-1.5 sm:gap-2 p-3 sm:p-4 bg-card dark:bg-dark-card border border-border dark:border-dark-border rounded-xl sm:rounded-lg hover:border-accent/40 hover:shadow-sm transition-all text-center min-w-[80px] shrink-0 active:scale-95">
            <div class="w-9 h-9 rounded-lg bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 flex items-center justify-center group-hover:bg-green-100 dark:group-hover:bg-green-900/30 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-primary dark:text-dark-primary">Meetings</p>
                <p class="text-[10px] text-secondary dark:text-dark-secondary">{{ $student->meetings->count() }}</p>
            </div>
        </a>
        <a href="{{ route('files.index', $student) }}" class="group flex flex-col items-center gap-1.5 sm:gap-2 p-3 sm:p-4 bg-card dark:bg-dark-card border border-border dark:border-dark-border rounded-xl sm:rounded-lg hover:border-accent/40 hover:shadow-sm transition-all text-center min-w-[80px] shrink-0 active:scale-95">
            <div class="w-9 h-9 rounded-lg bg-orange-50 dark:bg-orange-900/20 text-orange-600 dark:text-orange-400 flex items-center justify-center group-hover:bg-orange-100 dark:group-hover:bg-orange-900/30 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-primary dark:text-dark-primary">Files</p>
                <p class="text-[10px] text-secondary dark:text-dark-secondary">View</p>
            </div>
        </a>
        <a href="{{ route('publications.index', $student) }}" class="group flex flex-col items-center gap-1.5 sm:gap-2 p-3 sm:p-4 bg-card dark:bg-dark-card border border-border dark:border-dark-border rounded-xl sm:rounded-lg hover:border-accent/40 hover:shadow-sm transition-all text-center min-w-[80px] shrink-0 active:scale-95">
            <div class="w-9 h-9 rounded-lg bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 flex items-center justify-center group-hover:bg-amber-100 dark:group-hover:bg-amber-900/30 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-primary dark:text-dark-primary">Pubs</p>
                <p class="text-[10px] text-secondary dark:text-dark-secondary">{{ $student->publicationTracks->count() }}</p>
            </div>
        </a>
    </div>

    <div class="grid lg:grid-cols-3 gap-4 sm:gap-5">
        {{-- Left column --}}
        <div class="space-y-4">
            <x-card title="Student Information">
                <dl class="space-y-3">
                    <div>
                        <dt class="text-xs font-medium text-secondary dark:text-dark-secondary uppercase tracking-wide">Email</dt>
                        <dd class="mt-0.5 text-sm text-primary dark:text-dark-primary break-all">{{ $student->user->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-secondary dark:text-dark-secondary uppercase tracking-wide">Research Title</dt>
                        <dd class="mt-0.5 text-sm text-primary dark:text-dark-primary">{{ $student->research_title ?? '—' }}</dd>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <dt class="text-xs font-medium text-secondary dark:text-dark-secondary uppercase tracking-wide">Co-Supervisor</dt>
                            <dd class="mt-0.5 text-sm text-primary dark:text-dark-primary">{{ $student->cosupervisor?->name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-secondary dark:text-dark-secondary uppercase tracking-wide">Intake</dt>
                            <dd class="mt-0.5 text-sm text-primary dark:text-dark-primary">{{ $student->intake ?? '—' }}</dd>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <dt class="text-xs font-medium text-secondary dark:text-dark-secondary uppercase tracking-wide">Start</dt>
                            <dd class="mt-0.5 text-sm text-primary dark:text-dark-primary">{{ $student->start_date?->format('M Y') ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-secondary dark:text-dark-secondary uppercase tracking-wide">Expected End</dt>
                            <dd class="mt-0.5 text-sm text-primary dark:text-dark-primary">{{ $student->expected_completion?->format('M Y') ?? '—' }}</dd>
                        </div>
                    </div>
                </dl>
            </x-card>

            <x-card title="Overall Progress">
                <div class="text-center py-1">
                    <div class="text-2xl sm:text-3xl font-semibold text-primary dark:text-dark-primary">{{ $student->overall_progress ?? 0 }}%</div>
                    <div class="mt-2 w-full bg-gray-100 dark:bg-dark-border rounded-full h-2">
                        <div class="bg-accent h-2 rounded-full transition-all" style="width: {{ $student->overall_progress ?? 0 }}%"></div>
                    </div>
                    <p class="text-xs text-secondary dark:text-dark-secondary mt-2">Overall progress</p>
                </div>
            </x-card>

            <x-card title="Upcoming Meetings" :padding='false'>
                <div class="divide-y divide-border dark:divide-dark-border">
                    @forelse($student->meetings->where('scheduled_at', '>', now())->sortBy('scheduled_at')->take(3) as $meeting)
                        <a href="{{ route('meetings.show', [$student, $meeting]) }}" class="block px-4 sm:px-5 py-3 hover:bg-surface/60 dark:hover:bg-dark-surface/60 transition-colors active:bg-surface/80">
                            <p class="text-sm font-medium text-primary dark:text-dark-primary truncate">{{ $meeting->title }}</p>
                            <p class="text-xs text-secondary dark:text-dark-secondary mt-0.5">{{ $meeting->scheduled_at?->format('d M Y, H:i') }}</p>
                        </a>
                    @empty
                        <div class="px-4 sm:px-5 py-4 text-center text-xs text-secondary dark:text-dark-secondary">No upcoming meetings</div>
                    @endforelse
                </div>
                <div class="px-4 sm:px-5 py-2.5 border-t border-border dark:border-dark-border">
                    <a href="{{ route('meetings.create', $student) }}" class="text-xs text-accent hover:underline">+ Schedule meeting</a>
                </div>
            </x-card>
        </div>

        {{-- Right column --}}
        <div class="lg:col-span-2 space-y-4">
            <x-card title="Tasks Awaiting Review" :padding='false'>
                <div class="divide-y divide-border dark:divide-dark-border">
                    @forelse($student->tasks->whereIn('status', ['waiting_review', 'submitted'])->take(5) as $task)
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
                        <div class="px-4 sm:px-5 py-6 text-center">
                            <p class="text-sm text-secondary dark:text-dark-secondary">No tasks awaiting review</p>
                        </div>
                    @endforelse
                </div>
                <div class="px-4 sm:px-5 py-2.5 border-t border-border dark:border-dark-border flex flex-col sm:flex-row items-start sm:items-center sm:justify-between gap-2">
                    <a href="{{ route('tasks.index', $student) }}" class="text-xs text-accent hover:underline">View all tasks</a>
                    <a href="{{ route('tasks.create', $student) }}" class="text-xs text-secondary dark:text-dark-secondary hover:text-primary dark:hover:text-dark-primary">+ Assign task</a>
                </div>
            </x-card>

            <x-card title="Recent Progress Reports" :padding='false'>
                <div class="divide-y divide-border dark:divide-dark-border">
                    @forelse($student->progressReports->sortByDesc('submitted_at')->take(5) as $report)
                        <div class="flex items-center justify-between gap-3 px-4 sm:px-5 py-3">
                            <div class="min-w-0">
                                <a href="{{ route('reports.show', [$student, $report]) }}" class="text-sm font-medium text-primary dark:text-dark-primary hover:text-accent transition-colors truncate block">{{ $report->title }}</a>
                                <p class="text-xs text-secondary dark:text-dark-secondary mt-0.5">{{ $report->submitted_at?->format('d M Y') ?? 'Draft' }}</p>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <x-status-badge :status="$report->status" />
                                @if($report->status === 'submitted')
                                    <a href="{{ route('reports.show', [$student, $report]) }}" class="px-2.5 py-1 text-xs font-medium bg-accent text-white rounded hover:bg-amber-600 transition-colors hidden sm:inline-flex">Review</a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="px-4 sm:px-5 py-6 text-center text-sm text-secondary dark:text-dark-secondary">No reports submitted yet</div>
                    @endforelse
                </div>
                <div class="px-4 sm:px-5 py-2.5 border-t border-border dark:border-dark-border">
                    <a href="{{ route('reports.index', $student) }}" class="text-xs text-accent hover:underline">View all reports</a>
                </div>
            </x-card>

            <x-card title="Publication Track" :padding='false'>
                <div class="divide-y divide-border dark:divide-dark-border">
                    @forelse($student->publicationTracks->take(5) as $publication)
                        <div class="flex items-start justify-between gap-3 px-4 sm:px-5 py-3">
                            <div class="min-w-0">
                                <a href="{{ route('publications.index', $student) }}" class="text-sm font-medium text-primary dark:text-dark-primary hover:text-accent transition-colors truncate block">{{ $publication->title }}</a>
                                <p class="text-xs text-secondary dark:text-dark-secondary mt-0.5">{{ $publication->journal }}</p>
                            </div>
                            <span class="inline-flex rounded-full bg-accent/10 px-2 sm:px-2.5 py-0.5 sm:py-1 text-[10px] sm:text-xs font-medium text-accent shrink-0">{{ $publication->stage_label }}</span>
                        </div>
                    @empty
                        <div class="px-4 sm:px-5 py-6 text-center text-sm text-secondary dark:text-dark-secondary">No publication records yet</div>
                    @endforelse
                </div>
                <div class="px-4 sm:px-5 py-2.5 border-t border-border dark:border-dark-border">
                    <a href="{{ route('publications.index', $student) }}" class="text-xs text-accent hover:underline">View publication tracker</a>
                </div>
            </x-card>
        </div>
    </div>
</x-layouts.app>
