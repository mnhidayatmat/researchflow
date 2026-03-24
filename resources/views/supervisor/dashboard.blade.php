@push('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #faf9f7 0%, #f5f3ef 100%);
            font-family: 'DM Sans', system-ui, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        .font-serif { font-family: 'Playfair Display', Georgia, serif; }

        .grain-overlay {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            pointer-events: none; opacity: 0.008; z-index: 10;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)'/%3E%3C/svg%3E");
        }

        @keyframes fade-up {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-up { animation: fade-up 0.6s ease-out forwards; opacity: 0; }
        .delay-100 { animation-delay: 0.1s; }
        .delay-200 { animation-delay: 0.2s; }
        .delay-300 { animation-delay: 0.3s; }

        @keyframes pulse-ring {
            0% { transform: scale(0.8); opacity: 1; }
            100% { transform: scale(1.4); opacity: 0; }
        }
        .pulse-ring::before {
            content: '';
            position: absolute;
            inset: -4px;
            border-radius: 50%;
            border: 2px solid #f43f5e;
            animation: pulse-ring 2s ease-out infinite;
        }

        .card-hover {
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 25px 50px -12px rgba(26, 26, 46, 0.15);
        }

        .progress-animated {
            transition: width 1s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .accent-line::before {
            content: '';
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 3px;
            background: linear-gradient(180deg, #14b8a6, #5eead4);
        }

        .bg-pattern {
            background-image: radial-gradient(circle at 20% 50%, rgba(20, 184, 166, 0.03) 0%, transparent 50%),
                              radial-gradient(circle at 80% 20%, rgba(26, 26, 46, 0.02) 0%, transparent 50%);
        }

        .number-display {
            font-feature-settings: 'tnum';
            font-variant-numeric: tabular-nums;
        }

        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #e8ede9; border-radius: 3px; }
    </style>
@endpush

<x-layouts.app title="Supervisor Dashboard" :header="'Supervisor Dashboard'">
    <div class="grain-overlay"></div>
    <div class="max-w-7xl mx-auto px-6 py-8 bg-pattern relative">
            <!-- Welcome Header -->
            <header class="mb-10 fade-up">
                <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold text-teal-500 tracking-widest uppercase mb-2">Supervisor Dashboard</p>
                        <h1 class="font-serif text-3xl sm:text-4xl font-bold text-ink">
                            Welcome back, <span class="text-teal-600">{{ auth()->user()->name }}</span>
                        </h1>
                        <p class="text-sm text-sage-500 mt-2">
                            @if($stats['pending_reviews'] > 0)
                            <span class="inline-flex items-center gap-1.5 text-rose-500">
                                <span class="relative w-2 h-2">
                                    <span class="absolute inset-0 bg-rose-400 rounded-full animate-ping"></span>
                                    <span class="relative bg-rose-500 rounded-full"></span>
                                </span>
                                You have {{ $stats['pending_reviews'] }} item{{ $stats['pending_reviews'] !== 1 ? 's' : '' }} awaiting your review
                            </span>
                            @else
                            <span class="text-emerald-600">You're all caught up!</span>
                            @endif
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-sage-400 uppercase tracking-wide">{{ now()->format('l') }}</p>
                        <p class="font-serif text-2xl font-semibold text-ink">{{ now()->format('F j, Y') }}</p>
                    </div>
                </div>
            </header>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-10">
                <!-- Total Students -->
                <div class="card-hover group bg-white rounded-2xl p-6 border border-sage-200 relative overflow-hidden fade-up delay-100">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-teal-100/50 to-transparent rounded-bl-full"></div>
                    <div class="relative">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-teal-400/10 to-teal-600/10 flex items-center justify-center group-hover:scale-110 transition-transform duration-300 mb-4">
                            <svg class="w-7 h-7 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                        <p class="font-serif text-3xl font-bold text-ink number-display">{{ $stats['total_students'] }}</p>
                        <p class="text-sm text-sage-500 mt-1">Total Students</p>
                    </div>
                </div>

                <!-- Active Students -->
                <div class="card-hover group bg-white rounded-2xl p-6 border border-sage-200 relative overflow-hidden fade-up delay-200">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-emerald-100/50 to-transparent rounded-bl-full"></div>
                    <div class="relative">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-400/10 to-emerald-600/10 flex items-center justify-center group-hover:scale-110 transition-transform duration-300 mb-4">
                            <svg class="w-7 h-7 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <p class="font-serif text-3xl font-bold text-ink number-display">{{ $stats['active_students'] }}</p>
                        <p class="text-sm text-sage-500 mt-1">Active Students</p>
                    </div>
                </div>

                <!-- Pending Reviews -->
                <div class="card-hover group bg-white rounded-2xl p-6 border border-sage-200 relative overflow-hidden fade-up delay-300">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-rose-100/50 to-transparent rounded-bl-full"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-rose-400/10 to-rose-600/10 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-7 h-7 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            @if($stats['pending_reviews'] > 0)
                            <div class="relative">
                                <div class="w-3 h-3 bg-rose-400 rounded-full"></div>
                                <div class="absolute inset-0 w-3 h-3 bg-rose-400 rounded-full pulse-ring"></div>
                            </div>
                            @endif
                        </div>
                        <p class="font-serif text-3xl font-bold text-ink number-display">{{ $stats['pending_reviews'] }}</p>
                        <p class="text-sm text-sage-500 mt-1">Pending Reviews</p>
                    </div>
                </div>

                <!-- Tasks to Review -->
                <div class="card-hover group bg-white rounded-2xl p-6 border border-sage-200 relative overflow-hidden fade-up delay-400">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-violet-100/50 to-transparent rounded-bl-full"></div>
                    <div class="relative">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-violet-400/10 to-violet-600/10 flex items-center justify-center group-hover:scale-110 transition-transform duration-300 mb-4">
                            <svg class="w-7 h-7 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                            </svg>
                        </div>
                        <p class="font-serif text-3xl font-bold text-ink number-display">{{ $stats['tasks_waiting_review'] }}</p>
                        <p class="text-sm text-sage-500 mt-1">Tasks to Review</p>
                    </div>
                </div>
            </div>

            <!-- Main Grid -->
            <div class="grid lg:grid-cols-3 gap-6">
                <!-- Students List -->
                <div class="lg:col-span-2 bg-white rounded-2xl border border-sage-200 overflow-hidden fade-up delay-300">
                    <div class="px-7 py-5 border-b border-sage-100 flex items-center justify-between">
                        <div>
                            <h2 class="font-serif text-lg font-semibold text-ink">My Students</h2>
                            <p class="text-xs text-sage-500 mt-0.5">Students under your supervision</p>
                        </div>
                        @if($students->count() > 0)
                        <a href="{{ route('supervisor.students.index') }}" class="text-xs font-semibold text-teal-500 hover:text-teal-600 inline-flex items-center gap-1.5 transition-colors">
                            View all
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                            </svg>
                        </a>
                        @endif
                    </div>

                    @forelse($students as $s)
                    <div class="divide-y divide-sage-100">
                        <a href="{{ route('supervisor.students.show', $s) }}" class="flex items-center gap-5 p-5 hover:bg-sage-50/50 transition-colors group accent-line pl-6">
                            <div class="relative">
                                <div class="w-13 h-13 rounded-xl bg-gradient-to-br from-teal-100 to-teal-200 flex items-center justify-center text-sm font-semibold text-teal-700 w-13">
                                    {{ $s->user->name[0] }}{{ explode(' ', trim($s->user->name))[1][0] ?? '' }}
                                </div>
                                <span class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 rounded-full border-2 border-white {{ $s->status === 'active' ? 'bg-emerald-400' : 'bg-sage-300' }}"></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <p class="text-sm font-semibold text-ink group-hover:text-teal-600 transition-colors truncate">{{ $s->user->name }}</p>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $s->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-sage-100 text-sage-600' }}">
                                        {{ $s->status }}
                                    </span>
                                </div>
                                <p class="text-xs text-sage-500">{{ $s->programme->name }}</p>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="text-right hidden sm:block">
                                    <p class="text-[10px] text-sage-400 mb-1 uppercase tracking-wide">Progress</p>
                                    <div class="flex items-center gap-2">
                                        <div class="w-24 h-2 bg-sage-100 rounded-full overflow-hidden">
                                            <div class="h-full bg-gradient-to-r from-teal-400 to-teal-500 rounded-full progress-animated" style="width: {{ $s->overall_progress ?? 0 }}%"></div>
                                        </div>
                                        <span class="text-xs font-semibold text-ink w-10">{{ $s->overall_progress ?? 0 }}%</span>
                                    </div>
                                </div>
                                <svg class="w-5 h-5 text-sage-300 group-hover:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </a>
                    </div>
                    @empty
                        <div class="p-16 text-center">
                            <div class="w-20 h-20 rounded-3xl bg-gradient-to-br from-teal-100/50 to-teal-50/30 flex items-center justify-center mx-auto mb-6">
                                <svg class="w-10 h-10 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                            </div>
                            <h3 class="font-serif text-xl font-semibold text-ink mb-2">No students assigned</h3>
                            <p class="text-sm text-sage-500">Students will appear here once they are assigned to you.</p>
                        </div>
                    @endforelse
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Pending Reports -->
                    <div class="bg-white rounded-2xl border border-sage-200 overflow-hidden fade-up delay-400">
                        <div class="px-7 py-5 border-b border-sage-100">
                            <h2 class="font-serif text-lg font-semibold text-ink">Reports to Review</h2>
                            <p class="text-xs text-sage-500 mt-0.5">{{ $pendingReports->count() }} pending</p>
                        </div>
                        <div class="p-4 max-h-80 overflow-y-auto">
                            <div class="space-y-2">
                                @forelse($pendingReports as $report)
                                <a href="{{ route('reports.show', [$report->student_id, $report]) }}" class="flex items-start gap-3 p-3 rounded-xl hover:bg-sage-50 transition-colors border border-transparent hover:border-sage-200 group">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-rose-100 to-rose-50 flex items-center justify-center shrink-0 mt-0.5">
                                        <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-ink group-hover:text-rose-500 transition-colors truncate">{{ $report->title }}</p>
                                        <p class="text-xs text-sage-500">{{ $report->student->user->name }}</p>
                                        <p class="text-[10px] text-sage-400 mt-0.5">{{ $report->submitted_at?->diffForHumans() ?? 'Recently' }}</p>
                                    </div>
                                    <svg class="w-4 h-4 text-sage-300 group-hover:text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                                @empty
                                    <div class="text-center py-8">
                                        <svg class="w-12 h-12 text-sage-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <p class="text-sm text-sage-500">No reports pending</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white rounded-2xl border border-sage-200 overflow-hidden fade-up">
                        <div class="px-7 py-5 border-b border-sage-100">
                            <h2 class="font-serif text-lg font-semibold text-ink">Quick Actions</h2>
                        </div>
                        <div class="p-3">
                            <a href="{{ route('supervisor.students.index') }}" class="flex items-center gap-3 p-3 rounded-xl text-sage-600 hover:bg-sage-50 hover:text-ink transition-all group">
                                <div class="w-10 h-10 rounded-xl bg-teal-100 flex items-center justify-center group-hover:bg-teal-200 transition-colors">
                                    <svg class="w-5 h-5 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium">View All Students</p>
                                    <p class="text-xs text-sage-400">Manage your students</p>
                                </div>
                                <svg class="w-4 h-4 text-sage-300 group-hover:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                            <a href="{{ route('ai.chat') }}" class="flex items-center gap-3 p-3 rounded-xl text-sage-600 hover:bg-sage-50 hover:text-ink transition-all group">
                                <div class="w-10 h-10 rounded-xl bg-violet-100 flex items-center justify-center group-hover:bg-violet-200 transition-colors">
                                    <svg class="w-5 h-5 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium">AI Assistant</p>
                                    <p class="text-xs text-sage-400">Get AI help</p>
                                </div>
                                <svg class="w-4 h-4 text-sage-300 group-hover:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tasks Waiting Review -->
            @if($tasksForReview->count() > 0)
            <div class="mt-6 bg-white rounded-2xl border border-sage-200 overflow-hidden fade-up">
                <div class="px-7 py-5 border-b border-sage-100 flex items-center justify-between">
                    <div>
                        <h2 class="font-serif text-lg font-semibold text-ink">Tasks Waiting Review</h2>
                        <p class="text-xs text-sage-500 mt-0.5">Requiring your attention</p>
                    </div>
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-amber-100 text-amber-700 text-xs font-semibold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ $tasksForReview->count() }} due
                    </div>
                </div>
                <div class="divide-y divide-sage-100">
                    @foreach($tasksForReview as $task)
                    <a href="{{ route('tasks.show', [$task->student_id, $task]) }}" class="flex items-center gap-5 p-5 hover:bg-sage-50/50 transition-colors group accent-line pl-6">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-100 to-amber-50 flex items-center justify-center">
                            <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-ink group-hover:text-amber-600 transition-colors truncate">{{ $task->title }}</p>
                            <div class="flex items-center gap-3 mt-1">
                                <p class="text-xs text-sage-500">{{ $task->student->user->name }}</p>
                                <span class="text-sage-300">•</span>
                                <p class="text-xs text-sage-400">Due {{ $task->due_date?->format('M j') ?? '—' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                                waiting review
                            </span>
                            <svg class="w-5 h-5 text-sage-300 group-hover:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
