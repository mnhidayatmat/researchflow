<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ResearchFlow - Academic Research Supervision Platform</title>
    <meta name="description" content="Streamline postgraduate research supervision with task management, progress tracking, and AI-powered assistance.">

    <!-- Fonts: Playfair Display (editorial serif) + DM Sans (modern geometric) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400;1,9..40,500&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,400;1,500;1,600&display=swap" rel="stylesheet">

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
                        amber: {
                            400: '#f59e0b',
                            500: '#d97706',
                            600: '#b45309',
                            700: '#92400e',
                        },
                        sage: {
                            50: '#f7f9f7',
                            100: '#e8ede9',
                            200: '#d5e0d8',
                            300: '#b8c9b9',
                            400: '#9aab9b',
                            500: '#7d8a7e',
                        }
                    },
                    letterSpacing: {
                        editorial: '0.02em',
                        wide: '0.15em',
                    }
                }
            }
        }
    </script>

    <style>
        /* Base */
        html { scroll-behavior: smooth; }
        body {
            background-color: #faf9f7;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f5f3ef; }
        ::-webkit-scrollbar-thumb { background: #d97706; border-radius: 4px; }

        /* Grain texture overlay */
        .grain-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            pointer-events: none;
            opacity: 0.015;
            z-index: 9999;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)'/%3E%3C/svg%3E");
        }

        /* Typography */
        .font-serif { font-feature-settings: 'kern', 'liga', 'clig'; }
        .drop-cap::first-letter {
            float: left;
            font-size: 4.5em;
            line-height: 0.8;
            padding-right: 0.08em;
            color: #d97706;
        }

        /* Animations */
        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(2deg); }
        }
        @keyframes float-delayed {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-15px) rotate(-2deg); }
        }
        .float { animation: float 6s ease-in-out infinite; }
        .float-delayed { animation: float-delayed 7s ease-in-out infinite; animation-delay: 1s; }

        @keyframes fade-up {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-up {
            opacity: 0;
            animation: fade-up 0.8s ease-out forwards;
        }
        .delay-100 { animation-delay: 0.1s; }
        .delay-200 { animation-delay: 0.2s; }
        .delay-300 { animation-delay: 0.3s; }
        .delay-400 { animation-delay: 0.4s; }
        .delay-500 { animation-delay: 0.5s; }

        @keyframes pulse-soft {
            0%, 100% { opacity: 0.4; }
            50% { opacity: 0.8; }
        }
        .pulse-soft { animation: pulse-soft 3s ease-in-out infinite; }

        /* Scroll reveal */
        .reveal {
            opacity: 0;
            transform: translateY(40px);
            transition: all 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }

        /* Hover effects */
        .hover-lift {
            transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.4s ease;
        }
        .hover-lift:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(26, 26, 46, 0.1);
        }

        /* Gradient text */
        .gradient-text {
            background: linear-gradient(135deg, #1a1a2e 0%, #d97706 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Border decorative */
        .corner-bracket {
            position: relative;
        }
        .corner-bracket::before,
        .corner-bracket::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            border-color: #d97706;
        }
        .corner-bracket::before {
            top: -4px; left: -4px;
            border-top: 2px solid;
            border-left: 2px solid;
        }
        .corner-bracket::after {
            bottom: -4px; right: -4px;
            border-bottom: 2px solid;
            border-right: 2px solid;
        }

        /* Feature card accent */
        .feature-accent {
            position: relative;
        }
        .feature-accent::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 0;
            background: linear-gradient(180deg, #d97706, #b45309);
            transition: height 0.4s ease;
        }
        .feature-accent:hover::before {
            height: 100%;
        }

        /* Hero background pattern */
        .hero-pattern {
            background-image:
                radial-gradient(circle at 20% 50%, rgba(217, 119, 6, 0.03) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(26, 26, 46, 0.03) 0%, transparent 50%),
                radial-gradient(circle at 40% 80%, rgba(217, 119, 6, 0.02) 0%, transparent 40%);
        }

        /* Stagger children */
        .stagger-children > * {
            opacity: 0;
            transform: translateY(20px);
            animation: fade-up 0.6s ease-out forwards;
        }
        .stagger-children > *:nth-child(1) { animation-delay: 0.1s; }
        .stagger-children > *:nth-child(2) { animation-delay: 0.2s; }
        .stagger-children > *:nth-child(3) { animation-delay: 0.3s; }
        .stagger-children > *:nth-child(4) { animation-delay: 0.4s; }
        .stagger-children > *:nth-child(5) { animation-delay: 0.5s; }
        .stagger-children > *:nth-child(6) { animation-delay: 0.6s; }
    </style>
</head>
<body class="font-sans text-ink">
    <!-- Grain overlay -->
    <div class="grain-overlay"></div>

    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 bg-paper/80 backdrop-blur-md border-b border-ink/5">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 lg:h-20">
                <!-- Logo -->
                <a href="/" class="flex items-center gap-2.5 group">
                    <div class="w-9 h-9 bg-gradient-to-br from-amber-500 to-amber-700 rounded-lg flex items-center justify-center transform group-hover:rotate-3 transition-transform duration-300">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <span class="font-serif text-lg font-semibold tracking-wide">ResearchFlow</span>
                </a>

                <!-- Desktop Nav -->
                <div class="hidden md:flex items-center gap-8">
                    <a href="#features" class="text-sm font-medium text-ink/70 hover:text-ink transition-colors">Features</a>
                    <a href="#roles" class="text-sm font-medium text-ink/70 hover:text-ink transition-colors">For Roles</a>
                    <a href="#how-it-works" class="text-sm font-medium text-ink/70 hover:text-ink transition-colors">How It Works</a>
                </div>

                <!-- CTA Buttons -->
                <div class="flex items-center gap-3">
                    <a href="/login" class="hidden sm:inline-flex text-sm font-medium text-ink/70 hover:text-ink transition-colors">Sign In</a>
                    <a href="/register" class="inline-flex items-center gap-2 px-4 py-2 bg-ink text-white text-sm font-semibold rounded-lg hover:bg-amber-600 transition-colors duration-300">
                        Get Started
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative min-h-screen flex items-center pt-20 hero-pattern overflow-hidden">
        <!-- Floating decorative elements -->
        <div class="absolute top-32 left-[10%] w-64 h-64 bg-amber-400/10 rounded-full blur-3xl float"></div>
        <div class="absolute bottom-32 right-[15%] w-96 h-96 bg-sage-200/40 rounded-full blur-3xl float-delayed"></div>

        <div class="max-w-7xl mx-auto px-6 lg:px-8 py-16 lg:py-24">
            <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
                <!-- Left: Content -->
                <div class="stagger-children">
                    <!-- Badge -->
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-amber-50 border border-amber-200 rounded-full mb-6">
                        <span class="w-1.5 h-1.5 bg-amber-500 rounded-full pulse-soft"></span>
                        <span class="text-xs font-medium text-amber-700 tracking-wide uppercase">Research Management Platform</span>
                    </div>

                    <!-- Headline -->
                    <h1 class="font-serif text-4xl sm:text-5xl lg:text-6xl font-bold leading-[1.1] tracking-tight mb-6">
                        <span class="block">Transform Your</span>
                        <span class="block gradient-text">Research Journey</span>
                    </h1>

                    <!-- Subtitle -->
                    <p class="text-lg text-ink/60 leading-relaxed max-w-lg mb-8">
                        Streamline postgraduate research supervision with intelligent task management, progress tracking, collaborative meetings, and AI-powered assistance.
                    </p>

                    <!-- CTA Buttons -->
                    <div class="flex flex-wrap items-center gap-4 mb-10">
                        <a href="/register" class="inline-flex items-center gap-2.5 px-6 py-3 bg-gradient-to-r from-amber-500 to-amber-600 text-white text-sm font-semibold rounded-xl hover:from-amber-600 hover:to-amber-700 transition-all duration-300 shadow-lg shadow-amber-500/25 hover:shadow-xl hover:shadow-amber-500/30 hover:-translate-y-0.5">
                            Start Your Journey
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </a>
                        <a href="#how-it-works" class="inline-flex items-center gap-2 text-sm font-medium text-ink/70 hover:text-ink transition-colors group">
                            <span class="w-8 h-8 rounded-full border border-ink/10 flex items-center justify-center group-hover:border-ink/30 transition-colors">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M8 5v14l11-7z"/>
                                </svg>
                            </span>
                            See how it works
                        </a>
                    </div>

                    <!-- Social proof -->
                    <div class="flex items-center gap-6">
                        <div class="flex -space-x-2">
                            <div class="w-8 h-8 rounded-full bg-sage-200 border-2 border-paper flex items-center justify-center text-xs font-medium text-sage-600">JD</div>
                            <div class="w-8 h-8 rounded-full bg-amber-200 border-2 border-paper flex items-center justify-center text-xs font-medium text-amber-700">SM</div>
                            <div class="w-8 h-8 rounded-full bg-sage-300 border-2 border-paper flex items-center justify-center text-xs font-medium text-sage-700">AK</div>
                            <div class="w-8 h-8 rounded-full bg-ink/5 border-2 border-paper flex items-center justify-center text-xs font-medium text-ink/40">+42</div>
                        </div>
                        <p class="text-xs text-ink/50">Join <span class="font-semibold text-ink">500+ researchers</span> already streamlining their supervision</p>
                    </div>
                </div>

                <!-- Right: Visual -->
                <div class="relative fade-up delay-300">
                    <!-- Main card -->
                    <div class="relative bg-white rounded-2xl shadow-2xl shadow-ink/10 border border-ink/5 overflow-hidden transform rotate-1 hover:rotate-0 transition-transform duration-500">
                        <!-- Card header -->
                        <div class="px-6 py-4 border-b border-ink/5 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 rounded-full bg-red-400"></div>
                                <div class="w-3 h-3 rounded-full bg-amber-400"></div>
                                <div class="w-3 h-3 rounded-full bg-green-400"></div>
                            </div>
                            <span class="text-xs text-ink/40">ResearchFlow Dashboard</span>
                        </div>

                        <!-- Card body - Mock UI -->
                        <div class="p-6 space-y-4">
                            <!-- Progress bar -->
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-ink">Chapter 2: Literature Review</span>
                                <span class="text-sm font-semibold text-amber-600">75%</span>
                            </div>
                            <div class="h-2 bg-ink/5 rounded-full overflow-hidden">
                                <div class="h-full w-3/4 bg-gradient-to-r from-amber-400 to-amber-600 rounded-full"></div>
                            </div>

                            <!-- Task items -->
                            <div class="space-y-3 pt-4">
                                <div class="flex items-center gap-3 p-3 bg-sage-50 rounded-xl">
                                    <div class="w-5 h-5 rounded-full bg-green-500 flex items-center justify-center">
                                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                    <span class="text-sm text-ink/70 line-through">Submit initial draft</span>
                                </div>
                                <div class="flex items-center gap-3 p-3 bg-amber-50 rounded-xl border border-amber-200">
                                    <div class="w-5 h-5 rounded-full border-2 border-amber-400"></div>
                                    <span class="text-sm font-medium text-ink">Incorporate supervisor feedback</span>
                                </div>
                                <div class="flex items-center gap-3 p-3 bg-paper rounded-xl border border-ink/5">
                                    <div class="w-5 h-5 rounded-full border-2 border-ink/20"></div>
                                    <span class="text-sm text-ink/60">Schedule review meeting</span>
                                </div>
                            </div>
                        </div>

                        <!-- Floating badge -->
                        <div class="absolute -top-3 -right-3 bg-gradient-to-br from-amber-500 to-amber-600 text-white px-3 py-1.5 rounded-lg shadow-lg">
                            <span class="text-xs font-semibold">AI Powered</span>
                        </div>
                    </div>

                    <!-- Decorative element behind -->
                    <div class="absolute -bottom-4 -left-4 w-full h-full bg-amber-100 rounded-2xl -z-10"></div>
                </div>
            </div>

            <!-- Scroll indicator -->
            <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2 fade-up delay-500">
                <span class="text-xs text-ink/40 tracking-widest uppercase">Scroll to explore</span>
                <div class="w-5 h-8 rounded-full border-2 border-ink/10 flex justify-center pt-1.5">
                    <div class="w-1 h-2 bg-ink/30 rounded-full animate-bounce"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Trusted By / Stats Section -->
    <section class="py-16 lg:py-20 border-y border-ink/5 bg-cream/50">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-12">
                <div class="text-center reveal">
                    <p class="font-serif text-4xl lg:text-5xl font-bold text-ink mb-2">500+</p>
                    <p class="text-sm text-ink/50">Active Researchers</p>
                </div>
                <div class="text-center reveal">
                    <p class="font-serif text-4xl lg:text-5xl font-bold text-ink mb-2">12k+</p>
                    <p class="text-sm text-ink/50">Tasks Completed</p>
                </div>
                <div class="text-center reveal">
                    <p class="font-serif text-4xl lg:text-5xl font-bold text-ink mb-2">98%</p>
                    <p class="text-sm text-ink/50">On-time Graduation</p>
                </div>
                <div class="text-center reveal">
                    <p class="font-serif text-4xl lg:text-5xl font-bold text-ink mb-2">50+</p>
                    <p class="text-sm text-ink/50">Institutions</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 lg:py-28">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <!-- Section header -->
            <div class="max-w-2xl mb-16 reveal">
                <span class="inline-block text-xs font-semibold text-amber-600 tracking-widest uppercase mb-4">Features</span>
                <h2 class="font-serif text-3xl lg:text-4xl font-bold text-ink mb-4">Everything you need for successful research supervision</h2>
                <p class="text-ink/60">A comprehensive platform designed to simplify the complex journey of postgraduate research, from proposal to defense.</p>
            </div>

            <!-- Features grid -->
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Feature 1 -->
                <div class="feature-card bg-white rounded-xl p-6 border border-ink/5 hover:border-amber-200 hover-lift feature-accent reveal">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-400/10 to-amber-600/10 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    </div>
                    <h3 class="font-serif text-lg font-semibold text-ink mb-2">Task Management</h3>
                    <p class="text-sm text-ink/60 leading-relaxed">Break down your research into manageable tasks with Kanban boards, Gantt charts, and milestone tracking.</p>
                </div>

                <!-- Feature 2 -->
                <div class="feature-card bg-white rounded-xl p-6 border border-ink/5 hover:border-amber-200 hover-lift feature-accent reveal">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-sage-300/30 to-sage-500/30 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-sage-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <h3 class="font-serif text-lg font-semibold text-ink mb-2">Progress Reports</h3>
                    <p class="text-sm text-ink/60 leading-relaxed">Submit structured progress reports with revision tracking and supervisor feedback integration.</p>
                </div>

                <!-- Feature 3 -->
                <div class="feature-card bg-white rounded-xl p-6 border border-ink/5 hover:border-amber-200 hover-lift feature-accent reveal">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-400/10 to-blue-600/10 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </div>
                    <h3 class="font-serif text-lg font-semibold text-ink mb-2">Meeting Scheduler</h3>
                    <p class="text-sm text-ink/60 leading-relaxed">Schedule supervisory meetings, manage action items, and maintain comprehensive meeting notes.</p>
                </div>

                <!-- Feature 4 -->
                <div class="feature-card bg-white rounded-xl p-6 border border-ink/5 hover:border-amber-200 hover-lift feature-accent reveal">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-400/10 to-purple-600/10 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                        </svg>
                    </div>
                    <h3 class="font-serif text-lg font-semibold text-ink mb-2">AI Research Assistant</h3>
                    <p class="text-sm text-ink/60 leading-relaxed">Get intelligent support for writing, analysis, task breakdown, and deadline risk detection.</p>
                </div>

                <!-- Feature 5 -->
                <div class="feature-card bg-white rounded-xl p-6 border border-ink/5 hover:border-amber-200 hover-lift feature-accent reveal">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-rose-400/10 to-rose-600/10 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="font-serif text-lg font-semibold text-ink mb-2">File Vault</h3>
                    <p class="text-sm text-ink/60 leading-relaxed">Secure document storage with versioning, organized by category for proposals, reports, and thesis.</p>
                </div>

                <!-- Feature 6 -->
                <div class="feature-card bg-white rounded-xl p-6 border border-ink/5 hover:border-amber-200 hover-lift feature-accent reveal">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-400/10 to-emerald-600/10 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <h3 class="font-serif text-lg font-semibold text-ink mb-2">Publication Tracker</h3>
                    <p class="text-sm text-ink/60 leading-relaxed">Monitor your publication pipeline, track journal submissions, and manage collaboration records.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Roles Section -->
    <section id="roles" class="py-20 lg:py-28 bg-ink text-paper">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <!-- Section header -->
            <div class="text-center max-w-2xl mx-auto mb-16 reveal">
                <span class="inline-block text-xs font-semibold text-amber-400 tracking-widest uppercase mb-4">For Everyone</span>
                <h2 class="font-serif text-3xl lg:text-4xl font-bold mb-4">Designed for every role</h2>
                <p class="text-paper/60">Whether you're a student, supervisor, or administrator, ResearchFlow adapts to your needs.</p>
            </div>

            <div class="grid lg:grid-cols-3 gap-8">
                <!-- Students -->
                <div class="corner-bracket bg-paper/5 rounded-xl p-8 hover:bg-paper/10 transition-colors duration-300 reveal">
                    <div class="w-14 h-14 bg-gradient-to-br from-amber-400 to-amber-600 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <h3 class="font-serif text-2xl font-semibold mb-3">Students</h3>
                    <p class="text-paper/60 text-sm leading-relaxed mb-6">Track your research journey, manage tasks, submit reports, and collaborate with your supervisor seamlessly.</p>
                    <ul class="space-y-2 text-sm text-paper/50">
                        <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 bg-amber-400 rounded-full"></span>Kanban & Gantt task views</li>
                        <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 bg-amber-400 rounded-full"></span>Progress timeline visualization</li>
                        <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 bg-amber-400 rounded-full"></span>AI-powered research assistance</li>
                    </ul>
                </div>

                <!-- Supervisors -->
                <div class="corner-bracket bg-paper/5 rounded-xl p-8 hover:bg-paper/10 transition-colors duration-300 reveal">
                    <div class="w-14 h-14 bg-gradient-to-br from-sage-400 to-sage-600 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <h3 class="font-serif text-2xl font-semibold mb-3">Supervisors</h3>
                    <p class="text-paper/60 text-sm leading-relaxed mb-6">Monitor student progress, review submissions, schedule meetings, and provide timely feedback efficiently.</p>
                    <ul class="space-y-2 text-sm text-paper/50">
                        <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 bg-sage-400 rounded-full"></span>Student overview dashboard</li>
                        <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 bg-sage-400 rounded-full"></span>Report review workflow</li>
                        <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 bg-sage-400 rounded-full"></span>Meeting management</li>
                    </ul>
                </div>

                <!-- Admins -->
                <div class="corner-bracket bg-paper/5 rounded-xl p-8 hover:bg-paper/10 transition-colors duration-300 reveal">
                    <div class="w-14 h-14 bg-gradient-to-br from-blue-400 to-blue-600 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <h3 class="font-serif text-2xl font-semibold mb-3">Administrators</h3>
                    <p class="text-paper/60 text-sm leading-relaxed mb-6">Manage programmes, oversee all users, configure system settings, and maintain platform integrity.</p>
                    <ul class="space-y-2 text-sm text-paper/50">
                        <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 bg-blue-400 rounded-full"></span>Student & programme management</li>
                        <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 bg-blue-400 rounded-full"></span>AI & storage configuration</li>
                        <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 bg-blue-400 rounded-full"></span>Journey template creation</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section id="how-it-works" class="py-20 lg:py-28">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <!-- Left: Steps -->
                <div>
                    <span class="inline-block text-xs font-semibold text-amber-600 tracking-widest uppercase mb-4 reveal">How It Works</span>
                    <h2 class="font-serif text-3xl lg:text-4xl font-bold text-ink mb-8 reveal">Get started in minutes</h2>

                    <div class="space-y-8">
                        <!-- Step 1 -->
                        <div class="flex gap-4 reveal">
                            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-gradient-to-br from-amber-400 to-amber-600 text-white flex items-center justify-center font-serif font-bold">1</div>
                            <div>
                                <h3 class="font-semibold text-ink mb-1">Create your account</h3>
                                <p class="text-sm text-ink/60">Register as a student, supervisor, or administrator with your university credentials.</p>
                            </div>
                        </div>

                        <!-- Step 2 -->
                        <div class="flex gap-4 reveal">
                            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-gradient-to-br from-amber-400 to-amber-600 text-white flex items-center justify-center font-serif font-bold">2</div>
                            <div>
                                <h3 class="font-semibold text-ink mb-1">Set up your research journey</h3>
                                <p class="text-sm text-ink/60">Students get assigned a personalized research journey template with milestones and tasks.</p>
                            </div>
                        </div>

                        <!-- Step 3 -->
                        <div class="flex gap-4 reveal">
                            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-gradient-to-br from-amber-400 to-amber-600 text-white flex items-center justify-center font-serif font-bold">3</div>
                            <div>
                                <h3 class="font-semibold text-ink mb-1">Collaborate & track progress</h3>
                                <p class="text-sm text-ink/60">Manage tasks, submit reports, schedule meetings, and get AI assistance throughout your journey.</p>
                            </div>
                        </div>

                        <!-- Step 4 -->
                        <div class="flex gap-4 reveal">
                            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-gradient-to-br from-amber-400 to-amber-600 text-white flex items-center justify-center font-serif font-bold">4</div>
                            <div>
                                <h3 class="font-semibold text-ink mb-1">Submit & graduate</h3>
                                <p class="text-sm text-ink/60">Complete all milestones, submit your thesis, and celebrate your research achievement.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Visual -->
                <div class="relative reveal">
                    <div class="relative bg-white rounded-2xl shadow-xl border border-ink/5 p-8">
                        <!-- Timeline visualization -->
                        <div class="space-y-6">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-full bg-sage-200 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-sage-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <div class="h-3 bg-sage-100 rounded-full overflow-hidden">
                                        <div class="h-full w-full bg-sage-500 rounded-full"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-full bg-sage-200 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-sage-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <div class="h-3 bg-sage-100 rounded-full overflow-hidden">
                                        <div class="h-full w-full bg-sage-500 rounded-full"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-full bg-amber-200 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <div class="h-3 bg-amber-100 rounded-full overflow-hidden">
                                        <div class="h-full w-3/4 bg-gradient-to-r from-amber-400 to-amber-600 rounded-full"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-full bg-ink/10 flex items-center justify-center">
                                    <span class="text-ink/30">4</span>
                                </div>
                                <div class="flex-1">
                                    <div class="h-3 bg-ink/5 rounded-full overflow-hidden">
                                        <div class="h-full w-0 bg-ink/30 rounded-full"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Progress label -->
                        <div class="mt-8 pt-6 border-t border-ink/5 text-center">
                            <p class="text-2xl font-serif font-bold text-ink mb-1">75% Complete</p>
                            <p class="text-sm text-ink/50">Your research journey is progressing well</p>
                        </div>
                    </div>

                    <!-- Decorative element -->
                    <div class="absolute -bottom-4 -right-4 w-full h-full bg-amber-100/50 rounded-2xl -z-10"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 lg:py-28 bg-gradient-to-br from-amber-500 via-amber-600 to-amber-700 relative overflow-hidden">
        <!-- Background pattern -->
        <div class="absolute inset-0 opacity-10">
            <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                <defs>
                    <pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse">
                        <path d="M 10 0 L 0 0 0 10" fill="none" stroke="white" stroke-width="0.5"/>
                    </pattern>
                </defs>
                <rect width="100" height="100" fill="url(#grid)"/>
            </svg>
        </div>

        <div class="max-w-4xl mx-auto px-6 lg:px-8 text-center relative z-10">
            <h2 class="font-serif text-3xl lg:text-5xl font-bold text-white mb-6 reveal">Ready to transform your research supervision?</h2>
            <p class="text-lg text-white/80 mb-10 max-w-2xl mx-auto reveal">Join hundreds of researchers and supervisors who are already streamlining their academic journey with ResearchFlow.</p>
            <div class="flex flex-wrap justify-center gap-4 reveal">
                <a href="/register" class="inline-flex items-center gap-2.5 px-8 py-4 bg-white text-amber-700 text-sm font-bold rounded-xl hover:bg-paper transition-all duration-300 shadow-xl hover:-translate-y-0.5">
                    Start Free Trial
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
                <a href="/login" class="inline-flex items-center gap-2.5 px-8 py-4 bg-transparent text-white text-sm font-semibold rounded-xl border-2 border-white/30 hover:bg-white/10 transition-all duration-300">
                    Sign In
                </a>
            </div>
            <p class="text-white/50 text-xs mt-6 reveal">No credit card required • Start in 2 minutes</p>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-12 bg-ink text-paper">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8 mb-12">
                <!-- Brand -->
                <div class="md:col-span-1">
                    <a href="/" class="flex items-center gap-2.5 mb-4">
                        <div class="w-9 h-9 bg-gradient-to-br from-amber-500 to-amber-700 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                        <span class="font-serif text-lg font-semibold tracking-wide text-white">ResearchFlow</span>
                    </a>
                    <p class="text-sm text-paper/50">The modern platform for academic research supervision.</p>
                </div>

                <!-- Links -->
                <div>
                    <h4 class="text-xs font-semibold text-white uppercase tracking-wider mb-4">Product</h4>
                    <ul class="space-y-2">
                        <li><a href="#features" class="text-sm text-paper/60 hover:text-white transition-colors">Features</a></li>
                        <li><a href="#roles" class="text-sm text-paper/60 hover:text-white transition-colors">For Roles</a></li>
                        <li><a href="#how-it-works" class="text-sm text-paper/60 hover:text-white transition-colors">How It Works</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-xs font-semibold text-white uppercase tracking-wider mb-4">Account</h4>
                    <ul class="space-y-2">
                        <li><a href="/login" class="text-sm text-paper/60 hover:text-white transition-colors">Sign In</a></li>
                        <li><a href="/register" class="text-sm text-paper/60 hover:text-white transition-colors">Register</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-xs font-semibold text-white uppercase tracking-wider mb-4">Legal</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-sm text-paper/60 hover:text-white transition-colors">Privacy Policy</a></li>
                        <li><a href="#" class="text-sm text-paper/60 hover:text-white transition-colors">Terms of Service</a></li>
                    </ul>
                </div>
            </div>

            <div class="pt-8 border-t border-paper/10 flex flex-col md:flex-row items-center justify-between gap-4">
                <p class="text-xs text-paper/40">&copy; 2026 ResearchFlow. All rights reserved.</p>
                <p class="text-xs text-paper/40">Built with care for the academic community.</p>
            </div>
        </div>
    </footer>

    <!-- Scroll reveal script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const reveals = document.querySelectorAll('.reveal');

            function checkScroll() {
                const windowHeight = window.innerHeight;
                reveals.forEach(reveal => {
                    const revealTop = reveal.getBoundingClientRect().top;
                    const revealPoint = 150;

                    if (revealTop < windowHeight - revealPoint) {
                        reveal.classList.add('active');
                    }
                });
            }

            window.addEventListener('scroll', checkScroll);
            checkScroll(); // Initial check
        });
    </script>
</body>
</html>
