<x-layouts.app title="My Students">
    <x-slot:header>My Students</x-slot:header>

    @php
        $target = request('target');
        $targetLabel = match ($target) {
            'tasks' => 'tasks',
            'reports' => 'reports',
            'meetings' => 'meetings',
            'publications' => 'publications',
            default => null,
        };
        $pageTitle = match ($target) {
            'tasks' => 'Select Student for Tasks',
            'reports' => 'Select Student for Reports',
            'meetings' => 'Select Student for Meetings',
            'publications' => 'Select Student for Publications',
            default => 'My Students',
        };
        $targetMeta = match ($target) {
            'tasks' => [
                'verb' => 'Open task workspace',
                'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
                'accent' => 'from-amber-50 to-orange-100 text-amber-700 border-amber-200',
                'button' => 'Open Tasks',
            ],
            'reports' => [
                'verb' => 'Review submitted reports',
                'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                'accent' => 'from-blue-50 to-sky-100 text-sky-700 border-sky-200',
                'button' => 'Open Reports',
            ],
            'meetings' => [
                'verb' => 'View and schedule meetings',
                'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
                'accent' => 'from-emerald-50 to-teal-100 text-teal-700 border-teal-200',
                'button' => 'Open Meetings',
            ],
            'publications' => [
                'verb' => 'Track journal submissions and manuscript progress',
                'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
                'accent' => 'from-violet-50 to-fuchsia-100 text-fuchsia-700 border-fuchsia-200',
                'button' => 'Open Publications',
            ],
            default => null,
        };
    @endphp

    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-base font-semibold text-primary">{{ $pageTitle }}</h2>
            <p class="mt-0.5 text-xs text-secondary">
                @if($targetLabel)
                    Choose a student to continue to their {{ $targetLabel }} workspace
                @else
                    Students under your supervision
                @endif
            </p>
        </div>
    </div>

    @if($targetLabel)
        <div class="mb-5 rounded-2xl border border-border bg-gradient-to-r from-white via-surface/60 to-white px-5 py-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl border bg-gradient-to-br {{ $targetMeta['accent'] }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="{{ $targetMeta['icon'] }}"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-primary">{{ ucfirst($targetLabel) }} Picker</p>
                        <p class="mt-0.5 text-xs text-secondary">{{ $targetMeta['verb'] }}. Student details stay in the main My Students page.</p>
                    </div>
                </div>
                <a href="{{ route('supervisor.students.index') }}" class="inline-flex items-center gap-2 text-xs font-medium text-secondary hover:text-primary">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back to My Students
                </a>
            </div>
        </div>
    @endif

    <x-card class="mb-4">
        <form method="GET" action="{{ route('supervisor.students.index') }}" class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end">
            @if($target)
                <input type="hidden" name="target" value="{{ $target }}">
            @endif
            <div class="w-full min-w-0 flex-1 sm:min-w-[220px]">
                <label class="mb-1 block text-xs font-medium text-secondary dark:text-dark-secondary">Search</label>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="{{ $targetLabel ? 'Search by student name...' : 'Search by name or research title...' }}"
                    class="w-full rounded-lg border border-border dark:border-dark-border bg-white dark:bg-dark-card px-3 py-2.5 sm:py-2 text-sm text-primary dark:text-dark-primary placeholder-secondary/50 outline-none transition-colors focus:border-accent focus:ring-1 focus:ring-accent/30"
                >
            </div>
            <div class="grid grid-cols-2 gap-3 sm:contents">
                <div class="sm:min-w-[140px]">
                    <label class="mb-1 block text-xs font-medium text-secondary dark:text-dark-secondary">Programme</label>
                    <select name="programme_id" class="w-full rounded-lg border border-border dark:border-dark-border bg-white dark:bg-dark-card px-3 py-2.5 sm:py-2 text-sm text-primary dark:text-dark-primary outline-none transition-colors focus:border-accent focus:ring-1 focus:ring-accent/30">
                        <option value="">All</option>
                        @foreach($students->pluck('programme')->unique('id')->filter() as $programme)
                            <option value="{{ $programme->id }}" {{ request('programme_id') == $programme->id ? 'selected' : '' }}>
                                {{ $programme->code }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:min-w-[130px]">
                    <label class="mb-1 block text-xs font-medium text-secondary dark:text-dark-secondary">Status</label>
                    <select name="status" class="w-full rounded-lg border border-border dark:border-dark-border bg-white dark:bg-dark-card px-3 py-2.5 sm:py-2 text-sm text-primary dark:text-dark-primary outline-none transition-colors focus:border-accent focus:ring-1 focus:ring-accent/30">
                        <option value="">All</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="on_hold" {{ request('status') === 'on_hold' ? 'selected' : '' }}>On Hold</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>
            </div>
            <div class="flex w-full gap-2 sm:w-auto">
                <x-button type="submit" variant="primary" size="sm" class="flex-1 justify-center sm:flex-none">Filter</x-button>
                @if(request()->hasAny(['search', 'programme_id', 'status']))
                    <x-button href="{{ route('supervisor.students.index', array_filter(['target' => $target])) }}" variant="secondary" size="sm" class="flex-1 justify-center sm:flex-none">Clear</x-button>
                @endif
            </div>
        </form>
    </x-card>

    @if($pendingApprovals->count() > 0)
    <div class="bg-card dark:bg-dark-card rounded-2xl border border-warning/40 overflow-hidden mb-6">
        <div class="px-4 sm:px-6 py-4 sm:py-5 border-b border-warning/20 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-warning/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-primary dark:text-dark-primary">Pending Student Approvals</h3>
                    <p class="text-[10px] text-secondary dark:text-dark-secondary mt-0.5">Students awaiting your approval to join your supervision</p>
                </div>
            </div>
            <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-warning/10 text-warning text-xs font-semibold">
                <div class="w-1.5 h-1.5 rounded-full bg-warning animate-pulse"></div>
                {{ $pendingApprovals->count() }}
            </div>
        </div>
        <div class="divide-y divide-border dark:divide-dark-border">
            @foreach($pendingApprovals as $pending)
            @php
                $isMainSv = $pending->supervisor_id === auth()->id();
                $roleLabel = $isMainSv ? 'Supervisor' : 'Co-Supervisor';
                $approveUrl = URL::temporarySignedRoute('supervisor.student.approve', now()->addDays(7), ['student' => $pending->id, 'role' => $isMainSv ? 'supervisor' : 'cosupervisor']);
                $denyUrl    = URL::temporarySignedRoute('supervisor.student.deny',   now()->addDays(7), ['student' => $pending->id, 'role' => $isMainSv ? 'supervisor' : 'cosupervisor']);
            @endphp
            <div class="flex items-center gap-3 sm:gap-4 p-4 sm:p-5">
                <div class="relative shrink-0">
                    <x-avatar :name="$pending->user->name" size="md" />
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-primary dark:text-dark-primary truncate">{{ $pending->user->name }}</p>
                    <p class="text-xs text-secondary dark:text-dark-secondary">{{ $pending->programme_name ?? '—' }}</p>
                    <span class="inline-block text-[10px] font-medium px-2 py-0.5 rounded-full bg-warning/10 text-warning mt-1">{{ $roleLabel }}</span>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ $approveUrl }}"
                       class="px-3 py-1.5 rounded-lg bg-success/10 text-success text-xs font-semibold hover:bg-success/20 transition-colors">
                        Approve
                    </a>
                    <a href="{{ $denyUrl }}"
                       class="px-3 py-1.5 rounded-lg bg-surface dark:bg-dark-surface text-secondary dark:text-dark-secondary text-xs font-medium hover:bg-border dark:hover:bg-dark-border transition-colors">
                        Decline
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @if($students->isEmpty())
        <x-card>
            <div class="py-12 text-center text-secondary">
                <svg class="mx-auto mb-3 h-10 w-10 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <p class="text-sm font-medium">No students assigned</p>
                <p class="mt-1 text-xs">Students will appear here once assigned to you</p>
            </div>
        </x-card>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($students as $student)
                @php
                    $studentRoute = match ($target) {
                        'tasks' => route('tasks.index', $student),
                        'reports' => route('reports.index', $student),
                        'meetings' => route('meetings.index', $student),
                        'publications' => route('publications.index', $student),
                        default => route('supervisor.students.show', $student),
                    };
                @endphp

                <a href="{{ $studentRoute }}" class="group block">
                    @if($targetLabel)
                        <x-card class="h-full border border-border/80 transition-all hover:border-accent/30 hover:shadow-sm">
                            <div class="flex items-center gap-3">
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-surface text-sm font-semibold text-accent">
                                    {{ substr($student->user->name, 0, 1) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-primary transition-colors group-hover:text-accent">{{ $student->user->name }}</p>
                                    <div class="mt-1 flex items-center gap-2 text-xs text-secondary">
                                        <span class="truncate">{{ $student->programme?->code ?? ($student->programme_name ?? 'No programme') }}</span>
                                        <span class="text-border">•</span>
                                        <span class="font-mono">{{ $student->user->matric_number ?? 'No matric' }}</span>
                                    </div>
                                </div>
                                <x-status-badge :status="$student->status" size="sm" />
                            </div>

                            <div class="mt-4 flex items-center justify-between rounded-xl bg-surface/80 px-3 py-2.5">
                                <div>
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-tertiary">{{ ucfirst($targetLabel) }}</p>
                                    <p class="mt-1 text-xs text-secondary">{{ $targetMeta['verb'] }}</p>
                                </div>
                                <div class="flex h-9 w-9 items-center justify-center rounded-xl border bg-white text-secondary transition-colors group-hover:border-accent/30 group-hover:text-accent">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </div>
                            </div>

                            <div class="mt-4 flex items-center justify-between border-t border-border pt-3">
                                <span class="text-xs text-secondary">Continue to {{ $targetLabel }}</span>
                                <span class="text-xs font-semibold text-primary transition-colors group-hover:text-accent">{{ $targetMeta['button'] }}</span>
                            </div>
                        </x-card>
                    @else
                        <x-card class="h-full transition-all hover:border-gray-300 hover:shadow-sm">
                            <div class="mb-4 flex items-start gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-accent/10 text-sm font-semibold text-accent">
                                    {{ substr($student->user->name, 0, 1) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-primary transition-colors group-hover:text-accent">{{ $student->user->name }}</p>
                                    <p class="font-mono text-xs text-secondary">{{ $student->user->matric_number ?? 'No matric' }}</p>
                                </div>
                                <x-status-badge :status="$student->status" />
                            </div>

                            <div class="mb-4 space-y-2">
                                <div>
                                    <p class="text-[10px] font-medium uppercase tracking-wide text-secondary">Programme</p>
                                    <p class="mt-0.5 text-xs text-primary">{{ $student->programme?->name ?? ($student->programme_name ?? '—') }}</p>
                                </div>
                                @if($student->research_title)
                                    <div>
                                        <p class="text-[10px] font-medium uppercase tracking-wide text-secondary">Research Title</p>
                                        <p class="mt-0.5 line-clamp-2 text-xs text-primary">{{ $student->research_title }}</p>
                                    </div>
                                @endif
                            </div>

                            <div>
                                <div class="mb-1 flex items-center justify-between">
                                    <span class="text-[10px] font-medium uppercase tracking-wide text-secondary">Progress</span>
                                    <span class="text-xs font-medium text-primary">{{ $student->overall_progress ?? 0 }}%</span>
                                </div>
                                <div class="h-1.5 w-full rounded-full bg-gray-100">
                                    <div class="h-1.5 rounded-full bg-accent transition-all" style="width: {{ $student->overall_progress ?? 0 }}%"></div>
                                </div>
                            </div>

                            <div class="mt-3 flex items-center justify-between border-t border-border pt-3">
                                <span class="text-xs text-secondary">Expected completion</span>
                                <span class="text-xs font-medium text-primary">{{ $student->expected_completion?->format('M Y') ?? 'N/A' }}</span>
                            </div>
                        </x-card>
                    @endif
                </a>
            @endforeach
        </div>

        @if($students->hasPages())
            <div class="mt-4">
                {{ $students->withQueryString()->links() }}
            </div>
        @endif
    @endif
</x-layouts.app>
