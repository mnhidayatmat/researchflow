<x-layouts.app :title="$student->user->name">
    <x-slot:header>Student Overview</x-slot:header>

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-1.5 text-xs text-secondary mb-5">
        <a href="{{ route('supervisor.students.index') }}" class="hover:text-primary transition-colors">My Students</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-primary">{{ $student->user->name }}</span>
    </nav>

    {{-- Profile header --}}
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-full bg-accent/10 text-accent flex items-center justify-center text-xl font-semibold flex-shrink-0">
                {{ substr($student->user->name, 0, 1) }}
            </div>
            <div>
                <h2 class="text-lg font-semibold text-primary">{{ $student->user->name }}</h2>
                <div class="flex flex-wrap items-center gap-2 mt-1">
                    <span class="text-xs text-secondary font-mono">{{ $student->user->matric_number ?? 'No matric' }}</span>
                    <span class="text-secondary text-xs">&middot;</span>
                    <span class="text-xs text-secondary">{{ $student->programme->name ?? '—' }}</span>
                    <span class="text-secondary text-xs">&middot;</span>
                    <x-status-badge :status="$student->status" />
                </div>
            </div>
        </div>
    </div>

    {{-- Quick action links --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
        <a href="{{ route('tasks.index', $student) }}" class="group flex flex-col items-center gap-2 p-4 bg-white border border-border rounded-lg hover:border-accent/40 hover:shadow-sm transition-all text-center">
            <div class="w-9 h-9 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center group-hover:bg-blue-100 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-primary">Tasks</p>
                <p class="text-[10px] text-secondary">{{ $student->tasks->count() }} total</p>
            </div>
        </a>
        <a href="{{ route('reports.index', $student) }}" class="group flex flex-col items-center gap-2 p-4 bg-white border border-border rounded-lg hover:border-accent/40 hover:shadow-sm transition-all text-center">
            <div class="w-9 h-9 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center group-hover:bg-purple-100 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-primary">Reports</p>
                <p class="text-[10px] text-secondary">{{ $student->progressReports->count() }} submitted</p>
            </div>
        </a>
        <a href="{{ route('meetings.index', $student) }}" class="group flex flex-col items-center gap-2 p-4 bg-white border border-border rounded-lg hover:border-accent/40 hover:shadow-sm transition-all text-center">
            <div class="w-9 h-9 rounded-lg bg-green-50 text-green-600 flex items-center justify-center group-hover:bg-green-100 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-primary">Meetings</p>
                <p class="text-[10px] text-secondary">{{ $student->meetings->count() }} total</p>
            </div>
        </a>
        <a href="{{ route('files.index', $student) }}" class="group flex flex-col items-center gap-2 p-4 bg-white border border-border rounded-lg hover:border-accent/40 hover:shadow-sm transition-all text-center">
            <div class="w-9 h-9 rounded-lg bg-orange-50 text-orange-600 flex items-center justify-center group-hover:bg-orange-100 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-primary">Files</p>
                <p class="text-[10px] text-secondary">View all files</p>
            </div>
        </a>
        <a href="{{ route('publications.index', $student) }}" class="group flex flex-col items-center gap-2 p-4 bg-white border border-border rounded-lg hover:border-accent/40 hover:shadow-sm transition-all text-center">
            <div class="w-9 h-9 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center group-hover:bg-amber-100 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-primary">Publications</p>
                <p class="text-[10px] text-secondary">{{ $student->publicationTracks->count() }} tracked</p>
            </div>
        </a>
    </div>

    <div class="grid lg:grid-cols-3 gap-5">
        {{-- Left column --}}
        <div class="space-y-4">
            {{-- Student info --}}
            <x-card title="Student Information">
                <dl class="space-y-3">
                    <div>
                        <dt class="text-xs font-medium text-secondary uppercase tracking-wide">Email</dt>
                        <dd class="mt-0.5 text-sm text-primary">{{ $student->user->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-secondary uppercase tracking-wide">Research Title</dt>
                        <dd class="mt-0.5 text-sm text-primary">{{ $student->research_title ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-secondary uppercase tracking-wide">Co-Supervisor</dt>
                        <dd class="mt-0.5 text-sm text-primary">{{ $student->cosupervisor?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-secondary uppercase tracking-wide">Intake</dt>
                        <dd class="mt-0.5 text-sm text-primary">{{ $student->intake ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-secondary uppercase tracking-wide">Start Date</dt>
                        <dd class="mt-0.5 text-sm text-primary">{{ $student->start_date?->format('d M Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-secondary uppercase tracking-wide">Expected Completion</dt>
                        <dd class="mt-0.5 text-sm text-primary">{{ $student->expected_completion?->format('d M Y') ?? '—' }}</dd>
                    </div>
                </dl>
            </x-card>

            {{-- Progress --}}
            <x-card title="Overall Progress">
                <div class="text-center py-1">
                    <div class="text-3xl font-semibold text-primary">{{ $student->overall_progress ?? 0 }}%</div>
                    <div class="mt-2 w-full bg-gray-100 rounded-full h-2">
                        <div class="bg-accent h-2 rounded-full transition-all" style="width: {{ $student->overall_progress ?? 0 }}%"></div>
                    </div>
                    <p class="text-xs text-secondary mt-2">Overall progress</p>
                </div>
            </x-card>

            {{-- Upcoming meetings --}}
            <x-card title="Upcoming Meetings" :padding='false'>
                <div class="divide-y divide-border">
                    @forelse($student->meetings->where('scheduled_at', '>', now())->sortBy('scheduled_at')->take(3) as $meeting)
                        <a href="{{ route('meetings.show', [$student, $meeting]) }}" class="block px-5 py-3 hover:bg-surface/60 transition-colors">
                            <p class="text-sm font-medium text-primary">{{ $meeting->title }}</p>
                            <p class="text-xs text-secondary mt-0.5">{{ $meeting->scheduled_at?->format('d M Y, H:i') }}</p>
                        </a>
                    @empty
                        <div class="px-5 py-4 text-center text-xs text-secondary">No upcoming meetings</div>
                    @endforelse
                </div>
                <div class="px-5 py-2.5 border-t border-border">
                    <a href="{{ route('meetings.create', $student) }}" class="text-xs text-accent hover:underline">+ Schedule meeting</a>
                </div>
            </x-card>
        </div>

        {{-- Right column --}}
        <div class="lg:col-span-2 space-y-4">
            {{-- Tasks needing attention --}}
            <x-card title="Tasks Awaiting Review" :padding='false'>
                <div class="divide-y divide-border">
                    @forelse($student->tasks->whereIn('status', ['waiting_review', 'submitted'])->take(5) as $task)
                        <div class="flex items-center justify-between px-5 py-3">
                            <div>
                                <a href="{{ route('tasks.show', [$student, $task]) }}" class="text-sm font-medium text-primary hover:text-accent transition-colors">{{ $task->title }}</a>
                                @if($task->due_date)
                                    <p class="text-xs text-secondary mt-0.5">Due {{ $task->due_date->format('d M Y') }}</p>
                                @endif
                            </div>
                            <x-status-badge :status="$task->status" />
                        </div>
                    @empty
                        <div class="px-5 py-6 text-center">
                            <p class="text-sm text-secondary">No tasks awaiting review</p>
                        </div>
                    @endforelse
                </div>
                <div class="px-5 py-2.5 border-t border-border flex items-center justify-between">
                    <a href="{{ route('tasks.index', $student) }}" class="text-xs text-accent hover:underline">View all tasks</a>
                    <a href="{{ route('tasks.create', $student) }}" class="text-xs text-secondary hover:text-primary">+ Assign task</a>
                </div>
            </x-card>

            {{-- Recent progress reports --}}
            <x-card title="Recent Progress Reports" :padding='false'>
                <div class="divide-y divide-border">
                    @forelse($student->progressReports->sortByDesc('submitted_at')->take(5) as $report)
                        <div class="flex items-center justify-between px-5 py-3">
                            <div>
                                <a href="{{ route('reports.show', [$student, $report]) }}" class="text-sm font-medium text-primary hover:text-accent transition-colors">{{ $report->title }}</a>
                                <p class="text-xs text-secondary mt-0.5">{{ $report->submitted_at?->format('d M Y') ?? 'Draft' }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <x-status-badge :status="$report->status" />
                                @if($report->status === 'submitted')
                                    <a href="{{ route('reports.show', [$student, $report]) }}" class="px-2.5 py-1 text-xs font-medium bg-accent text-white rounded hover:bg-amber-600 transition-colors">Review</a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-6 text-center text-sm text-secondary">No reports submitted yet</div>
                    @endforelse
                </div>
                <div class="px-5 py-2.5 border-t border-border">
                    <a href="{{ route('reports.index', $student) }}" class="text-xs text-accent hover:underline">View all reports</a>
                </div>
            </x-card>

            <x-card title="Publication Track" :padding='false'>
                <div class="divide-y divide-border">
                    @forelse($student->publicationTracks->take(5) as $publication)
                        <div class="flex items-start justify-between px-5 py-3 gap-4">
                            <div>
                                <a href="{{ route('publications.index', $student) }}" class="text-sm font-medium text-primary hover:text-accent transition-colors">{{ $publication->title }}</a>
                                <p class="text-xs text-secondary mt-0.5">{{ $publication->journal }}</p>
                            </div>
                            <span class="inline-flex rounded-full bg-accent/10 px-2.5 py-1 text-xs font-medium text-accent">{{ $publication->stage_label }}</span>
                        </div>
                    @empty
                        <div class="px-5 py-6 text-center text-sm text-secondary">No publication records yet</div>
                    @endforelse
                </div>
                <div class="px-5 py-2.5 border-t border-border">
                    <a href="{{ route('publications.index', $student) }}" class="text-xs text-accent hover:underline">View publication tracker</a>
                </div>
            </x-card>
        </div>
    </div>
</x-layouts.app>
