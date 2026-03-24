<x-layouts.app title="Timeline - {{ $student->user->name }}">
    <div class="min-h-screen" x-data="timelineOverview({{ $student->id }})" x-init="init()">
        {{-- Toast Notifications --}}
        <div class="fixed top-4 right-4 z-[100] space-y-2">
            <template x-for="notification in notifications" :key="notification.id">
                <div x-show="notification.show"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-x-4"
                     x-transition:enter-end="opacity-100 translate-x-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-x-0"
                     x-transition:leave-end="opacity-0 translate-x-4"
                     :class="{
                         'bg-success text-white': notification.type === 'success',
                         'bg-danger text-white': notification.type === 'error',
                         'bg-warning text-white': notification.type === 'warning'
                     }"
                     class="px-4 py-3 rounded-xl shadow-lg flex items-center gap-3 min-w-[280px] text-sm">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path x-show="notification.type === 'success'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        <path x-show="notification.type === 'error'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span x-text="notification.message"></span>
                    <button @click="dismissNotification(notification.id)" class="opacity-60 hover:opacity-100 ml-auto">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </template>
        </div>

        <div class="flex">
            {{-- Main Content Area --}}
            <div class="flex-1 max-w-[1400px] mx-auto px-6 py-8">
                {{-- Breadcrumb & Student Selector --}}
                <div class="flex items-center justify-between mb-8">
                    <nav class="flex items-center gap-2 text-xs text-tertiary dark:text-dark-tertiary">
                        <a href="{{ route('timeline.index') }}" class="hover:text-primary dark:hover:text-dark-primary transition-colors">Timeline</a>
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        <span class="text-primary dark:text-dark-primary font-medium">{{ $student->user->name }}</span>
                    </nav>

                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open"
                                class="flex items-center gap-3 px-3 py-2 rounded-xl border border-border dark:border-dark-border
                                       hover:border-accent/30 dark:hover:border-dark-accent/30 hover:bg-surface/80 dark:hover:bg-dark-surface/80 transition-all">
                            <x-avatar :name="$student->user->name" size="sm" />
                            <span class="text-sm text-primary dark:text-dark-primary">{{ $student->user->name }}</span>
                            <svg class="w-4 h-4 text-tertiary dark:text-dark-tertiary" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" @click.outside="open = false" x-transition
                             class="absolute right-0 mt-2 w-56 bg-card dark:bg-dark-card border border-border dark:border-dark-border rounded-xl shadow-lg dark:shadow-dark-medium z-50 overflow-hidden">
                            @foreach($students as $studentOption)
                                <a href="{{ route('timeline.show', $studentOption) }}"
                                   class="flex items-center gap-3 px-4 py-3 hover:bg-surface dark:hover:bg-dark-surface transition-colors {{ $student->id === $studentOption->id ? 'bg-accent/5 dark:bg-dark-accent/10' : '' }}">
                                    <x-avatar :name="$studentOption->user->name" size="xs" />
                                    <span class="text-sm text-primary dark:text-dark-primary truncate">{{ $studentOption->user->name }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Page Header --}}
                <div class="mb-8">
                    <h1 class="text-2xl font-semibold text-primary dark:text-dark-primary mb-2">Timeline</h1>
                    <p class="text-sm text-secondary dark:text-dark-secondary">Track your research progress and milestones</p>
                </div>

                {{-- KPI Cards --}}
                <div class="grid grid-cols-3 gap-4 mb-8">
                    <div class="bg-card dark:bg-dark-card rounded-2xl border border-border dark:border-dark-border p-5">
                        <p class="text-xs text-secondary dark:text-dark-secondary mb-1">Total Activities</p>
                        <p class="text-3xl font-semibold text-primary dark:text-dark-primary" x-text="stats.total"></p>
                    </div>
                    <div class="bg-card dark:bg-dark-card rounded-2xl border border-border dark:border-dark-border p-5">
                        <p class="text-xs text-secondary dark:text-dark-secondary mb-1">Completed</p>
                        <p class="text-3xl font-semibold text-success dark:text-dark-success" x-text="stats.completed"></p>
                    </div>
                    <div class="bg-card dark:bg-dark-card rounded-2xl border border-border dark:border-dark-border p-5">
                        <p class="text-xs text-secondary dark:text-dark-secondary mb-1">Progress</p>
                        <div class="flex items-end justify-between">
                            <p class="text-3xl font-semibold text-accent dark:text-dark-accent" x-text="stats.overallProgress + '%'"></p>
                            <div class="w-20 h-2 bg-border-light dark:bg-dark-border-light rounded-full overflow-hidden mb-2">
                                <div class="h-full bg-accent dark:bg-dark-accent rounded-full transition-all duration-500" :style="'width: ' + stats.overallProgress + '%'"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Inline Add Activity (Quick Add) --}}
                <div class="mb-6" x-show="!loading">
                    <div class="flex items-center gap-3 p-2 bg-card dark:bg-dark-card rounded-2xl border border-border dark:border-dark-border focus-within:border-accent/30 dark:focus-within:border-dark-accent/30 focus-within:shadow-sm transition-all">
                        <input type="text" x-model="quickAdd.title" @keyup.enter="quickAddActivity()"
                               placeholder="Quick add activity... (press Enter)"
                               class="flex-1 px-4 py-2.5 text-sm bg-transparent border-0 outline-none text-primary dark:text-dark-primary placeholder-tertiary dark:placeholder-dark-tertiary">
                        <input type="date" x-model="quickAdd.start_date" class="px-3 py-2 text-sm bg-transparent border-0 outline-none text-primary dark:text-dark-primary">
                        <input type="number" x-model="quickAdd.duration_days" min="1" max="365" placeholder="7 days" class="w-20 px-3 py-2 text-sm bg-transparent border-0 outline-none text-primary dark:text-dark-primary placeholder-tertiary dark:placeholder-dark-tertiary">
                        <button @click="quickAddActivity()" :disabled="!quickAdd.title || quickAdd.submitting"
                                class="px-4 py-2 rounded-xl bg-accent dark:bg-dark-accent text-white text-sm font-medium hover:bg-amber-700 dark:hover:bg-amber-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                            <svg x-show="!quickAdd.submitting" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <svg x-show="quickAdd.submitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        </button>
                        <button @click="showAddForm = true" class="px-3 py-2 rounded-xl text-xs text-secondary dark:text-dark-secondary hover:text-primary dark:hover:text-dark-primary hover:bg-surface dark:hover:bg-dark-surface transition-all">
                            More options
                        </button>
                    </div>
                </div>

                {{-- Gantt Chart Section (Main Focus) --}}
                <div class="bg-card dark:bg-dark-card rounded-2xl border border-border dark:border-dark-border overflow-hidden mb-8">
                    {{-- Gantt Header --}}
                    <div class="px-6 py-4 border-b border-border dark:border-dark-border flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <h2 class="text-sm font-medium text-primary dark:text-dark-primary">Timeline</h2>
                            {{-- Inline Legend --}}
                            <div class="flex items-center gap-4 text-xs" x-show="tasks.length > 0">
                                <span class="flex items-center gap-1.5 text-secondary dark:text-dark-secondary">
                                    <span class="w-2.5 h-2.5 rounded-sm bg-success dark:bg-dark-success"></span>
                                    Done
                                </span>
                                <span class="flex items-center gap-1.5 text-secondary dark:text-dark-secondary">
                                    <span class="w-2.5 h-2.5 rounded-sm bg-accent dark:bg-dark-accent"></span>
                                    In Progress
                                </span>
                                <span class="flex items-center gap-1.5 text-secondary dark:text-dark-secondary">
                                    <span class="w-2.5 h-2.5 rounded-sm bg-info dark:bg-dark-info"></span>
                                    Planned
                                </span>
                                <span class="flex items-center gap-1.5 text-secondary dark:text-dark-secondary">
                                    <span class="w-2.5 h-2.5 rounded-full bg-danger dark:bg-dark-danger"></span>
                                    Milestone
                                </span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            {{-- View Toggle --}}
                            <div class="flex items-center bg-surface dark:bg-dark-surface rounded-xl p-1">
                                <template x-for="mode in ['Week', 'Month']" :key="mode">
                                    <button @click="changeViewMode(mode)"
                                            :class="viewMode === mode ? 'bg-card dark:bg-dark-card text-primary dark:text-dark-primary shadow-sm' : 'text-secondary dark:text-dark-secondary hover:text-primary dark:hover:text-dark-primary'"
                                            class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all"
                                            x-text="mode">
                                    </button>
                                </template>
                            </div>
                            <div class="w-px h-6 bg-border dark:bg-dark-border"></div>
                            {{-- Export Dropdown --}}
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" class="flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs text-secondary dark:text-dark-secondary hover:text-primary dark:hover:text-dark-primary hover:bg-surface dark:hover:bg-dark-surface transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    Export
                                </button>
                                <div x-show="open" @click.outside="open = false" x-transition
                                     class="absolute right-0 mt-2 w-36 bg-card dark:bg-dark-card border border-border dark:border-dark-border rounded-xl shadow-lg dark:shadow-dark-medium z-50 overflow-hidden">
                                    <button @click="exportImage(); open = false" class="w-full text-left px-4 py-2 text-xs text-secondary dark:text-dark-secondary hover:text-primary dark:hover:text-dark-primary hover:bg-surface dark:hover:bg-dark-surface transition-all">
                                        Export as Image
                                    </button>
                                    <button @click="exportPdf(); open = false" class="w-full text-left px-4 py-2 text-xs text-secondary dark:text-dark-secondary hover:text-primary dark:hover:text-dark-primary hover:bg-surface dark:hover:bg-dark-surface transition-all">
                                        Export as PDF
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Gantt Chart --}}
                    <div class="p-6" style="min-height: 480px;">
                        <!-- Loading State -->
                        <div x-show="loading" class="flex items-center justify-center h-full">
                            <div class="flex flex-col items-center">
                                <svg class="w-8 h-8 text-accent dark:text-dark-accent animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div x-show="!loading && tasks.length === 0" class="flex flex-col items-center justify-center py-16">
                            <div class="w-16 h-16 rounded-2xl bg-accent/5 dark:bg-dark-accent/10 flex items-center justify-center mb-6">
                                <svg class="w-8 h-8 text-accent dark:text-dark-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg>
                            </div>
                            <h3 class="text-lg font-medium text-primary dark:text-dark-primary mb-2">No activities yet</h3>
                            <p class="text-sm text-secondary dark:text-dark-secondary mb-6 text-center max-w-xs">Add activities to visualize your research timeline</p>
                            <div class="flex items-center gap-3">
                                <button @click="showAddForm = true" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-medium bg-accent dark:bg-dark-accent text-white hover:bg-amber-700 dark:hover:bg-amber-500 transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    Add Activity
                                </button>
                            </div>
                        </div>

                        <!-- Gantt Chart -->
                        <div x-show="!loading && tasks.length > 0" class="gantt-wrapper">
                            <svg id="gantt-chart" class="w-full rounded-xl"></svg>
                        </div>
                    </div>
                </div>

                <!-- Activities List (Simplified) -->
                <div class="bg-card dark:bg-dark-card rounded-2xl border border-border dark:border-dark-border overflow-hidden" x-show="tasks.length > 0">
                    <div class="px-6 py-4 border-b border-border dark:border-dark-border flex items-center justify-between">
                        <h3 class="text-sm font-medium text-primary dark:text-dark-primary">Activities</h3>
                        <input type="text" x-model="searchQuery" placeholder="Search..."
                               class="px-3 py-1.5 text-xs rounded-lg border border-border dark:border-dark-border bg-card dark:bg-dark-surface text-primary dark:text-dark-primary placeholder-tertiary dark:placeholder-dark-tertiary focus:border-accent dark:focus:border-dark-accent focus:outline-none w-40">
                    </div>
                    <div class="divide-y divide-border dark:divide-dark-border max-h-80 overflow-y-auto">
                        <template x-for="task in filteredTasks" :key="task.id">
                            <div class="flex items-center gap-4 px-6 py-3 hover:bg-surface/50 dark:hover:bg-dark-surface/50 transition-colors cursor-pointer" @click="viewTaskDetails(task)">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0"
                                     :class="task.is_milestone ? 'bg-danger/10 dark:bg-dark-danger/15' : 'bg-surface dark:bg-dark-surface'">
                                    <svg class="w-4 h-4" :class="task.is_milestone ? 'text-danger dark:text-dark-danger' : 'text-secondary dark:text-dark-secondary'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path x-show="task.is_milestone" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                        <path x-show="!task.is_milestone" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012 2h2a2 2 0 012 2"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-primary dark:text-dark-primary truncate" x-text="task.name"></p>
                                    <p class="text-xs text-secondary dark:text-dark-secondary mt-0.5" x-text="task.start + ' → ' + task.end"></p>
                                </div>
                                <div class="w-20">
                                    <div class="h-1.5 bg-border-light dark:bg-dark-border-light rounded-full overflow-hidden">
                                        <div class="h-full rounded-full transition-all"
                                             :class="task.progress === 100 ? 'bg-success dark:bg-dark-success' : (task.progress > 50 ? 'bg-accent dark:bg-dark-accent' : 'bg-tertiary dark:bg-dark-tertiary')"
                                             :style="'width: ' + task.progress + '%'"></div>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <div x-show="filteredTasks.length === 0" class="px-6 py-8 text-center text-sm text-secondary dark:text-dark-secondary">
                            No activities found
                        </div>
                    </div>
                </div>

                <!-- Full Add Activity Form (Collapsible) -->
                <div x-show="showAddForm" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="mb-8">
                    <div class="bg-card dark:bg-dark-card rounded-2xl border border-border dark:border-dark-border overflow-hidden">
                        <div class="px-6 py-4 border-b border-border dark:border-dark-border flex items-center justify-between">
                            <h3 class="text-sm font-medium text-primary dark:text-dark-primary">Add Activity</h3>
                            <button @click="showAddForm = false" class="text-secondary dark:text-dark-secondary hover:text-primary dark:hover:text-dark-primary">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <form @submit.prevent="addActivity()" class="p-6 grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs text-secondary dark:text-dark-secondary mb-1.5">Title</label>
                                <input type="text" x-model="form.title" required class="w-full px-4 py-2.5 rounded-xl border border-border dark:border-dark-border bg-card dark:bg-dark-surface text-primary dark:text-dark-primary placeholder-tertiary dark:placeholder-dark-tertiary focus:border-accent dark:focus:border-dark-accent focus:outline-none text-sm" placeholder="Activity name">
                            </div>
                            <div>
                                <label class="block text-xs text-secondary dark:text-dark-secondary mb-1.5">Start Date</label>
                                <input type="date" x-model="form.start_date" required class="w-full px-4 py-2.5 rounded-xl border border-border dark:border-dark-border bg-card dark:bg-dark-surface text-primary dark:text-dark-primary focus:border-accent dark:focus:border-dark-accent focus:outline-none text-sm">
                            </div>
                            <div>
                                <label class="block text-xs text-secondary dark:text-dark-secondary mb-1.5">Duration (days)</label>
                                <input type="number" x-model="form.duration_days" min="1" max="365" required class="w-full px-4 py-2.5 rounded-xl border border-border dark:border-dark-border bg-card dark:bg-dark-surface text-primary dark:text-dark-primary focus:border-accent dark:focus:border-dark-accent focus:outline-none text-sm">
                            </div>
                            <div>
                                <label class="block text-xs text-secondary dark:text-dark-secondary mb-1.5">Parent Task</label>
                                <select x-model="form.parent_task_id" class="w-full px-4 py-2.5 rounded-xl border border-border dark:border-dark-border bg-card dark:bg-dark-surface text-primary dark:text-dark-primary focus:border-accent dark:focus:border-dark-accent focus:outline-none text-sm">
                                    <option value="">None</option>
                                    @foreach($student->tasks->where('is_milestone', true) as $milestone)
                                        <option value="{{ $milestone->id }}">{{ $milestone->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-span-2 flex items-center justify-between">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" x-model="form.is_milestone" class="rounded border-border dark:border-dark-border">
                                    <span class="text-sm text-secondary dark:text-dark-secondary">Mark as milestone</span>
                                </label>
                                <button type="submit" :disabled="submitting" class="px-6 py-2.5 rounded-xl bg-accent dark:bg-dark-accent text-white text-sm font-medium hover:bg-amber-700 dark:hover:bg-amber-500 disabled:opacity-50 transition-all">
                                    <span x-text="submitting ? 'Adding...' : 'Add Activity'"></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Sidebar (Insights) --}}
            <aside class="w-80 border-l border-border dark:border-dark-border bg-surface/30 dark:bg-dark-surface/30 p-6">
                <h3 class="text-sm font-medium text-primary dark:text-dark-primary mb-6">Insights</h3>

                {{-- Next Milestone --}}
                <div class="mb-6">
                    <p class="text-xs text-secondary dark:text-dark-secondary mb-3">Next Milestone</p>
                    <div x-show="nextMilestone" class="p-4 bg-card dark:bg-dark-card rounded-2xl border border-border dark:border-dark-border">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-10 h-10 rounded-xl bg-danger/10 dark:bg-dark-danger/15 flex items-center justify-center">
                                <svg class="w-5 h-5 text-danger dark:text-dark-danger" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-primary dark:text-dark-primary truncate" x-text="nextMilestone?.name"></p>
                                <p class="text-xs text-secondary dark:text-dark-secondary" x-text="nextMilestone?.due_date"></p>
                            </div>
                        </div>
                    </div>
                    <p x-show="!nextMilestone" class="text-xs text-secondary dark:text-dark-secondary italic">No upcoming milestones</p>
                </div>

                {{-- Overdue Tasks --}}
                <div class="mb-6" x-show="overdueTasks.length > 0">
                    <p class="text-xs text-secondary dark:text-dark-secondary mb-3">Overdue</p>
                    <div class="space-y-2">
                        <template x-for="task in overdueTasks.slice(0, 3)" :key="task.id">
                            <div class="p-3 bg-danger/5 dark:bg-dark-danger/10 rounded-xl border border-danger/10 dark:border-dark-danger/20">
                                <p class="text-sm text-primary dark:text-dark-primary truncate" x-text="task.name"></p>
                                <p class="text-xs text-danger dark:text-dark-danger mt-1" x-text="'Due: ' + task.end"></p>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Progress Hints --}}
                <div>
                    <p class="text-xs text-secondary dark:text-dark-secondary mb-3">Progress Tips</p>
                    <div class="space-y-2">
                        <div class="p-4 bg-card dark:bg-dark-card rounded-2xl border border-border dark:border-dark-border" x-show="stats.inProgress > 0">
                            <p class="text-xs text-secondary dark:text-dark-secondary">You have <span class="text-accent dark:text-dark-accent font-medium" x-text="stats.inProgress"></span> tasks in progress</p>
                        </div>
                        <div class="p-4 bg-card dark:bg-dark-card rounded-2xl border border-border dark:border-dark-border" x-show="stats.overallProgress < 25">
                            <p class="text-xs text-secondary dark:text-dark-secondary">Focus on completing planned tasks to boost progress</p>
                        </div>
                        <div class="p-4 bg-card dark:bg-dark-card rounded-2xl border border-border dark:border-dark-border" x-show="stats.overallProgress >= 75">
                            <p class="text-xs text-secondary dark:text-dark-secondary">Great progress! You're almost at the finish line</p>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.1/dist/frappe-gantt.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        function timelineOverview(studentId) {
            return {
                studentId: studentId,
                loading: true,
                submitting: false,
                showAddForm: false,
                ganttInstance: null,
                viewMode: 'Week',
                tasks: [],
                searchQuery: '',
                notificationCounter: 0,
                notifications: [],

                form: {
                    title: '',
                    description: '',
                    start_date: new Date().toISOString().split('T')[0],
                    duration_days: 7,
                    parent_task_id: '',
                    is_milestone: false,
                    progress: 0,
                    priority: null,
                },

                quickAdd: {
                    title: '',
                    start_date: new Date().toISOString().split('T')[0],
                    duration_days: 7,
                    submitting: false,
                },

                errors: {},

                stats: {
                    total: 0,
                    completed: 0,
                    inProgress: 0,
                    milestones: 0,
                    overallProgress: 0,
                },

                get nextMilestone() {
                    const pending = this.tasks.filter(t => t.progress < 100 && (t.custom_class === 'gantt-milestone' || t.is_milestone));
                    return pending.length > 0 ? pending.sort((a, b) => new Date(a.start) - new Date(b.start))[0] : null;
                },

                get overdueTasks() {
                    const now = new Date();
                    return this.tasks.filter(t => t.progress < 100 && new Date(t.end) < now);
                },

                get filteredTasks() {
                    if (!this.searchQuery) return this.tasks;
                    const query = this.searchQuery.toLowerCase();
                    return this.tasks.filter(t => t.name?.toLowerCase().includes(query));
                },

                async init() {
                    await this.loadTasks();
                    this.loading = false;
                },

                async loadTasks() {
                    try {
                        const response = await fetch(`/api/students/${this.studentId}/tasks/gantt`);
                        if (!response.ok) throw new Error('Failed to load tasks');
                        this.tasks = await response.json();
                        this.calculateStats();
                        this.$nextTick(() => this.renderGantt());
                    } catch (error) {
                        console.error('Error loading tasks:', error);
                        this.showNotification('Failed to load timeline data', 'error');
                    }
                },

                calculateStats() {
                    this.stats.total = this.tasks.length;
                    this.stats.completed = this.tasks.filter(t => t.progress === 100).length;
                    this.stats.inProgress = this.tasks.filter(t => t.progress > 0 && t.progress < 100).length;
                    this.stats.milestones = this.tasks.filter(t => t.custom_class === 'gantt-milestone' || t.is_milestone).length;
                    this.stats.overallProgress = this.tasks.length > 0
                        ? Math.round(this.tasks.reduce((sum, t) => sum + (t.progress || 0), 0) / this.tasks.length)
                        : 0;
                },

                renderGantt() {
                    if (this.tasks.length === 0) return;
                    const chartElement = document.getElementById('gantt-chart');
                    if (!chartElement) return;
                    chartElement.innerHTML = '';

                    this.ganttInstance = new Gantt(chartElement, this.tasks, {
                        header_height: 48,
                        column_width: 32,
                        step: 1,
                        view_modes: ['Day', 'Week', 'Month'],
                        bar_height: 32,
                        bar_corner_radius: 6,
                        arrow_curve: 5,
                        padding: 20,
                        view_mode: this.viewMode,
                        date_format: 'YYYY-MM-DD',
                        language: 'en',
                        custom_popup_html: (task) => this.createPopupHtml(task),
                        draggable_progress: true,
                        draggable_update: true,
                        drag_listener: (task, start, end) => this.handleDateChange(task, start, end),
                        progress_change_listener: (task, progress) => this.handleProgressChange(task, progress),
                    });

                    this.$nextTick(() => this.applyGanttStyles());
                },

                createPopupHtml(task) {
                    const isMilestone = task.custom_class === 'gantt-milestone';
                    const isDark = document.documentElement.classList.contains('dark');
                    const popupBg = isDark ? '#1C1C1E' : '#FFFFFF';
                    const popupBorder = isDark ? '#2C2C2E' : '#E5E5E4';
                    const textPrimary = isDark ? '#F5F5F7' : '#1C1917';
                    const textSecondary = isDark ? '#86868B' : '#78716C';
                    const dangerColor = isDark ? '#FF453A' : '#DC2626';

                    return `
                        <div class="gantt-popup" style="background:${popupBg}; border: 1px solid ${popupBorder}; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,${isDark ? '0.3' : '0.1'}); padding: 12px; min-width: 180px; font-family: system-ui; font-size: 13px;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span style="font-weight: 500; font-size: 0.875rem; color: ${textPrimary};">${task.name}</span>
                                ${isMilestone ? `<span style="font-size: 10px; padding: 2px 8px; border-radius: 9999px; background: ${isDark ? 'rgba(255,69,58,0.15)' : 'rgba(220,38,38,0.1)'}; color: ${dangerColor};">Milestone</span>` : ''}
                            </div>
                            <div style="font-size: 0.75rem; color: ${textSecondary};">
                                <p style="margin-bottom: 0.25rem;">${task.start} — ${task.end}</p>
                                <p>Progress: ${task.progress}%</p>
                            </div>
                        </div>
                    `;
                },

                applyGanttStyles() {
                    const styleId = 'timeline-gantt-styles';
                    let styleEl = document.getElementById(styleId);
                    if (!styleEl) {
                        styleEl = document.createElement('style');
                        styleEl.id = styleId;
                        document.head.appendChild(styleEl);
                    }
                    styleEl.textContent = `
                        /* Light mode */
                        .gantt .grid-header { fill: #FAFAF9; stroke: #E5E5E4; }
                        .gantt .grid-row { fill: transparent; }
                        .gantt .tick { stroke: #E5E5E4; }
                        .gantt .today-highlight { fill: rgba(217, 119, 6, 0.08); }
                        .gantt .grid-text { fill: #78716C; font-size: 11px; font-weight: 500; }
                        .gantt .lower-text, .gantt .upper-text { fill: #78716C; }
                        .gantt .bar-label { display: none; }
                        .gantt .arrow { stroke: #A8A29E; stroke-width: 1.5; fill: none; }
                        .gantt .arrow-head { fill: #A8A29E; }

                        /* Dark mode */
                        .dark .gantt .grid-header { fill: #1A1A1A; stroke: #2C2C2E; }
                        .dark .gantt .tick { stroke: #2C2C2E; }
                        .dark .gantt .today-highlight { fill: rgba(255, 159, 10, 0.08); }
                        .dark .gantt .grid-text { fill: #86868B; }
                        .dark .gantt .lower-text, .dark .gantt .upper-text { fill: #86868B; }
                        .dark .gantt .arrow { stroke: #636366; }
                        .dark .gantt .arrow-head { fill: #636366; }

                        /* Task styles */
                        .gantt-milestone .gantt-bar-progress {
                            background: linear-gradient(135deg, #DC2626 0%, #E11D48 100%) !important;
                            border-radius: 50% !important;
                            transform: scale(1.1);
                            box-shadow: 0 0 0 2px rgba(220, 38, 38, 0.1);
                        }
                        .gantt-bar { transition: all 0.15s ease; }
                        .gantt-bar:hover { transform: scaleY(1.05); }
                        .gantt-task-completed .gantt-bar-progress { background: linear-gradient(90deg, #10B981 0%, #059669 100%) !important; }
                        .gantt-task-in_progress .gantt-bar-progress { background: linear-gradient(90deg, #F59E0B 0%, #D97706 100%) !important; }
                        .gantt-task-planned .gantt-bar-progress { background: linear-gradient(90deg, #3B82F6 0%, #2563EB 100%) !important; }
                        .gantt-task-waiting_review .gantt-bar-progress { background: linear-gradient(90deg, #F97316 0%, #EA580C 100%) !important; }
                    `;
                },

                async handleDateChange(task, start, end) {
                    try {
                        const taskId = task.task_id || task.id;
                        await fetch(`/api/tasks/${taskId}/dates`, {
                            method: 'PUT',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.getCsrfToken() },
                            body: JSON.stringify({ start_date: start, due_date: end })
                        });
                        await this.loadTasks();
                        this.showNotification('Dates updated');
                    } catch (error) {
                        this.showNotification('Failed to update dates', 'error');
                    }
                },

                async handleProgressChange(task, progress) {
                    try {
                        const taskId = task.task_id || task.id;
                        await fetch(`/api/tasks/${taskId}/progress`, {
                            method: 'PUT',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.getCsrfToken() },
                            body: JSON.stringify({ progress: progress })
                        });
                        await this.loadTasks();
                    } catch (error) {
                        console.error(error);
                    }
                },

                async addActivity() {
                    this.submitting = true;
                    this.errors = {};

                    const formData = {
                        title: this.form.title.trim(),
                        description: this.form.description?.trim() || null,
                        start_date: this.form.start_date,
                        duration_days: parseInt(this.form.duration_days),
                        parent_task_id: this.form.parent_task_id || null,
                        is_milestone: Boolean(this.form.is_milestone),
                        progress: this.form.is_milestone ? 0 : 0,
                    };

                    try {
                        const response = await fetch(`/api/students/${this.studentId}/tasks/activity`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.getCsrfToken() },
                            body: JSON.stringify(formData)
                        });

                        const data = await response.json();

                        if (response.ok) {
                            await this.loadTasks();
                            this.showAddForm = false;
                            this.resetForm();
                            this.showNotification('Activity added');
                        } else {
                            this.errors = data.errors || {};
                            this.showNotification(data.message || 'Failed to add activity', 'error');
                        }
                    } catch (error) {
                        this.showNotification('Network error', 'error');
                    } finally {
                        this.submitting = false;
                    }
                },

                async quickAddActivity() {
                    if (!this.quickAdd.title.trim()) return;

                    this.quickAdd.submitting = true;

                    const formData = {
                        title: this.quickAdd.title.trim(),
                        start_date: this.quickAdd.start_date,
                        duration_days: parseInt(this.quickAdd.duration_days) || 7,
                        is_milestone: false,
                        progress: 0,
                    };

                    try {
                        const response = await fetch(`/api/students/${this.studentId}/tasks/activity`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.getCsrfToken() },
                            body: JSON.stringify(formData)
                        });

                        if (response.ok) {
                            await this.loadTasks();
                            this.quickAdd.title = '';
                            this.showNotification('Activity added');
                        } else {
                            this.showNotification('Failed to add activity', 'error');
                        }
                    } catch (error) {
                        this.showNotification('Network error', 'error');
                    } finally {
                        this.quickAdd.submitting = false;
                    }
                },

                resetForm() {
                    this.form = {
                        title: '',
                        description: '',
                        start_date: new Date().toISOString().split('T')[0],
                        duration_days: 7,
                        parent_task_id: '',
                        is_milestone: false,
                        progress: 0,
                        priority: null,
                    };
                    this.errors = {};
                },

                changeViewMode(mode) {
                    this.viewMode = mode;
                    if (this.ganttInstance) {
                        this.ganttInstance.change_view_mode(mode);
                    }
                },

                viewTaskDetails(task) {
                    const taskId = task.task_id || task.id;
                    if (taskId) {
                        window.location.href = `/students/${this.studentId}/tasks/${taskId}`;
                    }
                },

                exportImage() {
                    if (!this.ganttInstance) return;
                    const svg = document.querySelector('#gantt-chart svg');
                    if (!svg) return;

                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');
                    const svgData = new XMLSerializer().serializeToString(svg);
                    const svgBlob = new Blob([svgData], { type: 'image/svg+xml;charset=utf-8' });
                    const url = URL.createObjectURL(svgBlob);

                    const img = new Image();
                    img.onload = () => {
                        canvas.width = svg.clientWidth * 2;
                        canvas.height = svg.clientHeight * 2;
                        ctx.scale(2, 2);
                        const isDark = document.documentElement.classList.contains('dark');
                        ctx.fillStyle = isDark ? '#0D0D0D' : '#ffffff';
                        ctx.fillRect(0, 0, canvas.width, canvas.height);
                        ctx.drawImage(img, 0, 0);
                        URL.revokeObjectURL(url);

                        const link = document.createElement('a');
                        link.download = `timeline-${new Date().toISOString().split('T')[0]}.png`;
                        link.href = canvas.toDataURL('image/png');
                        link.click();
                        this.showNotification('Exported as image');
                    };
                    img.src = url;
                },

                exportPdf() {
                    if (!this.ganttInstance) return;
                    const element = document.querySelector('.gantt-wrapper');
                    if (!element) return;

                    html2pdf().set({
                        margin: 10,
                        filename: `timeline-${new Date().toISOString().split('T')[0]}.pdf`,
                        image: { type: 'jpeg', quality: 0.98 },
                        html2canvas: { scale: 2 },
                        jsPDF: { unit: 'mm', format: 'a4', orientation: 'landscape' }
                    }).from(element).save().then(() => {
                        this.showNotification('Exported as PDF');
                    });
                },

                showNotification(message, type = 'success') {
                    const id = ++this.notificationCounter;
                    this.notifications.push({ id, message, type, show: true });
                    setTimeout(() => this.dismissNotification(id), 3000);
                },

                dismissNotification(id) {
                    this.notifications = this.notifications.filter(n => n.id !== id);
                },

                getCsrfToken() {
                    return document.querySelector('meta[name="csrf-token"]')?.content || '';
                }
            };
        }
    </script>

    <style>
        .gantt-wrapper {
            border-radius: 12px;
            padding: 16px;
        }
        /* Light mode gantt wrapper background */
        .gantt-wrapper {
            background: linear-gradient(135deg, #fafafa 0%, #f5f5f4 100%);
        }
        /* Dark mode gantt wrapper background */
        .dark .gantt-wrapper {
            background: linear-gradient(135deg, #141414 0%, #1A1A1A 100%);
        }
    </style>
</x-layouts.app>
