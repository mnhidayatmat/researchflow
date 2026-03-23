<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - ResearchFlow</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">

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
                        sky: {
                            50: '#f0f9ff', 100: '#e0f2fe', 200: '#bae6fd', 300: '#7dd3fc', 400: '#38bdf8', 500: '#0ea5e9', 600: '#0284c7',
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

        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 20px rgba(14, 165, 233, 0.1); }
            50% { box-shadow: 0 0 40px rgba(14, 165, 233, 0.2); }
        }
        .pulse-glow { animation: pulse-glow 3s ease-in-out infinite; }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        .float { animation: float 4s ease-in-out infinite; }

        /* Card hover */
        .card-hover {
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 25px 50px -12px rgba(26, 26, 46, 0.15);
        }

        /* Gradient text */
        .gradient-text {
            background: linear-gradient(135deg, #1a1a2e 0%, #0ea5e9 100%);
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
            background: linear-gradient(180deg, #0ea5e9, #7dd3fc);
        }

        /* Status indicator pulse */
        .status-pulse {
            animation: status-pulse 2s ease-in-out infinite;
        }
        @keyframes status-pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        /* Progress bar animation */
        .progress-animated {
            transition: width 1s cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #e8ede9; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #d5e0d8; }

        /* Background pattern */
        .bg-pattern {
            background-image: radial-gradient(circle at 20% 50%, rgba(14, 165, 233, 0.03) 0%, transparent 50%),
                              radial-gradient(circle at 80% 20%, rgba(26, 26, 46, 0.02) 0%, transparent 50%);
        }

        /* Decorative elements */
        .decorative-dot {
            width: 6px; height: 6px;
            background: linear-gradient(135deg, #0ea5e9, #7dd3fc);
            border-radius: 50%;
        }

        .number-display {
            font-feature-settings: 'tnum';
            font-variant-numeric: tabular-nums;
        }
    </style>
</head>
<body class="min-h-screen">
    <div class="grain-overlay"></div>

    <!-- Sidebar included via layout -->
    @include('layouts.sidebar')
    @include('layouts.topbar')

    <!-- Main Content -->
    <main class="lg:pl-64 pt-16">
        <div class="max-w-7xl mx-auto px-6 py-8 bg-pattern">
            <!-- Welcome Header -->
            <header class="mb-10 fade-up">
                <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold text-sky-500 tracking-widest uppercase mb-2">Admin Dashboard</p>
                        <h1 class="font-serif text-3xl sm:text-4xl font-bold text-ink mb-2">
                            Welcome back, <span class="gradient-text">{{ auth()->user()->first_name ?? auth()->user()->name }}</span>
                        </h1>
                        <p class="text-sm text-sage-500">{{ now()->format('l, F j, Y') }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <button class="p-3 rounded-2xl bg-white border border-sage-200 text-sage-500 hover:text-ink hover:border-sky-300 transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                            </button>
                            @if($stats['pending_reviews'] > 0)
                            <span class="absolute -top-1 -right-1 w-5 h-5 bg-gradient-to-br from-sky-400 to-sky-600 rounded-full text-white text-xs font-semibold flex items-center justify-center status-pulse">{{ $stats['pending_reviews'] }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </header>

            <!-- KPI Stats Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-10">
                <!-- Total Students -->
                <div class="card-hover group bg-white rounded-2xl p-6 border border-sage-200 relative overflow-hidden fade-up delay-100">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-sky-100/50 to-transparent rounded-bl-full"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-sky-400/10 to-sky-600/10 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-7 h-7 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                            </div>
                            @if($stats['student_trend'])
                            <div class="flex items-center gap-1 px-2.5 py-1 rounded-full bg-sage-100 text-sage-600 text-xs font-semibold">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                                </svg>
                                {{ $stats['student_trend'] }}
                            </div>
                            @endif
                        </div>
                        <p class="font-serif text-3xl font-bold text-ink number-display">{{ $stats['total_students'] }}</p>
                        <p class="text-sm text-sage-500 mt-1">Total Students</p>
                    </div>
                </div>

                <!-- Active Students -->
                <div class="card-hover group bg-white rounded-2xl p-6 border border-sage-200 relative overflow-hidden fade-up delay-200">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-emerald-100/50 to-transparent rounded-bl-full"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-400/10 to-emerald-600/10 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-7 h-7 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                        <p class="font-serif text-3xl font-bold text-ink number-display">{{ $stats['active_students'] }}</p>
                        <p class="text-sm text-sage-500 mt-1">Active Students</p>
                        <p class="text-xs text-sage-400 mt-2">Currently enrolled</p>
                    </div>
                </div>

                <!-- Pending Reviews -->
                <div class="card-hover group bg-white rounded-2xl p-6 border border-sage-200 relative overflow-hidden fade-up delay-300">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-amber-100/50 to-transparent rounded-bl-full"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-400/10 to-amber-600/10 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-7 h-7 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            @if($stats['pending_reviews'] > 0)
                            <div class="w-3 h-3 rounded-full bg-amber-400 status-pulse"></div>
                            @endif
                        </div>
                        <p class="font-serif text-3xl font-bold text-ink number-display">{{ $stats['pending_reviews'] }}</p>
                        <p class="text-sm text-sage-500 mt-1">Pending Reviews</p>
                        <p class="text-xs text-sage-400 mt-2">Awaiting approval</p>
                    </div>
                </div>

                <!-- Tasks Due -->
                <div class="card-hover group bg-white rounded-2xl p-6 border border-sage-200 relative overflow-hidden fade-up delay-400">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-violet-100/50 to-transparent rounded-bl-full"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-violet-400/10 to-violet-600/10 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-7 h-7 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                </svg>
                            </div>
                        </div>
                        <p class="font-serif text-3xl font-bold text-ink number-display">{{ $stats['tasks_due'] }}</p>
                        <p class="text-sm text-sage-500 mt-1">Tasks Due</p>
                        <p class="text-xs text-sage-400 mt-2">This week</p>
                    </div>
                </div>
            </div>

            <!-- Main Grid -->
            <div class="grid lg:grid-cols-3 gap-6">
                <!-- Students List -->
                <div class="lg:col-span-2 bg-white rounded-2xl border border-sage-200 overflow-hidden fade-up delay-300">
                    <div class="px-7 py-5 border-b border-sage-100 flex items-center justify-between">
                        <div>
                            <h2 class="font-serif text-lg font-semibold text-ink">Recent Students</h2>
                            <p class="text-xs text-sage-500 mt-0.5">Latest additions to your program</p>
                        </div>
                        @if($recentStudents->count() > 0)
                        <a href="{{ route('admin.students.index') }}" class="text-xs font-semibold text-sky-500 hover:text-sky-600 inline-flex items-center gap-1.5 transition-colors">
                            View all
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                            </svg>
                        </a>
                        @endif
                    </div>

                    @if($recentStudents->count() === 0)
                        <div class="p-16 text-center">
                            <div class="w-24 h-24 rounded-3xl bg-gradient-to-br from-sky-100/50 to-sky-50/30 flex items-center justify-center mx-auto mb-6 float">
                                <svg class="w-12 h-12 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                            </div>
                            <h3 class="font-serif text-2xl font-semibold text-ink mb-3">No students yet</h3>
                            <p class="text-sm text-sage-500 max-w-sm mx-auto mb-8">Start building your research supervision program by adding your first student.</p>
                            <div class="flex items-center justify-center gap-3">
                                <a href="{{ route('admin.students.create') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-semibold bg-gradient-to-r from-sky-500 to-sky-600 text-white hover:from-sky-600 hover:to-sky-700 transition-all shadow-lg shadow-sky-500/25">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Add First Student
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="divide-y divide-sage-100">
                            @foreach($recentStudents as $s)
                            <a href="{{ route('admin.students.show', $s) }}" class="flex items-center gap-5 p-5 hover:bg-sage-50/50 transition-colors group accent-line pl-6">
                                <div class="relative">
                                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-sage-200 to-sage-300 flex items-center justify-center text-sm font-semibold text-sage-700">
                                        {{ $s->user->name[0] }}{{ $s->user->name.split(' ')[1][0] ?? '' }}
                                    </div>
                                    <span class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 rounded-full border-2 border-white {{ $s->status === 'active' ? 'bg-emerald-400' : 'bg-sage-300' }}"></span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <p class="text-sm font-semibold text-ink group-hover:text-sky-500 transition-colors truncate">{{ $s->user->name }}</p>
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
                                                <div class="h-full bg-gradient-to-r from-sky-400 to-sky-500 rounded-full progress-animated" style="width: {{ $s->overall_progress ?? 0 }}%"></div>
                                            </div>
                                            <span class="text-xs font-semibold text-ink w-10">{{ $s->overall_progress ?? 0 }}%</span>
                                        </div>
                                    </div>
                                    <svg class="w-5 h-5 text-sage-300 group-hover:text-sky-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </div>
                            </a>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Quick Stats -->
                    <div class="bg-white rounded-2xl border border-sage-200 overflow-hidden fade-up delay-400">
                        <div class="px-7 py-5 border-b border-sage-100">
                            <h2 class="font-serif text-lg font-semibold text-ink">Quick Stats</h2>
                        </div>
                        <div class="p-6 space-y-5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-11 h-11 rounded-xl bg-violet-100 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-serif text-xl font-bold text-ink number-display">{{ App\Models\User::whereIn('role', ['supervisor', 'cosupervisor'])->count() }}</p>
                                        <p class="text-xs text-sage-500">Supervisors</p>
                                    </div>
                                </div>
                            </div>
                            <div class="h-px bg-sage-100"></div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-11 h-11 rounded-xl bg-sky-100 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-serif text-xl font-bold text-ink number-display">{{ \App\Models\Programme::count() }}</p>
                                        <p class="text-xs text-sage-500">Programmes</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="bg-white rounded-2xl border border-sage-200 overflow-hidden fade-up">
                        <div class="px-7 py-5 border-b border-sage-100">
                            <h2 class="font-serif text-lg font-semibold text-ink">Recent Activity</h2>
                        </div>
                        <div class="p-6 max-h-80 overflow-y-auto">
                            @if($recentActivity->count() === 0)
                                <div class="text-center py-10">
                                    <svg class="w-12 h-12 text-sage-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <p class="text-sm text-sage-500">No recent activity</p>
                                </div>
                            @else
                                <div class="space-y-4">
                                    @foreach($recentActivity as $activity)
                                    <div class="flex items-start gap-3 group">
                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-sky-100 to-sky-50 flex items-center justify-center shrink-0">
                                            <svg class="w-5 h-5 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $activity['icon'] }}"/>
                                            </svg>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-medium text-ink truncate">{{ $activity['title'] }}</p>
                                            <p class="text-xs text-sage-500">{{ $activity['student'] }}</p>
                                            <p class="text-[10px] text-sage-400 mt-1">{{ $activity['time'] }}</p>
                                        </div>
                                    </div>
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
                            <a href="{{ route('admin.students.create') }}" class="flex items-center gap-3 p-3 rounded-xl text-sage-600 hover:bg-sage-50 hover:text-ink transition-all group">
                                <div class="w-10 h-10 rounded-xl bg-sky-100 flex items-center justify-center group-hover:bg-sky-200 transition-colors">
                                    <svg class="w-5 h-5 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium">Add Student</p>
                                    <p class="text-xs text-sage-400">Register new student</p>
                                </div>
                                <svg class="w-4 h-4 text-sage-300 group-hover:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                            <a href="{{ route('admin.programmes.create') }}" class="flex items-center gap-3 p-3 rounded-xl text-sage-600 hover:bg-sage-50 hover:text-ink transition-all group">
                                <div class="w-10 h-10 rounded-xl bg-violet-100 flex items-center justify-center group-hover:bg-violet-200 transition-colors">
                                    <svg class="w-5 h-5 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium">New Programme</p>
                                    <p class="text-xs text-sage-400">Create programme</p>
                                </div>
                                <svg class="w-4 h-4 text-sage-300 group-hover:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                            <a href="{{ route('ai.chat') }}" class="flex items-center gap-3 p-3 rounded-xl text-sage-600 hover:bg-sage-50 hover:text-ink transition-all group">
                                <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center group-hover:bg-emerald-200 transition-colors">
                                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium">AI Assistant</p>
                                    <p class="text-xs text-sage-400">Get AI help</p>
                                </div>
                                <svg class="w-4 h-4 text-sage-300 group-hover:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tasks Due Soon -->
            @if($tasksDue->count() > 0)
            <div class="mt-6 bg-white rounded-2xl border border-sage-200 overflow-hidden fade-up">
                <div class="px-7 py-5 border-b border-sage-100 flex items-center justify-between">
                    <div>
                        <h2 class="font-serif text-lg font-semibold text-ink">Tasks Due Soon</h2>
                        <p class="text-xs text-sage-500 mt-0.5">Requiring attention this week</p>
                    </div>
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-amber-100 text-amber-700 text-xs font-semibold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ $tasksDue->count() }} due
                    </div>
                </div>
                <div class="divide-y divide-sage-100">
                    @foreach($tasksDue as $t)
                    <a href="{{ route('tasks.show', [$t->student_id, $t]) }}" class="flex items-center gap-5 p-5 hover:bg-sage-50/50 transition-colors group accent-line pl-6">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-100 to-amber-50 flex items-center justify-center">
                            <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-ink group-hover:text-amber-600 transition-colors truncate">{{ $t->title }}</p>
                            <div class="flex items-center gap-3 mt-1">
                                <p class="text-xs text-sage-500">{{ $t->student->user->name ?? 'Unknown' }}</p>
                                <span class="text-sage-300">•</span>
                                <p class="text-xs text-sage-400">Due {{ $t->due_date->format('M j') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium @if($t->status === 'waiting_review') bg-amber-100 text-amber-700 @elseif($t->status === 'in_progress') bg-sky-100 text-sky-700 @else bg-sage-100 text-sage-600 @endif">
                                {{ str_replace('_', ' ', $t->status) }}
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
    </main>
</body>
</html>
