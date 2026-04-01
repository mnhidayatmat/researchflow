@use('Illuminate\Support\Facades\URL')
<x-layouts.app title="Supervisor Dashboard" :header="'Dashboard'">
    <div class="space-y-5 sm:space-y-6">
        {{-- Welcome --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-xl sm:text-2xl font-semibold text-primary dark:text-dark-primary">Welcome back, {{ auth()->user()->name }}</h2>
                <p class="text-xs text-secondary dark:text-dark-secondary mt-0.5">You have {{ $stats['pending_reviews'] }} item{{ $stats['pending_reviews'] !== 1 ? 's' : '' }} awaiting your review.</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs text-tertiary dark:text-dark-tertiary hidden sm:inline">{{ now()->format('l, F j, Y') }}</span>
                <span class="text-xs text-tertiary dark:text-dark-tertiary sm:hidden">{{ now()->format('M j, Y') }}</span>
            </div>
        </div>

        {{-- Stats - 2x2 on mobile --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            {{-- Total Students --}}
            <div class="group relative bg-card dark:bg-dark-card rounded-2xl p-4 sm:p-6 border border-border dark:border-dark-border hover:border-accent/30 hover:shadow-soft transition-all duration-300">
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-gradient-to-br from-accent/20 to-accent/10 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <div class="mt-3 sm:mt-4">
                    <p class="text-lg font-bold text-primary dark:text-dark-primary">{{ $stats['total_students'] }}</p>
                    <p class="text-[10px] sm:text-xs text-secondary dark:text-dark-secondary mt-0.5">Students</p>
                </div>
            </div>

            {{-- Active Students --}}
            <div class="group relative bg-card dark:bg-dark-card rounded-2xl p-4 sm:p-6 border border-border dark:border-dark-border hover:border-success/30 hover:shadow-soft transition-all duration-300">
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-gradient-to-br from-success/20 to-success/10 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="mt-3 sm:mt-4">
                    <p class="text-lg font-bold text-primary dark:text-dark-primary">{{ $stats['active_students'] }}</p>
                    <p class="text-[10px] sm:text-xs text-secondary dark:text-dark-secondary mt-0.5">Active</p>
                </div>
            </div>

            {{-- Pending Reviews --}}
            <div class="group relative bg-card dark:bg-dark-card rounded-2xl p-4 sm:p-6 border border-border dark:border-dark-border hover:border-warning/30 hover:shadow-soft transition-all duration-300">
                <div class="flex items-start justify-between">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-gradient-to-br from-warning/20 to-warning/10 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    @if($stats['pending_reviews'] > 0)
                    <div class="w-2.5 h-2.5 rounded-full bg-warning animate-pulse"></div>
                    @endif
                </div>
                <div class="mt-3 sm:mt-4">
                    <p class="text-lg font-bold text-primary dark:text-dark-primary">{{ $stats['pending_reviews'] }}</p>
                    <p class="text-[10px] sm:text-xs text-secondary dark:text-dark-secondary mt-0.5">Pending</p>
                </div>
            </div>

            {{-- Tasks to Review --}}
            <div class="group relative bg-card dark:bg-dark-card rounded-2xl p-4 sm:p-6 border border-border dark:border-dark-border hover:border-info/30 hover:shadow-soft transition-all duration-300">
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-gradient-to-br from-info/20 to-info/10 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                </div>
                <div class="mt-3 sm:mt-4">
                    <p class="text-lg font-bold text-primary dark:text-dark-primary">{{ $stats['tasks_waiting_review'] }}</p>
                    <p class="text-[10px] sm:text-xs text-secondary dark:text-dark-secondary mt-0.5">To Review</p>
                </div>
            </div>
        </div>

        {{-- Pending Student Approvals --}}
        @if($pendingApprovals->count() > 0)
        <div class="bg-card dark:bg-dark-card rounded-2xl border border-warning/40 overflow-hidden">
            <div class="px-4 sm:px-6 py-4 sm:py-5 border-b border-warning/20 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-warning/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-primary dark:text-dark-primary">Pending Student Approvals</h3>
                        <p class="text-[10px] text-secondary dark:text-dark-secondary mt-0.5">Students awaiting your approval</p>
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

        {{-- Mobile Quick Actions --}}
        <div class="flex gap-2 overflow-x-auto pb-1 -mx-4 px-4 sm:mx-0 sm:px-0 sm:overflow-visible sm:flex-wrap scrollbar-thin lg:hidden">
            <a href="{{ route('supervisor.students.index') }}" class="flex items-center gap-2 px-4 py-2.5 rounded-xl border border-border dark:border-dark-border bg-card dark:bg-dark-card text-sm font-medium text-secondary dark:text-dark-secondary hover:text-accent hover:border-accent/30 transition-all whitespace-nowrap shrink-0 active:scale-95">
                <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                My Students
            </a>
            <a href="{{ route('supervisor.grants.index') }}" class="flex items-center gap-2 px-4 py-2.5 rounded-xl border border-border dark:border-dark-border bg-card dark:bg-dark-card text-sm font-medium text-secondary dark:text-dark-secondary hover:text-info hover:border-info/30 transition-all whitespace-nowrap shrink-0 active:scale-95">
                <svg class="w-4 h-4 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 6V4a3 3 0 013-3h0a3 3 0 013 3v2m-9 0h10a2 2 0 012 2v9a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2h0z"/></svg>
                Grants
            </a>
            <a href="{{ route('supervisor.publications.index') }}" class="flex items-center gap-2 px-4 py-2.5 rounded-xl border border-border dark:border-dark-border bg-card dark:bg-dark-card text-sm font-medium text-secondary dark:text-dark-secondary hover:text-warning hover:border-warning/30 transition-all whitespace-nowrap shrink-0 active:scale-95">
                <svg class="w-4 h-4 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                Publications
            </a>
            <a href="{{ route('ai.chat') }}" class="flex items-center gap-2 px-4 py-2.5 rounded-xl border border-border dark:border-dark-border bg-card dark:bg-dark-card text-sm font-medium text-secondary dark:text-dark-secondary hover:text-success hover:border-success/30 transition-all whitespace-nowrap shrink-0 active:scale-95">
                <svg class="w-4 h-4 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                AI Assistant
            </a>
        </div>

    <div class="grid lg:grid-cols-3 gap-5 sm:gap-6">
        {{-- Students list --}}
        <div class="lg:col-span-2 bg-card dark:bg-dark-card rounded-2xl border border-border dark:border-dark-border overflow-hidden">
            <div class="px-4 sm:px-6 py-4 sm:py-5 border-b border-border dark:border-dark-border flex items-center justify-between">
                <div>
                    <h3 class="text-sm sm:text-base font-semibold text-primary dark:text-dark-primary">My Students</h3>
                    <p class="text-[10px] sm:text-xs text-secondary dark:text-dark-secondary mt-0.5">Students under your supervision</p>
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
            <div class="divide-y divide-border dark:divide-dark-border">
                @forelse($students as $s)
                    <a href="{{ route('supervisor.students.show', $s) }}" class="flex items-center gap-3 sm:gap-4 p-4 sm:p-5 transition-colors hover:bg-surface dark:hover:bg-dark-surface group active:bg-surface/80">
                        <div class="relative shrink-0">
                            <x-avatar :name="$s->user->name" size="md" />
                            <span class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 sm:w-4 sm:h-4 rounded-full border-2 border-card dark:border-dark-card {{ $s->status === 'active' ? 'bg-success' : 'bg-tertiary' }}"></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-0.5">
                                <p class="text-sm font-medium text-primary dark:text-dark-primary group-hover:text-accent transition-colors truncate">{{ $s->user->name }}</p>
                                <x-status-badge :status="$s->status" size="sm" />
                            </div>
                            <p class="text-xs text-secondary dark:text-dark-secondary truncate">{{ $s->programme?->name ?? $s->programme_name ?? '—' }}</p>
                        </div>
                        <div class="flex items-center gap-3 shrink-0">
                            <div class="hidden sm:block text-right">
                                <p class="text-xs text-tertiary dark:text-dark-tertiary mb-1">Progress</p>
                                <div class="flex items-center gap-2">
                                    <div class="w-20 h-2 bg-border-light dark:bg-dark-border rounded-full overflow-hidden">
                                        <div class="h-full bg-accent rounded-full" style="width: {{ $s->overall_progress ?? 0 }}%"></div>
                                    </div>
                                    <span class="text-xs font-medium text-secondary dark:text-dark-secondary w-8">{{ $s->overall_progress ?? 0 }}%</span>
                                </div>
                            </div>
                            <span class="sm:hidden text-xs font-medium text-secondary dark:text-dark-secondary">{{ $s->overall_progress ?? 0 }}%</span>
                            <svg class="w-5 h-5 text-tertiary dark:text-dark-tertiary group-hover:text-accent transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </a>
                @empty
                    <div class="p-8 sm:p-12">
                        <div class="flex flex-col items-center justify-center text-center">
                            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-br from-accent/15 to-accent/5 flex items-center justify-center mb-3 sm:mb-4">
                                <svg class="w-7 h-7 sm:w-8 sm:h-8 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                            </div>
                            <h3 class="text-sm sm:text-base font-semibold text-primary dark:text-dark-primary mb-1">No students assigned</h3>
                            <p class="text-xs sm:text-sm text-secondary dark:text-dark-secondary">Students will appear here once assigned to you.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-5 sm:space-y-6">
            {{-- Pending reports --}}
            <div class="bg-card dark:bg-dark-card rounded-2xl border border-border dark:border-dark-border overflow-hidden">
                <div class="px-4 sm:px-6 py-4 sm:py-5 border-b border-border dark:border-dark-border">
                    <h3 class="text-sm sm:text-base font-semibold text-primary dark:text-dark-primary">Reports to Review</h3>
                    <p class="text-[10px] sm:text-xs text-secondary dark:text-dark-secondary mt-0.5">{{ $pendingReports->count() }} pending</p>
                </div>
                <div class="p-3 sm:p-4">
                    <div class="space-y-1.5 sm:space-y-2 max-h-[280px] overflow-y-auto scrollbar-thin">
                        @forelse($pendingReports as $report)
                            <a href="{{ route('reports.show', [$report->student_id, $report]) }}" class="flex items-start gap-3 p-2.5 sm:p-3 rounded-xl hover:bg-surface dark:hover:bg-dark-surface transition-colors border border-transparent hover:border-border dark:hover:border-dark-border active:bg-surface/80">
                                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-gradient-to-br from-warning/20 to-warning/10 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-primary dark:text-dark-primary truncate">{{ $report->title }}</p>
                                    <p class="text-xs text-secondary dark:text-dark-secondary">{{ $report->student->user->name }}</p>
                                    <p class="text-[10px] text-tertiary dark:text-dark-tertiary mt-0.5">{{ $report->submitted_at?->diffForHumans() ?? 'Recently' }}</p>
                                </div>
                            </a>
                        @empty
                            <p class="text-sm text-secondary dark:text-dark-secondary text-center py-4">No reports pending review</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Quick actions (desktop only) --}}
            <div class="hidden lg:block bg-card dark:bg-dark-card rounded-2xl border border-border dark:border-dark-border overflow-hidden">
                <div class="px-6 py-5 border-b border-border dark:border-dark-border">
                    <h3 class="text-base font-semibold text-primary dark:text-dark-primary">Quick Actions</h3>
                </div>
                <div class="p-2">
                    <a href="{{ route('supervisor.students.index') }}" class="flex items-center gap-3 px-4 py-3 text-sm text-secondary dark:text-dark-secondary hover:text-primary dark:hover:text-dark-primary hover:bg-surface dark:hover:bg-dark-surface rounded-xl transition-colors">
                        <div class="w-8 h-8 rounded-lg bg-accent/10 flex items-center justify-center"><svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg></div>
                        <span>View All Students</span>
                        <svg class="w-4 h-4 text-tertiary ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('ai.chat') }}" class="flex items-center gap-3 px-4 py-3 text-sm text-secondary dark:text-dark-secondary hover:text-primary dark:hover:text-dark-primary hover:bg-surface dark:hover:bg-dark-surface rounded-xl transition-colors">
                        <div class="w-8 h-8 rounded-lg bg-success/10 flex items-center justify-center"><svg class="w-4 h-4 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg></div>
                        <span>AI Assistant</span>
                        <svg class="w-4 h-4 text-tertiary ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Tasks waiting review --}}
    @if($tasksForReview->count() > 0)
    <div class="bg-card dark:bg-dark-card rounded-2xl border border-border dark:border-dark-border overflow-hidden">
        <div class="px-4 sm:px-6 py-4 sm:py-5 border-b border-border dark:border-dark-border flex items-center justify-between">
            <div>
                <h3 class="text-sm sm:text-base font-semibold text-primary dark:text-dark-primary">Tasks Waiting Review</h3>
                <p class="text-[10px] sm:text-xs text-secondary dark:text-dark-secondary mt-0.5">Requiring your attention</p>
            </div>
            <div class="flex items-center gap-1.5 sm:gap-2 px-2.5 sm:px-3 py-1 sm:py-1.5 rounded-full bg-warning/10 text-warning text-[10px] sm:text-xs font-medium">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ $tasksForReview->count() }} due
            </div>
        </div>
        <div class="divide-y divide-border dark:divide-dark-border">
            @foreach($tasksForReview as $task)
                <a href="{{ route('tasks.show', [$task->student_id, $task]) }}" class="flex items-center gap-3 sm:gap-4 p-4 sm:p-5 transition-colors hover:bg-surface dark:hover:bg-dark-surface group active:bg-surface/80">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-br from-warning/20 to-warning/10 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-primary dark:text-dark-primary group-hover:text-accent transition-colors truncate">{{ $task->title }}</p>
                        <div class="flex items-center gap-2 sm:gap-3 mt-0.5 sm:mt-1">
                            <p class="text-xs text-secondary dark:text-dark-secondary truncate">{{ $task->student->user->name }}</p>
                            <span class="text-tertiary dark:text-dark-tertiary hidden sm:inline">·</span>
                            <p class="text-xs text-tertiary dark:text-dark-tertiary hidden sm:inline">Due {{ $task->due_date?->format('M j') ?? '—' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                        <x-status-badge :status="$task->status" size="sm" />
                        <svg class="w-5 h-5 text-tertiary dark:text-dark-tertiary group-hover:text-accent transition-colors hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
    @endif
    </div>
</x-layouts.app>
