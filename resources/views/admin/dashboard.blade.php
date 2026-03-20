<x-layouts.app title="Admin Dashboard" :header="'Dashboard'">
    <div class="space-y-6">
        {{-- Welcome Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-primary">Welcome back, {{ auth()->user()->first_name ?? auth()->user()->name }}</h1>
                <p class="text-sm text-secondary mt-1">Here's an overview of your research supervision program.</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs text-tertiary">{{ now()->format('l, F j, Y') }}</span>
                <button class="relative p-2.5 text-secondary hover:text-primary hover:bg-surface rounded-xl transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    @if($stats['pending_reviews'] > 0)
                    <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-danger rounded-full ring-2 ring-card"></span>
                    @endif
                </button>
            </div>
        </div>

        {{-- KPI Stats --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Total Students --}}
            <div class="group relative bg-card rounded-2xl p-6 border border-border hover:border-accent/30 hover:shadow-soft transition-all duration-300">
                <div class="flex items-start justify-between">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-accent/20 to-accent/10 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    @if($stats['student_trend'])
                    <div class="flex items-center gap-1 px-2 py-1 rounded-full bg-success/10 text-success text-xs font-semibold">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                        </svg>
                        <span>{{ $stats['student_trend'] }}</span>
                    </div>
                    @endif
                </div>
                <div class="mt-4">
                    <div class="flex items-baseline gap-2">
                        <p class="text-lg font-bold text-primary">{{ $stats['total_students'] }}</p>
                        <p class="text-xs text-secondary">total</p>
                    </div>
                    <p class="text-xs text-secondary mt-1">Students</p>
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
                    <div class="flex items-baseline gap-2">
                        <p class="text-lg font-bold text-primary">{{ $stats['active_students'] }}</p>
                        <p class="text-xs text-secondary">active</p>
                    </div>
                    <p class="text-xs text-secondary mt-1">Currently enrolled</p>
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
                    <div class="flex items-baseline gap-2">
                        <p class="text-lg font-bold text-primary">{{ $stats['pending_reviews'] }}</p>
                        <p class="text-xs text-secondary">pending</p>
                    </div>
                    <p class="text-xs text-secondary mt-1">Awaiting review</p>
                </div>
            </div>

            {{-- Tasks Due --}}
            <div class="group relative bg-card rounded-2xl p-6 border border-border hover:border-info/30 hover:shadow-soft transition-all duration-300">
                <div class="flex items-start justify-between">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-info/20 to-info/10 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="flex items-baseline gap-2">
                        <p class="text-lg font-bold text-primary">{{ $stats['tasks_due'] }}</p>
                        <p class="text-xs text-secondary">due</p>
                    </div>
                    <p class="text-xs text-secondary mt-1">This week</p>
                </div>
            </div>
        </div>

        {{-- Main Content Grid --}}
        <div class="grid lg:grid-cols-3 gap-6">
            {{-- Students Overview (2 columns) --}}
            <div class="lg:col-span-2 bg-card rounded-2xl border border-border overflow-hidden">
                {{-- Card Header --}}
                <div class="px-6 py-5 border-b border-border flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-semibold text-primary">Recent Students</h2>
                        <p class="text-xs text-secondary mt-0.5">Latest additions to your program</p>
                    </div>
                    @if($recentStudents->count() > 0)
                    <a href="{{ route('admin.students.index') }}" class="text-xs text-accent hover:text-amber-700 font-medium inline-flex items-center gap-1">
                        View all
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </a>
                    @endif
                </div>

                {{-- Card Content --}}
                @if($recentStudents->count() === 0)
                    <div class="p-12">
                        <div class="flex flex-col items-center justify-center text-center">
                            <div class="w-20 h-20 rounded-3xl bg-gradient-to-br from-accent/15 to-accent/5 flex items-center justify-center mb-6">
                                <svg class="w-10 h-10 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-primary mb-2">No students yet</h3>
                            <p class="text-sm text-secondary max-w-sm mb-8">Start building your research supervision program by adding your first student.</p>
                            <div class="flex items-center gap-3">
                                <a href="{{ route('admin.students.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold bg-accent text-white hover:bg-amber-700 transition-all shadow-sm hover:shadow">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Add First Student
                                </a>
                                <a href="#" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-medium text-secondary hover:text-primary border border-border hover:bg-surface transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                    Import CSV
                                </a>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="divide-y divide-border">
                        @foreach($recentStudents as $s)
                        <a href="{{ route('admin.students.show', $s) }}" class="flex items-center gap-4 p-5 hover:bg-surface transition-colors group">
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
                                <div class="text-right">
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
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Right Sidebar --}}
            <div class="space-y-6">
                {{-- Quick Stats --}}
                <div class="bg-card rounded-2xl border border-border overflow-hidden">
                    <div class="px-6 py-5 border-b border-border">
                        <h2 class="text-base font-semibold text-primary">Quick Stats</h2>
                    </div>
                    <div class="p-6 space-y-5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-info/10 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-lg font-bold text-primary">{{ App\Models\User::whereIn('role', ['supervisor', 'cosupervisor'])->count() }}</p>
                                    <p class="text-xs text-secondary">Supervisors</p>
                                </div>
                            </div>
                        </div>
                        <div class="h-px bg-border"></div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-accent/10 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-lg font-bold text-primary">{{ \App\Models\Programme::count() }}</p>
                                    <p class="text-xs text-secondary">Programmes</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Recent Activity --}}
                <div class="bg-card rounded-2xl border border-border overflow-hidden">
                    <div class="px-6 py-5 border-b border-border flex items-center justify-between">
                        <h2 class="text-base font-semibold text-primary">Recent Activity</h2>
                        @if($recentActivity->count() > 0)
                        <span class="text-xs font-medium text-tertiary">Latest updates</span>
                        @endif
                    </div>
                    <div class="p-6">
                        @if($recentActivity->count() === 0)
                            <div class="text-center py-8">
                                <svg class="w-12 h-12 text-tertiary mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="text-sm text-secondary">No recent activity</p>
                            </div>
                        @else
                            <div class="space-y-4">
                                @foreach($recentActivity as $activity)
                                <div class="flex items-start gap-3 group">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-accent/20 to-accent/10 flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $activity['icon'] }}"/>
                                        </svg>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-primary truncate">{{ $activity['title'] }}</p>
                                        <p class="text-xs text-secondary">{{ $activity['student'] }}</p>
                                        <p class="text-[10px] text-tertiary mt-1">{{ $activity['time'] }}</p>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Quick Actions --}}
                <div class="bg-card rounded-2xl border border-border overflow-hidden">
                    <div class="px-6 py-5 border-b border-border">
                        <h2 class="text-base font-semibold text-primary">Quick Actions</h2>
                    </div>
                    <div class="p-3">
                        <a href="{{ route('admin.students.create') }}" class="flex items-center gap-3 p-3 rounded-xl text-secondary hover:bg-surface hover:text-primary transition-all group">
                            <div class="w-10 h-10 rounded-xl bg-accent/10 flex items-center justify-center group-hover:bg-accent/20 transition-colors">
                                <svg class="w-5 h-5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium">Add Student</p>
                                <p class="text-xs text-tertiary">Register new student</p>
                            </div>
                            <svg class="w-5 h-5 text-tertiary group-hover:text-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                        <a href="{{ route('admin.programmes.create') }}" class="flex items-center gap-3 p-3 rounded-xl text-secondary hover:bg-surface hover:text-primary transition-all group">
                            <div class="w-10 h-10 rounded-xl bg-info/10 flex items-center justify-center group-hover:bg-info/20 transition-colors">
                                <svg class="w-5 h-5 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium">New Programme</p>
                                <p class="text-xs text-tertiary">Create academic programme</p>
                            </div>
                            <svg class="w-5 h-5 text-tertiary group-hover:text-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                        <a href="{{ route('ai.chat') }}" class="flex items-center gap-3 p-3 rounded-xl text-secondary hover:bg-surface hover:text-primary transition-all group">
                            <div class="w-10 h-10 rounded-xl bg-success/10 flex items-center justify-center group-hover:bg-success/20 transition-colors">
                                <svg class="w-5 h-5 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium">AI Assistant</p>
                                <p class="text-xs text-tertiary">Get AI-powered help</p>
                            </div>
                            <svg class="w-5 h-5 text-tertiary group-hover:text-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tasks Due Soon --}}
        @if($tasksDue->count() > 0)
        <div class="bg-card rounded-2xl border border-border overflow-hidden">
            <div class="px-6 py-5 border-b border-border flex items-center justify-between">
                <div>
                    <h2 class="text-base font-semibold text-primary">Tasks Due Soon</h2>
                    <p class="text-xs text-secondary mt-0.5">Requiring attention this week</p>
                </div>
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-warning/10 text-warning text-xs font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ $tasksDue->count() }} due
                </div>
            </div>
            <div class="divide-y divide-border">
                @foreach($tasksDue as $t)
                <a href="{{ route('tasks.show', [$t->student_id, $t]) }}" class="flex items-center gap-4 p-5 hover:bg-surface transition-colors group">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-warning/20 to-warning/10 flex items-center justify-center">
                        <svg class="w-6 h-6 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-primary group-hover:text-accent transition-colors truncate">{{ $t->title }}</p>
                        <div class="flex items-center gap-3 mt-1">
                            <p class="text-xs text-secondary">{{ $t->student->user->name }}</p>
                            <span class="text-tertiary">•</span>
                            <p class="text-xs text-tertiary">Due {{ $t->due_date->format('M j') }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <x-status-badge :status="$t->status" size="sm" />
                        <svg class="w-5 h-5 text-tertiary group-hover:text-accent transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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