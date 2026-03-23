<x-layouts.app :title="$student->user->name">
    <x-slot:header>Student Profile</x-slot:header>

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-1.5 text-xs text-secondary mb-5">
        <a href="{{ route('admin.students.index') }}" class="hover:text-primary transition-colors">Students</a>
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
                    <span class="text-xs text-secondary">{{ $student->user->email }}</span>
                    <span class="text-secondary text-xs">&middot;</span>
                    <x-status-badge :status="$student->status" />
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <x-button href="{{ route('admin.students.edit', $student) }}" variant="secondary" size="sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit
            </x-button>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-5">
        {{-- Left column --}}
        <div class="space-y-4">
            {{-- Student info --}}
            <x-card title="Student Information">
                <dl class="space-y-3">
                    <div>
                        <dt class="text-xs font-medium text-secondary uppercase tracking-wide">Programme</dt>
                        <dd class="mt-0.5 text-sm text-primary">{{ $student->programme->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-secondary uppercase tracking-wide">Research Title</dt>
                        <dd class="mt-0.5 text-sm text-primary">{{ $student->research_title ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-secondary uppercase tracking-wide">Main Supervisor</dt>
                        <dd class="mt-0.5 text-sm text-primary">{{ $student->supervisor?->name ?? '—' }}</dd>
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

            {{-- Overall progress --}}
            <x-card title="Overall Progress">
                <div class="text-center py-2">
                    <div class="text-3xl font-semibold text-primary">{{ $student->overall_progress ?? 0 }}%</div>
                    <div class="mt-2 w-full bg-gray-100 rounded-full h-2">
                        <div class="bg-accent h-2 rounded-full transition-all" style="width: {{ $student->overall_progress ?? 0 }}%"></div>
                    </div>
                    <p class="text-xs text-secondary mt-2">Overall progress</p>
                </div>
            </x-card>

            <x-card title="Publication Tracking">
                <div class="text-center py-2">
                    <div class="text-3xl font-semibold text-primary">{{ $student->publicationTracks->count() }}</div>
                    <p class="text-xs text-secondary mt-2">Journal records tracked</p>
                    <div class="mt-4">
                        <x-button :href="route('publications.index', $student)" variant="secondary" size="sm">Open Tracker</x-button>
                    </div>
                </div>
            </x-card>
        </div>

        {{-- Right column --}}
        <div class="lg:col-span-2 space-y-4">
            {{-- Recent tasks --}}
            <x-card title="Recent Tasks" :padding='false'>
                <div class="divide-y divide-border">
                    @forelse($student->tasks->take(5) as $task)
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
                        <div class="px-5 py-6 text-center text-sm text-secondary">No tasks yet</div>
                    @endforelse
                </div>
                @if($student->tasks->count() > 5)
                    <div class="px-5 py-3 border-t border-border">
                        <a href="{{ route('tasks.index', $student) }}" class="text-xs text-accent hover:underline">View all {{ $student->tasks->count() }} tasks</a>
                    </div>
                @endif
            </x-card>

            {{-- Recent progress reports --}}
            <x-card title="Recent Progress Reports" :padding='false'>
                <div class="divide-y divide-border">
                    @forelse($student->progressReports->take(5) as $report)
                        <div class="flex items-center justify-between px-5 py-3">
                            <div>
                                <a href="{{ route('reports.show', [$student, $report]) }}" class="text-sm font-medium text-primary hover:text-accent transition-colors">{{ $report->title }}</a>
                                <p class="text-xs text-secondary mt-0.5">{{ $report->submitted_at?->format('d M Y') ?? 'Not submitted' }}</p>
                            </div>
                            <x-status-badge :status="$report->status" />
                        </div>
                    @empty
                        <div class="px-5 py-6 text-center text-sm text-secondary">No reports submitted</div>
                    @endforelse
                </div>
                @if($student->progressReports->count() > 5)
                    <div class="px-5 py-3 border-t border-border">
                        <a href="{{ route('reports.index', $student) }}" class="text-xs text-accent hover:underline">View all {{ $student->progressReports->count() }} reports</a>
                    </div>
                @endif
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
                @if($student->publicationTracks->count() > 5)
                    <div class="px-5 py-3 border-t border-border">
                        <a href="{{ route('publications.index', $student) }}" class="text-xs text-accent hover:underline">View all {{ $student->publicationTracks->count() }} publication records</a>
                    </div>
                @else
                    <div class="px-5 py-3 border-t border-border">
                        <a href="{{ route('publications.index', $student) }}" class="text-xs text-accent hover:underline">Open publication tracker</a>
                    </div>
                @endif
            </x-card>
        </div>
    </div>
</x-layouts.app>
