<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - ResearchFlow</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        serif: ['"Playfair Display"', 'Georgia', 'serif'],
                        sans: ['"DM Sans"', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        ink: '#1a1a2e',
                        paper: '#faf9f7',
                        cream: '#f5f3ef',
                        sage: {
                            50: '#f7f9f7', 100: '#e8ede9', 200: '#d5e0d8', 300: '#b8c9b9', 400: '#9aab9b', 500: '#7d8a7e', 600: '#5a6a5c',
                        },
                        indigo: {
                            50: '#eef2ff', 100: '#e0e7ff', 200: '#c7d2fe', 300: '#a5b4fc', 400: '#818cf8', 500: '#6366f1', 600: '#4f46e5',
                        },
                        coral: {
                            50: '#fff1f0', 100: '#ffe4e1', 200: '#ffcdc7', 300: '#ffa8a0', 400: '#ff7f72', 500: '#ff5a4d', 600: '#e6392e',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        body {
            background: linear-gradient(135deg, #faf9f7 0%, #f5f3ef 100%);
            font-family: 'DM Sans', system-ui, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        .font-serif { font-family: 'Playfair Display', Georgia, serif; }

        /* Grain overlay */
        .grain-overlay {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            pointer-events: none; opacity: 0.008; z-index: 9999;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)'/%3E%3C/svg%3E");
        }

        /* Animations */
        @keyframes fade-up {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-up { animation: fade-up 0.6s ease-out forwards; opacity: 0; }
        .delay-100 { animation-delay: 0.1s; }
        .delay-200 { animation-delay: 0.2s; }
        .delay-300 { animation-delay: 0.3s; }
        .delay-400 { animation-delay: 0.4s; }

        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        .shimmer {
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            background-size: 200% 100%;
            animation: shimmer 2s infinite;
        }

        /* Card hover */
        .card-hover {
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 25px 50px -12px rgba(26, 26, 46, 0.15);
        }

        /* Progress ring */
        .progress-ring {
            transform: rotate(-90deg);
        }
        .progress-ring circle {
            transition: stroke-dashoffset 1s cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* Gradient text */
        .gradient-text {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Accent line */
        .accent-line::before {
            content: '';
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 3px;
            background: linear-gradient(180deg, #6366f1, #a5b4fc);
        }

        /* Background pattern */
        .bg-pattern {
            background-image: radial-gradient(circle at 20% 50%, rgba(99, 102, 241, 0.04) 0%, transparent 50%),
                              radial-gradient(circle at 80% 20%, rgba(139, 92, 246, 0.03) 0%, transparent 50%);
        }

        .number-display {
            font-feature-settings: 'tnum';
            font-variant-numeric: tabular-nums;
        }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #e8ede9; border-radius: 3px; }

        /* Floating animation */
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }
        .float { animation: float 4s ease-in-out infinite; }
    </style>
</head>
<body class="min-h-screen">
    <div class="grain-overlay"></div>

    @include('layouts.sidebar')
    @include('layouts.topbar')

    <!-- Main Content -->
    <main class="lg:pl-64 pt-16">
        <div class="max-w-7xl mx-auto px-6 py-8 bg-pattern">
            <!-- Welcome Header -->
            <header class="mb-10 fade-up">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold text-indigo-500 tracking-widest uppercase mb-2">Student Dashboard</p>
                        <h1 class="font-serif text-3xl sm:text-4xl font-bold text-ink">
                            Welcome back, <span class="gradient-text">{{ auth()->user()->name }}</span>
                        </h1>
                        <p class="text-sm text-sage-500 mt-2">Your research progress: <span class="font-semibold text-indigo-600">{{ $student->overall_progress }}%</span> complete</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="text-right hidden sm:block">
                            <p class="text-xs text-sage-400 uppercase tracking-wide">{{ now()->format('l') }}</p>
                            <p class="font-serif text-lg font-semibold text-ink">{{ now()->format('F j, Y') }}</p>
                        </div>
                        <button class="p-3 rounded-2xl bg-white border border-sage-200 text-sage-500 hover:text-ink hover:border-indigo-300 transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </header>

            <!-- KPI Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-10">
                <!-- Programme -->
                <div class="card-hover group bg-white rounded-2xl p-6 border border-sage-200 relative overflow-hidden fade-up delay-100">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-indigo-100/50 to-transparent rounded-bl-full"></div>
                    <div class="relative">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-400/10 to-indigo-600/10 flex items-center justify-center group-hover:scale-110 transition-transform duration-300 mb-4">
                            <svg class="w-7 h-7 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                        <p class="font-serif text-2xl font-bold text-ink">{{ $student->programme->code }}</p>
                        <p class="text-sm text-sage-500 mt-1">Programme</p>
                    </div>
                </div>

                <!-- Overall Progress -->
                <div class="card-hover group bg-white rounded-2xl p-6 border border-sage-200 relative overflow-hidden fade-up delay-200">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-violet-100/50 to-transparent rounded-bl-full"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-violet-400/10 to-violet-600/10 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-7 h-7 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            @if($student->overall_progress >= 75)
                            <div class="flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                                </svg>
                                On track
                            </div>
                            @endif
                        </div>
                        <div class="flex items-baseline gap-2">
                            <p class="font-serif text-3xl font-bold text-ink number-display">{{ $student->overall_progress }}%</p>
                            <p class="text-sm text-sage-500">complete</p>
                        </div>
                        <div class="w-full h-2.5 bg-sage-100 rounded-full overflow-hidden mt-3">
                            <div class="h-full bg-gradient-to-r from-violet-400 to-violet-500 rounded-full progress-animated" style="width: {{ $student->overall_progress }}%"></div>
                        </div>
                    </div>
                </div>

                <!-- Total Tasks -->
                <a href="{{ route('tasks.index', $student) }}" class="card-hover group bg-white rounded-2xl p-6 border border-sage-200 relative overflow-hidden block fade-up delay-300">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-emerald-100/50 to-transparent rounded-bl-full"></div>
                    <div class="relative">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-400/10 to-emerald-600/10 flex items-center justify-center group-hover:scale-110 transition-transform duration-300 mb-4">
                            <svg class="w-7 h-7 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <p class="font-serif text-3xl font-bold text-ink number-display">{{ $tasks->count() }}</p>
                            <p class="text-sm text-sage-500">total</p>
                        </div>
                        <p class="text-sm text-sage-500 mt-1">Tasks</p>
                    </div>
                </a>

                <!-- Completed -->
                <div class="card-hover group bg-white rounded-2xl p-6 border border-sage-200 relative overflow-hidden fade-up delay-400">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-coral-100/30 to-transparent rounded-bl-full"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-coral-400/10 to-coral-600/10 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-7 h-7 text-coral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            @if($tasks->where('status', 'completed')->count() > 0)
                            <div class="w-3 h-3 rounded-full bg-emerald-400"></div>
                            @endif
                        </div>
                        <div class="flex items-baseline gap-2">
                            <p class="font-serif text-3xl font-bold text-ink number-display">{{ $tasks->where('status', 'completed')->count() }}</p>
                            <p class="text-sm text-sage-500">done</p>
                        </div>
                        <p class="text-sm text-sage-500 mt-1">Completed</p>
                    </div>
                </div>
            </div>

            <!-- Main Grid -->
            <div class="grid lg:grid-cols-3 gap-6">
                <!-- Left Column -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Research Overview -->
                    <div class="bg-white rounded-2xl border border-sage-200 overflow-hidden fade-up delay-300">
                        <div class="px-7 py-5 border-b border-sage-100">
                            <h2 class="font-serif text-lg font-semibold text-ink">Research Overview</h2>
                            <p class="text-xs text-sage-500 mt-0.5">Your research journey details</p>
                        </div>
                        <div class="p-7">
                            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                                <div>
                                    <p class="text-[10px] font-semibold text-sage-400 uppercase tracking-wider mb-2">Research Title</p>
                                    <p class="text-sm font-medium text-ink">{{ $student->research_title ?? 'Not assigned yet' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-semibold text-sage-400 uppercase tracking-wider mb-2">Supervisor</p>
                                    <div class="flex items-center gap-2">
                                        @if($student->supervisor)
                                            <div class="w-7 h-7 rounded-lg bg-indigo-100 flex items-center justify-center text-xs font-semibold text-indigo-600">
                                                {{ $student->supervisor->name[0] }}
                                            </div>
                                            <p class="text-sm font-medium text-ink">{{ $student->supervisor->name }}</p>
                                        @else
                                            <p class="text-sm text-sage-400">Not assigned</p>
                                        @endif
                                    </div>
                                    @if($student->cosupervisor)
                                    <div class="flex items-center gap-2 mt-2">
                                        <div class="w-6 h-6 rounded-md bg-violet-100 flex items-center justify-center text-[10px] font-semibold text-violet-600">
                                            {{ $student->cosupervisor->name[0] }}
                                        </div>
                                        <p class="text-xs text-sage-500">Co: {{ $student->cosupervisor->name }}</p>
                                    </div>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-[10px] font-semibold text-sage-400 uppercase tracking-wider mb-2">Start Date</p>
                                    <p class="text-sm text-ink">{{ $student->start_date?->format('M d, Y') ?? '—' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-semibold text-sage-400 uppercase tracking-wider mb-2">Expected Completion</p>
                                    <p class="text-sm text-ink">{{ $student->expected_completion?->format('M d, Y') ?? '—' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Upcoming Tasks -->
                    <div class="bg-white rounded-2xl border border-sage-200 overflow-hidden fade-up">
                        <div class="px-7 py-5 border-b border-sage-100 flex items-center justify-between">
                            <div>
                                <h2 class="font-serif text-lg font-semibold text-ink">Upcoming Tasks</h2>
                                <p class="text-xs text-sage-500 mt-0.5">Tasks requiring your attention</p>
                            </div>
                            @if($upcomingTasks->count() > 0)
                            <a href="{{ route('tasks.index', $student) }}" class="text-xs font-semibold text-indigo-500 hover:text-indigo-600 inline-flex items-center gap-1.5 transition-colors">
                                View all
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                </svg>
                            </a>
                            @endif
                        </div>

                        @if($upcomingTasks->count() === 0)
                            <div class="p-16 text-center">
                                <div class="w-24 h-24 rounded-3xl bg-gradient-to-br from-violet-100/50 to-violet-50/30 flex items-center justify-center mx-auto mb-6 float">
                                    <svg class="w-12 h-12 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                    </svg>
                                </div>
                                <h3 class="font-serif text-2xl font-semibold text-ink mb-3">No upcoming tasks</h3>
                                <p class="text-sm text-sage-500 max-w-sm mx-auto mb-8">You're all caught up! Enjoy your free time or check out your completed tasks.</p>
                                <a href="{{ route('tasks.index', $student) }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-semibold bg-gradient-to-r from-indigo-500 to-violet-500 text-white hover:from-indigo-600 hover:to-violet-600 transition-all shadow-lg shadow-indigo-500/25">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    View All Tasks
                                </a>
                            </div>
                        @else
                            <div class="divide-y divide-sage-100">
                                @foreach($upcomingTasks as $task)
                                <a href="{{ route('tasks.show', [$student, $task]) }}" class="flex items-center gap-5 p-5 hover:bg-sage-50/50 transition-colors group accent-line pl-6">
                                    <div class="w-12 h-12 rounded-xl @if($task->status === 'in_progress') bg-gradient-to-br from-violet-100 to-violet-50 @elseif($task->status === 'waiting_review') bg-gradient-to-br from-amber-100 to-amber-50 @else bg-gradient-to-br from-sage-100 to-sage-50 @endif flex items-center justify-center">
                                        <svg class="w-6 h-6 @if($task->status === 'in_progress') text-violet-500 @elseif($task->status === 'waiting_review') text-amber-500 @else text-sage-400 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-ink group-hover:text-violet-500 transition-colors truncate">{{ $task->title }}</p>
                                        <div class="flex items-center gap-3 mt-1">
                                            <p class="text-xs text-sage-500">Due {{ $task->due_date?->format('M d, Y') ?? 'No date' }}</p>
                                            @if($task->milestone)
                                            <span class="text-sage-300">•</span>
                                            <p class="text-xs text-sage-400">{{ $task->milestone->title }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium @if($task->status === 'in_progress') bg-violet-100 text-violet-700 @elseif($task->status === 'waiting_review') bg-amber-100 text-amber-700 @else bg-sage-100 text-sage-600 @endif">
                                        {{ str_replace('_', ' ', $task->status) }}
                                    </span>
                                    <svg class="w-5 h-5 text-sage-300 group-hover:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Recent Reports -->
                    <div class="bg-white rounded-2xl border border-sage-200 overflow-hidden fade-up">
                        <div class="px-7 py-5 border-b border-sage-100 flex items-center justify-between">
                            <div>
                                <h2 class="font-serif text-lg font-semibold text-ink">Progress Reports</h2>
                                <p class="text-xs text-sage-500 mt-0.5">Your submitted progress reports</p>
                            </div>
                            @if($recentReports->count() > 0)
                            <a href="{{ route('reports.index', $student) }}" class="text-xs font-semibold text-indigo-500 hover:text-indigo-600 inline-flex items-center gap-1.5 transition-colors">
                                View all
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                </svg>
                            </a>
                            @endif
                        </div>

                        @if($recentReports->count() === 0)
                            <div class="p-16 text-center">
                                <div class="w-24 h-24 rounded-3xl bg-gradient-to-br from-sky-100/50 to-sky-50/30 flex items-center justify-center mx-auto mb-6">
                                    <svg class="w-12 h-12 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <h3 class="font-serif text-2xl font-semibold text-ink mb-3">No reports yet</h3>
                                <p class="text-sm text-sage-500 max-w-sm mx-auto mb-8">Start documenting your research progress by submitting your first report.</p>
                                <a href="{{ route('reports.create', $student) }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-semibold bg-gradient-to-r from-sky-500 to-indigo-500 text-white hover:from-sky-600 hover:to-indigo-600 transition-all shadow-lg shadow-sky-500/25">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Create Report
                                </a>
                            </div>
                        @else
                            <div class="divide-y divide-sage-100">
                                @foreach($recentReports as $report)
                                <a href="{{ route('reports.show', [$student, $report]) }}" class="flex items-center justify-between p-5 hover:bg-sage-50/50 transition-colors group accent-line pl-6">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-sky-100 to-sky-50 flex items-center justify-center">
                                            <svg class="w-6 h-6 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-ink group-hover:text-sky-500 transition-colors">{{ $report->title }}</p>
                                            <p class="text-xs text-sage-500 mt-1">{{ $report->created_at->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium @if($report->status === 'submitted') bg-sky-100 text-sky-700 @elseif($report->status === 'reviewed') bg-emerald-100 text-emerald-700 @elseif($report->status === 'revision_needed') bg-rose-100 text-rose-700 @else bg-sage-100 text-sage-600 @endif">
                                        {{ str_replace('_', ' ', $report->status) }}
                                    </span>
                                </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Right Sidebar -->
                <div class="space-y-6">
                    <!-- Quick Stats -->
                    <div class="bg-white rounded-2xl border border-sage-200 overflow-hidden fade-up delay-400">
                        <div class="px-7 py-5 border-b border-sage-100">
                            <h2 class="font-serif text-lg font-semibold text-ink">Quick Stats</h2>
                        </div>
                        <div class="p-6 space-y-5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-11 h-11 rounded-xl bg-amber-100 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-serif text-xl font-bold text-ink number-display">{{ $tasksByStatus->get('in_progress', collect())->count() }}</p>
                                        <p class="text-xs text-sage-500">In Progress</p>
                                    </div>
                                </div>
                            </div>
                            <div class="h-px bg-sage-100"></div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-11 h-11 rounded-xl bg-violet-100 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-serif text-xl font-bold text-ink number-display">{{ $tasksByStatus->get('waiting_review', collect())->count() }}</p>
                                        <p class="text-xs text-sage-500">In Review</p>
                                    </div>
                                </div>
                            </div>
                            <div class="h-px bg-sage-100"></div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-11 h-11 rounded-xl bg-sage-100 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-sage-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-serif text-xl font-bold text-ink number-display">{{ $tasksByStatus->get('planned', collect())->count() }}</p>
                                        <p class="text-xs text-sage-500">Planned</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Upcoming Meetings -->
                    <div class="bg-white rounded-2xl border border-sage-200 overflow-hidden fade-up">
                        <div class="px-7 py-5 border-b border-sage-100 flex items-center justify-between">
                            <h2 class="font-serif text-lg font-semibold text-ink">Upcoming Meetings</h2>
                            @if($upcomingMeetings->count() > 0)
                            <a href="{{ route('meetings.index', $student) }}" class="text-xs font-semibold text-indigo-500 hover:text-indigo-600">View all</a>
                            @else
                            <a href="{{ route('meetings.create', $student) }}" class="text-xs font-semibold text-indigo-500 hover:text-indigo-600">Schedule</a>
                            @endif
                        </div>
                        <div class="p-6">
                            @if($upcomingMeetings->count() === 0)
                                <div class="text-center py-8">
                                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-sky-100/50 to-sky-50/30 flex items-center justify-center mx-auto mb-4">
                                        <svg class="w-8 h-8 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <p class="text-sm text-sage-500 mb-4">No upcoming meetings</p>
                                    <a href="{{ route('meetings.create', $student) }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-r from-sky-500 to-indigo-500 text-white hover:from-sky-600 hover:to-indigo-600 transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        Schedule Meeting
                                    </a>
                                </div>
                            @else
                                <div class="space-y-3">
                                    @foreach($upcomingMeetings as $meeting)
                                    <a href="{{ route('meetings.show', [$student, $meeting]) }}" class="flex items-start gap-3 p-3 rounded-xl hover:bg-sage-50 transition-all group">
                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-sky-100 to-sky-50 flex items-center justify-center shrink-0">
                                            <svg class="w-5 h-5 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-medium text-ink group-hover:text-sky-500 transition-colors truncate">{{ $meeting->title }}</p>
                                            <p class="text-xs text-sage-500 mt-1">{{ $meeting->scheduled_at->format('M d, Y · g:i A') }}</p>
                                        </div>
                                    </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white rounded-2xl border border-sage-200 overflow-hidden fade-up">
                        <div class="px-7 py-5 border-b border-sage-100">
                            <h2 class="font-serif text-lg font-semibold text-ink">Quick Actions</h2>
                        </div>
                        <div class="p-3">
                            <a href="{{ route('tasks.index', $student) }}" class="flex items-center gap-3 p-3 rounded-xl text-sage-600 hover:bg-sage-50 hover:text-ink transition-all group">
                                <div class="w-10 h-10 rounded-xl bg-violet-100 flex items-center justify-center group-hover:bg-violet-200 transition-colors">
                                    <svg class="w-5 h-5 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium">View Tasks</p>
                                    <p class="text-xs text-sage-400">Manage your tasks</p>
                                </div>
                                <svg class="w-4 h-4 text-sage-300 group-hover:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                            <a href="{{ route('reports.create', $student) }}" class="flex items-center gap-3 p-3 rounded-xl text-sage-600 hover:bg-sage-50 hover:text-ink transition-all group">
                                <div class="w-10 h-10 rounded-xl bg-sky-100 flex items-center justify-center group-hover:bg-sky-200 transition-colors">
                                    <svg class="w-5 h-5 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium">New Report</p>
                                    <p class="text-xs text-sage-400">Submit progress</p>
                                </div>
                                <svg class="w-4 h-4 text-sage-300 group-hover:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                            <a href="{{ route('meetings.create', $student) }}" class="flex items-center gap-3 p-3 rounded-xl text-sage-600 hover:bg-sage-50 hover:text-ink transition-all group">
                                <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center group-hover:bg-emerald-200 transition-colors">
                                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium">Schedule Meeting</p>
                                    <p class="text-xs text-sage-400">Book with supervisor</p>
                                </div>
                                <svg class="w-4 h-4 text-sage-300 group-hover:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                            <a href="{{ route('publications.index', $student) }}" class="flex items-center gap-3 p-3 rounded-xl text-sage-600 hover:bg-sage-50 hover:text-ink transition-all group">
                                <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center group-hover:bg-amber-200 transition-colors">
                                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium">Publication Track</p>
                                    <p class="text-xs text-sage-400">Monitor submissions</p>
                                </div>
                                <svg class="w-4 h-4 text-sage-300 group-hover:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                            <a href="{{ route('ai.chat') }}" class="flex items-center gap-3 p-3 rounded-xl text-sage-600 hover:bg-sage-50 hover:text-ink transition-all group">
                                <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center group-hover:bg-indigo-200 transition-colors">
                                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium">AI Assistant</p>
                                    <p class="text-xs text-sage-400">Get research help</p>
                                </div>
                                <svg class="w-4 h-4 text-sage-300 group-hover:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- Recent Publications -->
                    <div class="bg-white rounded-2xl border border-sage-200 overflow-hidden fade-up">
                        <div class="px-7 py-5 border-b border-sage-100 flex items-center justify-between">
                            <div>
                                <h2 class="font-serif text-lg font-semibold text-ink">Recent Publications</h2>
                                <p class="text-xs text-sage-500 mt-0.5">Latest submission updates</p>
                            </div>
                            <a href="{{ route('publications.index', $student) }}" class="text-xs font-semibold text-indigo-500 hover:text-indigo-600">View all</a>
                        </div>
                        <div class="p-6">
                            @if($recentPublications->count() === 0)
                                <p class="text-sm text-sage-500 text-center py-4">No publication records yet.</p>
                            @else
                                <div class="space-y-3">
                                    @foreach($recentPublications as $publication)
                                        <a href="{{ route('publications.index', $student) }}" class="flex items-start gap-3 p-3 rounded-xl hover:bg-sage-50 transition-all group">
                                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-100 to-amber-50 flex items-center justify-center shrink-0">
                                                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                                </svg>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-medium text-ink group-hover:text-amber-600 transition-colors truncate">{{ $publication->title }}</p>
                                                <p class="text-xs text-sage-500 mt-1">{{ $publication->journal }}</p>
                                            </div>
                                            <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-700">{{ $publication->stage_label }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
