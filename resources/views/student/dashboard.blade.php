<x-layouts.app title="My Dashboard" :header="'Dashboard'">
    {{-- Welcome --}}
    <div class="mb-8">
        <h1 class="text-xl font-semibold text-primary">Welcome back, {{ auth()->user()->name }}</h1>
        <p class="text-sm text-secondary mt-1">Your research progress: {{ $student->overall_progress }}% complete</p>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <x-stat-card
            title="Programme"
            :value="$student->programme->code"
            :href="route('tasks.index', $student)"
            icon="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
            variant="info"
        />
        <x-stat-card
            title="Overall Progress"
            :value="$student->overall_progress . '%'"
            icon="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
            :variant="$student->overall_progress >= 75 ? 'success' : ($student->overall_progress >= 40 ? 'accent' : 'default')"
        />
        <x-stat-card
            title="Total Tasks"
            :value="$tasks->count()"
            :href="route('tasks.index', $student)"
            icon="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
            variant="default"
        />
        <x-stat-card
            title="Completed"
            :value="$tasks->where('status', 'completed')->count()"
            icon="M5 13l4 4L19 7"
            variant="success"
        />
    </div>

    {{-- Research info card --}}
    <x-card class="mb-6" :padding="'loose'">
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
                <p class="text-sm text-primary mt-1">{{ $student->start_date?->format('d M Y') ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs font-medium text-secondary uppercase tracking-wide">Expected Completion</p>
                <p class="text-sm text-primary mt-1">{{ $student->expected_completion?->format('d M Y') ?? '—' }}</p>
            </div>
        </div>
    </x-card>

    <div class="grid lg:grid-cols-2 gap-6">
        {{-- Upcoming tasks --}}
        <x-card title="Upcoming Tasks" :action="'View All'">
            <div class="space-y-1 -mx-2">
                @forelse($upcomingTasks as $task)
                    <a href="{{ route('tasks.show', [$student, $task]) }}" class="flex items-center justify-between p-3 rounded-lg hover:bg-surface transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg @if($task->status === 'in_progress') bg-accent/10 text-accent @elseif($task->status === 'waiting_review') bg-warning/10 text-warning @else bg-surface text-secondary @endif flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-primary">{{ $task->title }}</p>
                                <p class="text-xs text-secondary">Due {{ $task->due_date?->format('M d') ?? 'No date' }}</p>
                            </div>
                        </div>
                        <x-status-badge :status="$task->status" size="sm" />
                    </a>
                @empty
                    <p class="text-sm text-secondary text-center py-4">No upcoming tasks</p>
                @endforelse
            </div>
            <x-slot:action>
                <a href="{{ route('tasks.index', $student) }}" class="text-xs text-accent hover:text-amber-700 font-medium">View all</a>
            </x-slot:action>
        </x-card>

        {{-- Research journey --}}
        <x-card title="Research Journey">
            @forelse($student->researchJourleys as $journey)
                <div class="mb-4">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-sm font-medium text-primary">{{ $journey->name }}</p>
                        <x-status-badge :status="$journey->status" size="sm" />
                    </div>
                    <div class="space-y-3">
                        @foreach($journey->stages as $stage)
                            <div>
                                <div class="flex items-center gap-2 mb-2">
                                    <div class="w-2.5 h-2.5 rounded-full @if($stage->status === 'completed') bg-success @elseif($stage->status === 'in_progress') bg-accent @else bg-tertiary @endif"></div>
                                    <p class="text-xs font-medium text-primary">{{ $stage->name }}</p>
                                    <span class="text-[10px] text-tertiary ml-auto">{{ $stage->milestones->where('status', 'completed')->count() }}/{{ $stage->milestones->count() }}</span>
                                </div>
                                <div class="ml-4 space-y-1">
                                    @foreach($stage->milestones as $milestone)
                                        <div class="flex items-center gap-2 py-0.5">
                                            <div class="w-1.5 h-1.5 rounded-full @if($milestone->status === 'completed') bg-success/70 @else bg-tertiary @endif"></div>
                                            <span class="text-xs text-secondary @if($milestone->status === 'completed') line-through opacity-60 @endif">{{ $milestone->name }}</span>
                                            @if($milestone->due_date)
                                                <span class="text-[10px] text-tertiary ml-auto">{{ $milestone->due_date->format('M d') }}</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="text-sm text-secondary text-center py-4">No journey assigned yet</p>
            @endforelse
        </x-card>

        {{-- Recent reports --}}
        <x-card title="Recent Reports" :action="'View All'">
            <div class="space-y-1 -mx-2">
                @forelse($recentReports as $report)
                    <a href="{{ route('reports.show', [$student, $report]) }}" class="flex items-center justify-between p-3 rounded-lg hover:bg-surface transition-colors">
                        <div>
                            <p class="text-sm font-medium text-primary">{{ $report->title }}</p>
                            <p class="text-xs text-secondary">{{ $report->created_at->diffForHumans() }}</p>
                        </div>
                        <x-status-badge :status="$report->status" size="sm" />
                    </a>
                @empty
                    <p class="text-sm text-secondary text-center py-4">No reports yet</p>
                @endforelse
            </div>
            <x-slot:action>
                <a href="{{ route('reports.index', $student) }}" class="text-xs text-accent hover:text-amber-700 font-medium">View all</a>
            </x-slot:action>
        </x-card>

        {{-- Upcoming meetings --}}
        <x-card title="Upcoming Meetings" :action="'Schedule'">
            <div class="space-y-1 -mx-2">
                @forelse($upcomingMeetings as $meeting)
                    <a href="{{ route('meetings.show', [$student, $meeting]) }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-surface transition-colors">
                        <div class="w-10 h-10 rounded-lg bg-info/10 text-info flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-primary">{{ $meeting->title }}</p>
                            <p class="text-xs text-secondary">{{ $meeting->scheduled_at->format('M d, Y · g:i A') }}</p>
                        </div>
                    </a>
                @empty
                    <p class="text-sm text-secondary text-center py-4">No upcoming meetings</p>
                @endforelse
            </div>
            <x-slot:action>
                <a href="{{ route('meetings.create', $student) }}" class="text-xs text-accent hover:text-amber-700 font-medium">Schedule</a>
            </x-slot:action>
        </x-card>
    </div>
</x-layouts.app>
