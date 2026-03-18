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
                    <p class="text-xs text-secondary mt-2">Research journey completion</p>
                </div>
            </x-card>
        </div>

        {{-- Right column --}}
        <div class="lg:col-span-2 space-y-4">
            {{-- Research journey --}}
            <x-card title="Research Journey" :padding='false'>
                @forelse($student->researchJourneys as $journey)
                    <div class="p-5">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <p class="text-sm font-medium text-primary">{{ $journey->template?->name ?? 'Custom Journey' }}</p>
                                <p class="text-xs text-secondary mt-0.5">{{ $journey->stages->count() }} stages</p>
                            </div>
                            <x-status-badge :status="$journey->status ?? 'in_progress'" />
                        </div>
                        <div class="space-y-3">
                            @foreach($journey->stages->sortBy('order') as $stage)
                                <div class="border border-border rounded-lg overflow-hidden">
                                    <div class="flex items-center justify-between px-4 py-2.5 bg-surface">
                                        <div class="flex items-center gap-2">
                                            <span class="w-5 h-5 rounded-full bg-white border border-border text-xs font-medium text-secondary flex items-center justify-center flex-shrink-0">{{ $stage->order }}</span>
                                            <span class="text-sm font-medium text-primary">{{ $stage->name }}</span>
                                        </div>
                                        <x-status-badge :status="$stage->status ?? 'not_started'" />
                                    </div>
                                    @if($stage->milestones->isNotEmpty())
                                        <div class="divide-y divide-border">
                                            @foreach($stage->milestones->sortBy('order') as $milestone)
                                                <div class="flex items-center justify-between px-4 py-2">
                                                    <div class="flex items-center gap-2.5">
                                                        @if($milestone->completed_at)
                                                            <svg class="w-3.5 h-3.5 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                        @else
                                                            <div class="w-3.5 h-3.5 rounded-full border border-border flex-shrink-0"></div>
                                                        @endif
                                                        <span class="text-xs {{ $milestone->completed_at ? 'text-secondary line-through' : 'text-primary' }}">{{ $milestone->name }}</span>
                                                    </div>
                                                    @if($milestone->due_date)
                                                        <span class="text-xs text-secondary flex-shrink-0">{{ $milestone->due_date->format('d M Y') }}</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-secondary">
                        <svg class="w-8 h-8 mx-auto mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                        <p class="text-sm">No research journey assigned</p>
                    </div>
                @endforelse
            </x-card>

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
        </div>
    </div>
</x-layouts.app>
