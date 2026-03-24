<x-layouts.app title="Gantt Chart">
    <x-slot:header>
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            <span>Gantt Chart</span>
            <span class="text-xs text-secondary">{{ $student->user->name }}</span>
        </div>
    </x-slot:header>

    <!-- View Toggle Tabs -->
    <div class="flex items-center justify-between mb-5">
        <div class="flex items-center gap-1 bg-surface dark:bg-dark-surface rounded-xl p-1 border border-border dark:border-dark-border">
            <a href="{{ route('tasks.index', $student) }}" class="px-4 py-2 text-xs font-medium rounded-lg text-secondary dark:text-dark-secondary hover:text-primary dark:hover:text-dark-primary transition-all flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
                List
            </a>
            <a href="{{ route('tasks.kanban', $student) }}" class="px-4 py-2 text-xs font-medium rounded-lg text-secondary dark:text-dark-secondary hover:text-primary dark:hover:text-dark-primary transition-all flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7"/>
                </svg>
                Kanban
            </a>
            <a href="{{ route('tasks.gantt', $student) }}" class="px-4 py-2 text-xs font-medium rounded-lg bg-accent dark:bg-dark-accent text-white shadow-sm flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Gantt
            </a>
        </div>
    </div>

    <div x-data="ganttChartApp({{ $student->id }})" x-cloak class="space-y-4">
        <!-- Enhanced Toolbar -->
        <div class="bg-card dark:bg-dark-card rounded-2xl border border-border dark:border-dark-border p-4">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="text-xs font-medium text-secondary dark:text-dark-secondary mr-1">View:</span>
                    <div class="flex items-center gap-0.5 bg-surface dark:bg-dark-surface rounded-lg p-0.5 border border-border-light dark:border-dark-border-light">
                        <button @click="setView('Day')" :class="viewMode === 'Day' ? 'bg-card dark:bg-dark-card text-accent dark:text-dark-accent shadow-sm' : 'text-secondary dark:text-dark-secondary hover:text-primary dark:hover:text-dark-primary'" class="px-2.5 py-1 text-xs font-medium rounded-md transition-all">Day</button>
                        <button @click="setView('Week')" :class="viewMode === 'Week' ? 'bg-card dark:bg-dark-card text-accent dark:text-dark-accent shadow-sm' : 'text-secondary dark:text-dark-secondary hover:text-primary dark:hover:text-dark-primary'" class="px-2.5 py-1 text-xs font-medium rounded-md transition-all">Week</button>
                        <button @click="setView('Month')" :class="viewMode === 'Month' ? 'bg-card dark:bg-dark-card text-accent dark:text-dark-accent shadow-sm' : 'text-secondary dark:text-dark-secondary hover:text-primary dark:hover:text-dark-primary'" class="px-2.5 py-1 text-xs font-medium rounded-md transition-all">Month</button>
                    </div>

                    <div class="w-px h-6 bg-border dark:bg-dark-border"></div>

                    <div class="flex items-center gap-0.5 bg-surface dark:bg-dark-surface rounded-lg p-0.5 border border-border-light dark:border-dark-border-light">
                        <button @click="navigate('prev')" class="p-1.5 rounded-md text-secondary dark:text-dark-secondary hover:text-primary dark:hover:text-dark-primary hover:bg-card dark:hover:bg-dark-card transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        <button @click="navigate('today')" class="px-2 py-1 text-xs font-medium rounded-md text-secondary dark:text-dark-secondary hover:text-primary dark:hover:text-dark-primary hover:bg-card dark:hover:bg-dark-card transition-all">Today</button>
                        <button @click="navigate('next')" class="p-1.5 rounded-md text-secondary dark:text-dark-secondary hover:text-primary dark:hover:text-dark-primary hover:bg-card dark:hover:bg-dark-card transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>

                    <div class="w-px h-6 bg-border dark:bg-dark-border"></div>

                    <div class="flex items-center gap-0.5 bg-surface dark:bg-dark-surface rounded-lg p-0.5 border border-border-light dark:border-dark-border-light">
                        <button @click="zoom('out')" class="p-1.5 rounded-md text-secondary dark:text-dark-secondary hover:text-primary dark:hover:text-dark-primary hover:bg-card dark:hover:bg-dark-card transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                        </button>
                        <button @click="zoom('reset')" class="px-2 py-1 text-xs font-medium rounded-md text-secondary dark:text-dark-secondary hover:text-primary dark:hover:text-dark-primary hover:bg-card dark:hover:bg-dark-card transition-all">Fit</button>
                        <button @click="zoom('in')" class="p-1.5 rounded-md text-secondary dark:text-dark-secondary hover:text-primary dark:hover:text-dark-primary hover:bg-card dark:hover:bg-dark-card transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </button>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <div class="hidden sm:flex items-center gap-2">
                        <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-success/10 dark:bg-dark-success/15 text-success dark:text-dark-success text-xs font-medium">
                            <span x-text="taskStats.completed || 0">0</span> Done
                        </div>
                        <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-warning/10 dark:bg-dark-warning/15 text-warning dark:text-dark-warning text-xs font-medium">
                            <span x-text="taskStats.inProgress || 0">0</span> Active
                        </div>
                    </div>

                    <button @click="refresh()" class="p-2 text-secondary dark:text-dark-secondary hover:text-primary dark:hover:text-dark-primary hover:bg-surface dark:hover:bg-dark-surface rounded-xl transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </button>

                    <!-- New Task Button - Opens Modal -->
                    <button @click="openTaskModal()" class="flex items-center gap-1.5 px-3 py-2 text-xs font-medium rounded-lg bg-accent dark:bg-dark-accent text-white hover:bg-amber-700 dark:hover:bg-amber-500 transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        New Task
                    </button>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div x-show="loading" class="flex flex-col items-center justify-center py-20">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-accent/20 dark:from-dark-accent/20 to-accent/5 dark:to-dark-accent/5 flex items-center justify-center">
                <svg class="w-6 h-6 text-accent dark:text-dark-accent animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </div>
            <p class="text-sm text-secondary dark:text-dark-secondary mt-4">Loading timeline...</p>
        </div>

        <!-- Split Layout Gantt Container -->
        <div x-show="!loading" class="bg-card dark:bg-dark-card rounded-2xl border border-border dark:border-dark-border overflow-hidden shadow-soft dark:shadow-dark-soft">
            <!-- Header Row -->
            <div class="flex border-b border-border dark:border-dark-border bg-surface/50 dark:bg-dark-surface/50">
                <div class="flex-shrink-0 w-[260px] px-4 py-3 border-r border-border dark:border-dark-border">
                    <span class="text-xs font-semibold text-secondary dark:text-dark-secondary uppercase tracking-wider">Task</span>
                </div>
                <div class="flex-1 overflow-hidden">
                    <div id="gantt-header-container" class="h-12"></div>
                </div>
            </div>

            <!-- Content Row -->
            <div class="flex" style="max-height: 600px;">
                <!-- Left Panel: Task List -->
                <div class="flex-shrink-0 w-[260px] border-r border-border dark:border-dark-border bg-card dark:bg-dark-card overflow-y-auto">
                    <div class="divide-y divide-border dark:divide-dark-border">
                        <template x-for="(task, index) in tasks" :key="task.id">
                            <a :href="`/students/${studentId}/tasks/${task.task_id}`"
                               class="flex items-center gap-3 p-4 hover:bg-surface dark:hover:bg-dark-surface transition-colors"
                               style="min-height: 56px;">
                                <span class="flex-shrink-0 w-2 h-2 rounded-full"
                                      :style="`background-color: ${statusColors[task.status] || '#9CA3AF'}`"></span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-primary dark:text-dark-primary truncate" x-text="task.name"></p>
                                    <p class="text-xs text-secondary dark:text-dark-secondary" x-text="formatDateRange(task.start, task.end)"></p>
                                </div>
                                <span class="text-xs text-tertiary dark:text-dark-tertiary" x-text="`${task.progress || 0}%`"></span>
                            </a>
                        </template>
                    </div>

                    <!-- Empty State -->
                    <div x-show="tasks.length === 0" class="flex flex-col items-center justify-center py-16 text-center px-4">
                        <svg class="w-12 h-12 text-tertiary dark:text-dark-tertiary mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                        </svg>
                        <h3 class="text-sm font-medium text-primary dark:text-dark-primary mb-1">No tasks yet</h3>
                        <p class="text-xs text-secondary dark:text-dark-secondary mb-4">Click "New Task" or click on the timeline to create tasks</p>
                        <button @click="openTaskModal()" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium rounded-lg bg-accent dark:bg-dark-accent text-white hover:bg-amber-700 dark:hover:bg-amber-500 transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Create First Task
                        </button>
                    </div>
                </div>

                <!-- Right Panel: Gantt Timeline -->
                <div class="flex-1 overflow-auto" id="gantt-timeline-wrapper">
                    <div id="gantt-chart-container" class="min-w-[1200px]" style="min-height: 400px; cursor: crosshair;" @click="handleTimelineClick"></div>
                </div>
            </div>
        </div>

        <!-- Instructions -->
        <div x-show="!loading && tasks.length > 0" class="flex items-center justify-center gap-6 px-2 py-2">
            <span class="flex items-center gap-1.5 text-xs text-secondary dark:text-dark-secondary">
                <svg class="w-4 h-4 text-accent dark:text-dark-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Drag task ends to adjust dates
            </span>
            <span class="flex items-center gap-1.5 text-xs text-secondary dark:text-dark-secondary">
                <svg class="w-4 h-4 text-accent dark:text-dark-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/></svg>
                Click on timeline to create task
            </span>
            <span class="flex items-center gap-1.5 text-xs text-secondary dark:text-dark-secondary">
                <svg class="w-4 h-4 text-accent dark:text-dark-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/></svg>
                Click task to view details
            </span>
        </div>
    </div>

    <!-- Task Creation Modal -->
    <div x-show="showTaskModal" style="display: none;" x-cloak>
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <!-- Backdrop -->
            <div x-show="showTaskModal" x-transition:enter="transition-opacity ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="absolute inset-0 bg-primary/20 dark:bg-dark-primary/20 backdrop-blur-sm" @click="closeTaskModal()"></div>

            <!-- Modal Content -->
            <div x-show="showTaskModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="relative bg-card dark:bg-dark-card rounded-2xl border border-border dark:border-dark-border shadow-xl dark:shadow-dark-medium w-full max-w-lg">
                <!-- Header -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-border dark:border-dark-border">
                    <div>
                        <h3 class="text-base font-semibold text-primary dark:text-dark-primary">Create New Task</h3>
                        <p class="text-xs text-secondary dark:text-dark-secondary mt-0.5">Fill in the task details below</p>
                    </div>
                    <button @click="closeTaskModal()" class="p-2 text-secondary dark:text-dark-secondary hover:text-primary dark:hover:text-dark-primary hover:bg-surface dark:hover:bg-dark-surface rounded-xl transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Form -->
                <form @submit.prevent="createTask()" class="p-6 space-y-4">
                    @csrf
                    <input type="hidden" name="student_id" :value="studentId">

                    <!-- Task Name -->
                    <div>
                        <label class="block text-xs font-medium text-secondary dark:text-dark-secondary mb-1.5">Task Name *</label>
                        <input type="text" name="name" x-model="newTask.name" required
                            placeholder="Enter task name..."
                            class="w-full px-3 py-2 text-sm text-primary dark:text-dark-primary bg-card dark:bg-dark-surface border border-border dark:border-dark-border rounded-lg focus:ring-2 focus:ring-accent/20 dark:focus:ring-dark-accent/20 focus:border-accent dark:focus:border-dark-accent transition-all placeholder:text-tertiary dark:placeholder:text-dark-tertiary">
                    </div>

                    <!-- Dates Row -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-secondary dark:text-dark-secondary mb-1.5">Start Date *</label>
                            <input type="date" name="start_date" x-model="newTask.start_date" required
                                class="w-full px-3 py-2 text-sm text-primary dark:text-dark-primary bg-card dark:bg-dark-surface border border-border dark:border-dark-border rounded-lg focus:ring-2 focus:ring-accent/20 dark:focus:ring-dark-accent/20 focus:border-accent dark:focus:border-dark-accent transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-secondary dark:text-dark-secondary mb-1.5">Due Date *</label>
                            <input type="date" name="due_date" x-model="newTask.due_date" required
                                class="w-full px-3 py-2 text-sm text-primary dark:text-dark-primary bg-card dark:bg-dark-surface border border-border dark:border-dark-border rounded-lg focus:ring-2 focus:ring-accent/20 dark:focus:ring-dark-accent/20 focus:border-accent dark:focus:border-dark-accent transition-all">
                        </div>
                    </div>

                    <!-- Priority & Status -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-secondary dark:text-dark-secondary mb-1.5">Priority</label>
                            <select name="priority" x-model="newTask.priority" class="w-full px-3 py-2 text-sm text-primary dark:text-dark-primary bg-card dark:bg-dark-surface border border-border dark:border-dark-border rounded-lg focus:ring-2 focus:ring-accent/20 dark:focus:ring-dark-accent/20 focus:border-accent dark:focus:border-dark-accent transition-all">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-secondary dark:text-dark-secondary mb-1.5">Status</label>
                            <select name="status" x-model="newTask.status" class="w-full px-3 py-2 text-sm text-primary dark:text-dark-primary bg-card dark:bg-dark-surface border border-border dark:border-dark-border rounded-lg focus:ring-2 focus:ring-accent/20 dark:focus:ring-dark-accent/20 focus:border-accent dark:focus:border-dark-accent transition-all">
                                <option value="planned" selected>Planned</option>
                                <option value="in_progress">In Progress</option>
                            </select>
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-xs font-medium text-secondary dark:text-dark-secondary mb-1.5">Description</label>
                        <textarea name="description" x-model="newTask.description" rows="3"
                            placeholder="Task description..."
                            class="w-full px-3 py-2 text-sm text-primary dark:text-dark-primary bg-card dark:bg-dark-surface border border-border dark:border-dark-border rounded-lg focus:ring-2 focus:ring-accent/20 dark:focus:ring-dark-accent/20 focus:border-accent dark:focus:border-dark-accent transition-all resize-none placeholder:text-tertiary dark:placeholder:text-dark-tertiary"></textarea>
                    </div>

                    <!-- Milestone Selection (Optional) -->
                    <div>
                        <label class="block text-xs font-medium text-secondary dark:text-dark-secondary mb-1.5">Milestone (Optional)</label>
                        <select name="milestone_id" x-model="newTask.milestone_id" class="w-full px-3 py-2 text-sm text-primary dark:text-dark-primary bg-card dark:bg-dark-surface border border-border dark:border-dark-border rounded-lg focus:ring-2 focus:ring-accent/20 dark:focus:ring-dark-accent/20 focus:border-accent dark:focus:border-dark-accent transition-all">
                            <option value="">No milestone</option>
                            @foreach($milestones ?? [] as $milestone)
                            <option value="{{ $milestone->id }}">{{ $milestone->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Error Message -->
                    <div x-show="taskError" x-cloak class="p-3 rounded-lg bg-danger/10 dark:bg-dark-danger/15 text-danger dark:text-dark-danger text-xs">
                        <span x-text="taskError"></span>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" @click="closeTaskModal()" class="px-4 py-2 text-sm font-medium text-secondary dark:text-dark-secondary hover:text-primary dark:hover:text-dark-primary border border-border dark:border-dark-border hover:bg-surface dark:hover:bg-dark-surface rounded-lg transition-all">
                            Cancel
                        </button>
                        <button type="submit" :disabled="taskSubmitting" class="flex items-center gap-1.5 px-4 py-2 text-sm font-medium rounded-lg bg-accent dark:bg-dark-accent text-white hover:bg-amber-700 dark:hover:bg-amber-500 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg x-show="!taskSubmitting" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <svg x-show="taskSubmitting" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Create Task
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.1/dist/frappe-gantt.min.css">
    <style>
        .gantt-task-list::-webkit-scrollbar { width: 4px; }
        .gantt-task-list::-webkit-scrollbar-track { background: transparent; }
        .gantt-task-list::-webkit-scrollbar-thumb { background-color: #E5E5E4; border-radius: 4px; }
        .gantt-timeline::-webkit-scrollbar { width: 8px; height:8px; }
        .gantt-timeline::-webkit-scrollbar-track { background: #FAFAF9; }
        .gantt-timeline::-webkit-scrollbar-thumb { background-color: #E5E5E4; border-radius: 4px; }

        /* Dark mode scrollbars */
        .dark .gantt-task-list::-webkit-scrollbar-thumb { background-color: #2C2C2E; }
        .dark .gantt-timeline::-webkit-scrollbar-track { background: #1A1A1A; }
        .dark .gantt-timeline::-webkit-scrollbar-thumb { background-color: #2C2C2E; }
    </style>
    @endpush

    @push('scripts')
    <script>
        // Define Gantt component using Alpine's init event
        document.addEventListener('alpine:init', () => {
            window.ganttChartApp = function(studentId) {
                return {
                    loading: true,
                    tasks: [],
                    ganttInstance: null,
                    studentId: studentId,
                    viewMode: 'Month',
                    taskStats: { total: 0, completed: 0, inProgress: 0 },
                    statusColors: {
                        'planned': '#3B82F6',
                        'in_progress': '#F59E0B',
                        'waiting_review': '#F97316',
                        'revision': '#8B5CF6',
                        'completed': '#10B981'
                    },
                    showTaskModal: false,
                    taskSubmitting: false,
                    taskError: '',
                    newTask: {
                        name: '',
                        start_date: '',
                        due_date: '',
                        priority: 'medium',
                        status: 'planned',
                        description: '',
                        milestone_id: ''
                    },

                    init() {
                        console.log('Gantt: init called with studentId:', this.studentId);
                        this.loadGanttLibrary().then(() => {
                            return this.loadTasks();
                        }).then(() => {
                            this.loading = false;
                            this.$nextTick(() => {
                                this.renderGantt();
                                this.initScrollSync();
                            });
                        }).catch((error) => {
                            console.error('Gantt init failed:', error);
                            this.loading = false;
                        });
                    },

                    loadGanttLibrary() {
                        return new Promise((resolve, reject) => {
                            if (typeof Gantt !== 'undefined') {
                                resolve();
                                return;
                            }
                            const css = document.createElement('link');
                            css.rel = 'stylesheet';
                            css.href = 'https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.1/dist/frappe-gantt.min.css';
                            document.head.appendChild(css);
                            const script = document.createElement('script');
                            script.src = 'https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.1/dist/frappe-gantt.min.js';
                            script.onload = resolve;
                            script.onerror = reject;
                            document.head.appendChild(script);
                        });
                    },

                    async loadTasks() {
                        const response = await axios.get('/api/students/' + this.studentId + '/tasks/gantt');
                        this.tasks = response.data.sort((a, b) => {
                            if (a.start !== b.start) return new Date(a.start) - new Date(b.start);
                            return a.name.localeCompare(b.name);
                        });
                        this.taskStats = {
                            total: this.tasks.length,
                            completed: this.tasks.filter(t => t.progress === 100).length,
                            inProgress: this.tasks.filter(t => t.progress > 0 && t.progress < 100).length
                        };
                    },

                    renderGantt() {
                        const container = document.getElementById('gantt-chart-container');
                        if (!container) return;

                        this.ganttInstance = new Gantt(container, this.tasks, {
                            view_mode: this.viewMode,
                            date_format: 'YYYY-MM-DD',
                            header_height: 48,
                            bar_height: 28,
                            bar_corner_radius: 4,
                            padding: 14,
                            language: 'en',
                            show_dates: true,
                            on_date_change: (task, start, end) => this.handleDateChange(task, start, end),
                            on_progress_change: (task, progress) => this.handleProgressChange(task, progress),
                            on_click: (task) => {
                                const taskId = task.id ? task.id.replace('task-', '') : task.task_id;
                                if (taskId) {
                                    window.location.href = '/students/' + this.studentId + '/tasks/' + taskId;
                                }
                            },
                            custom_popup_html: (task) => {
                                const taskId = task.id ? task.id.replace('task-', '') : task.task_id;
                                const statusLabel = task.status ? task.status.replace('_', ' ').replace(/^\w/, c => c.toUpperCase()) : 'Planned';
                                const statusColor = this.statusColors[task.status] || '#3B82F6';
                                const isDark = document.documentElement.classList.contains('dark');
                                const popupBg = isDark ? '#1C1C1E' : '#FFFFFF';
                                const popupBorder = isDark ? '#2C2C2E' : '#E5E5E4';
                                const textPrimary = isDark ? '#F5F5F7' : '#1C1917';
                                const textSecondary = isDark ? '#86868B' : '#78716C';
                                const accentColor = isDark ? '#FF9F0A' : '#D97706';
                                const accentBg = isDark ? 'rgba(255, 159, 10, 0.15)' : 'rgba(217, 119, 6, 0.1)';
                                return '<div class="gantt-popup" style="background:' + popupBg + '; border: 1px solid ' + popupBorder + '; border-radius: 0.5rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,' + (isDark ? '0.3' : '0.1') + '); padding: 1rem; min-width: 220px; font-family: system-ui, -apple-system, sans-serif;">' +
                                    '<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">' +
                                    '<span style="font-weight: 600; font-size: 0.875rem; color: ' + textPrimary + ';">' + task.name + '</span>' +
                                    '<span style="font-size: 10px; padding: 2px 8px; border-radius: 9999px; background-color: ' + statusColor + '20; color: ' + statusColor + ';">' + statusLabel + '</span>' +
                                    '</div>' +
                                    '<div style="font-size: 0.75rem; color: ' + textSecondary + ';">' +
                                    '<div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.375rem;">' +
                                    '<svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>' +
                                    '<span>' + task.start + ' → ' + task.end + '</span>' +
                                    '</div>' +
                                    '<div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem;">' +
                                    '<svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>' +
                                    '<span>Progress: ' + task.progress + '%</span>' +
                                    '</div>' +
                                    '</div>' +
                                    '<a href="/students/' + this.studentId + '/tasks/' + taskId + '" style="display: block; width: 100%; text-align: center; font-size: 0.75rem; font-weight: 500; color: ' + accentColor + '; padding: 0.5rem; border-radius: 0.5rem; background: ' + accentBg + '; text-decoration: none;">View Details →</a>' +
                                    '</div>';
                            }
                        });
                        this.applyCustomStyles();
                    },

                    applyCustomStyles() {
                        const styleId = 'gantt-custom-styles';
                        let styleEl = document.getElementById(styleId);
                        if (!styleEl) {
                            styleEl = document.createElement('style');
                            styleEl.id = styleId;
                            document.head.appendChild(styleEl);
                        }
                        styleEl.textContent =
                            /* Light mode */
                            '.gantt .bar { fill: #D97706; cursor: pointer; transition: filter 0.2s; }\n' +
                            '.gantt .bar:hover { filter: brightness(1.1); }\n' +
                            '.gantt .bar-progress { fill: #B45309; }\n' +
                            '.gantt-completed .bar { fill: #10B981; }\n' +
                            '.gantt-completed .bar-progress { fill: #059669; }\n' +
                            '.gantt-in-progress .bar { fill: #F59E0B; }\n' +
                            '.gantt-in-progress .bar-progress { fill: #D97706; }\n' +
                            '.gantt-waiting-review .bar { fill: #F97316; }\n' +
                            '.gantt-waiting-review .bar-progress { fill: #EA580C; }\n' +
                            '.gantt-revision .bar { fill: #8B5CF6; }\n' +
                            '.gantt-revision .bar-progress { fill: #7C3AED; }\n' +
                            '.gantt-planned .bar { fill: #3B82F6; }\n' +
                            '.gantt-planned .bar-progress { fill: #2563EB; }\n' +
                            '.gantt .bar-label { display: none; }\n' +
                            '.gantt .grid-header { fill: #FAFAF9; stroke: #E5E5E4; }\n' +
                            '.gantt .grid-row { fill: transparent; }\n' +
                            '.gantt .tick { stroke: #E5E5E4; }\n' +
                            '.gantt .today-highlight { fill: rgba(217, 119, 6, 0.08); }\n' +
                            '.gantt .grid-text { fill: #78716C; font-size: 11px; font-weight: 500; }\n' +
                            '.gantt .arrow { stroke: #A8A29E; stroke-width: 1.5; fill: none; }\n' +
                            '.gantt .arrow-head { fill: #A8A29E; }\n' +
                            '.gantt-popup { font-family: system-ui, -apple-system, sans-serif; z-index: 1000; }\n' +
                            /* Dark mode overrides */
                            '.dark .gantt .grid-header { fill: #1A1A1A; stroke: #2C2C2E; }\n' +
                            '.dark .gantt .grid-row { fill: transparent; }\n' +
                            '.dark .gantt .tick { stroke: #2C2C2E; }\n' +
                            '.dark .gantt .today-highlight { fill: rgba(255, 159, 10, 0.08); }\n' +
                            '.dark .gantt .grid-text { fill: #86868B; }\n' +
                            '.dark .gantt .arrow { stroke: #636366; }\n' +
                            '.dark .gantt .arrow-head { fill: #636366; }\n' +
                            '.dark .gantt .lower-text, .dark .gantt .upper-text { fill: #86868B; }';
                    },

                    initScrollSync() {
                        const taskList = document.querySelector('.gantt-task-list');
                        const timeline = document.querySelector('.gantt-timeline');
                        if (!taskList || !timeline) return;
                        let isScrolling = false;
                        taskList.addEventListener('scroll', () => {
                            if (!isScrolling) {
                                isScrolling = true;
                                timeline.scrollTop = taskList.scrollTop;
                                setTimeout(() => isScrolling = false, 50);
                            }
                        });
                        timeline.addEventListener('scroll', () => {
                            if (!isScrolling) {
                                isScrolling = true;
                                taskList.scrollTop = timeline.scrollTop;
                                setTimeout(() => isScrolling = false, 50);
                            }
                        });
                    },

                    handleDateChange(task, start, end) {
                        const startDate = start.toISOString().split('T')[0];
                        const dueDate = end.toISOString().split('T')[0];
                        const taskId = task.id.replace('task-', '');
                        axios.put('/api/tasks/' + taskId + '/dates', { start_date: startDate, due_date: dueDate })
                            .then(() => this.showNotification('Dates updated'));
                    },

                    handleProgressChange(task, progress) {
                        const taskId = task.id.replace('task-', '');
                        axios.put('/api/tasks/' + taskId + '/progress', { progress })
                            .then(() => this.showNotification('Progress updated to ' + progress + '%'));
                    },

                    setView(mode) {
                        this.viewMode = mode;
                        if (this.ganttInstance) {
                            this.ganttInstance.change_view_mode(mode);
                        }
                    },

                    navigate(direction) {
                        if (!this.ganttInstance) return;
                        const shifts = { 'Day': 1, 'Week': 7, 'Month': 30 };
                        const days = shifts[this.viewMode] || 7;
                        if (direction === 'prev') {
                            this.ganttInstance.gantt_start.setDate(this.ganttInstance.gantt_start.getDate() - days);
                            this.ganttInstance.gantt_end.setDate(this.ganttInstance.gantt_end.getDate() - days);
                        } else if (direction === 'next') {
                            this.ganttInstance.gantt_start.setDate(this.ganttInstance.gantt_start.getDate() + days);
                            this.ganttInstance.gantt_end.setDate(this.ganttInstance.gantt_end.getDate() + days);
                        } else if (direction === 'today') {
                            const today = new Date();
                            this.ganttInstance.gantt_start = new Date(today);
                            this.ganttInstance.gantt_start.setDate(today.getDate() - days);
                            this.ganttInstance.gantt_end = new Date(today);
                            this.ganttInstance.gantt_end.setDate(today.getDate() + days * 2);
                        }
                    },

                    refresh() {
                        this.renderGantt();
                    },

                    zoom(level) {
                        const modes = ['Day', 'Week', 'Month'];
                        const currentIndex = modes.indexOf(this.viewMode);
                        if (level === 'in') {
                            this.setView(modes[Math.max(0, currentIndex - 1)]);
                        } else if (level === 'out') {
                            this.setView(modes[Math.min(modes.length - 1, currentIndex + 1)]);
                        } else if (level === 'reset') {
                            this.setView('Month');
                        }
                    },

                    showNotification(message) {
                        const notification = document.createElement('div');
                        notification.className = 'fixed bottom-4 right-4 px-4 py-3 rounded-xl shadow-lg text-sm font-medium z-50 flex items-center gap-2 bg-success text-white';
                        notification.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>' + message;
                        document.body.appendChild(notification);
                        setTimeout(() => notification.remove(), 3000);
                    },

                    formatDateRange(start, end) {
                        const startDate = new Date(start);
                        const endDate = new Date(end);
                        if (startDate.getMonth() === endDate.getMonth() && startDate.getFullYear() === endDate.getFullYear()) {
                            return startDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) + ' - ' + endDate.getDate();
                        }
                        return startDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) + ' - ' + endDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                    },

                    // Task Modal Functions
                    openTaskModal(preselectedDate = null) {
                        this.showTaskModal = true;
                        this.taskError = '';

                        if (preselectedDate) {
                            this.newTask.start_date = preselectedDate;
                            this.newTask.due_date = preselectedDate;
                        } else {
                            // Default to today and 7 days from now
                            const today = new Date().toISOString().split('T')[0];
                            const nextWeek = new Date();
                            nextWeek.setDate(nextWeek.getDate() + 7);
                            this.newTask.start_date = today;
                            this.newTask.due_date = nextWeek.toISOString().split('T')[0];
                        }

                        // Focus on name field after modal opens
                        this.$nextTick(() => {
                            document.querySelector('input[name="name"]')?.focus();
                        });
                    },

                    closeTaskModal() {
                        this.showTaskModal = false;
                        this.taskError = '';
                        this.resetTaskForm();
                    },

                    resetTaskForm() {
                        this.newTask = {
                            name: '',
                            start_date: '',
                            due_date: '',
                            priority: 'medium',
                            status: 'planned',
                            description: '',
                            milestone_id: ''
                        };
                    },

                    async createTask() {
                        this.taskSubmitting = true;
                        this.taskError = '';

                        try {
                            const response = await axios.post('/students/' + this.studentId + '/tasks', this.newTask);

                            this.showNotification('Task created successfully!');
                            this.closeTaskModal();

                            // Reload tasks and re-render
                            await this.loadTasks();
                            this.renderGantt();
                        } catch (error) {
                            console.error('Task creation failed:', error);
                            this.taskError = error.response?.data?.message || 'Failed to create task. Please try again.';
                        } finally {
                            this.taskSubmitting = false;
                        }
                    },

                    // Handle click on timeline to create task
                    handleTimelineClick(event) {
                        // Check if click was on a task bar or empty space
                        const target = event.target;
                        if (target.closest('.bar-wrapper') || target.closest('.bar')) {
                            return; // Don't trigger if clicking on a task
                        }

                        // Get the clicked date from the Gantt chart
                        const container = document.getElementById('gantt-chart-container');
                        if (!container || !this.ganttInstance) return;

                        // Calculate the date from click position
                        const rect = container.getBoundingClientRect();
                        const clickX = event.clientX - rect.left + document.getElementById('gantt-timeline-wrapper').scrollLeft;

                        // Estimate date from position (this is approximate)
                        const daysInView = this.getDaysInView();
                        const startDate = new Date(this.ganttInstance.gantt_start);
                        const dayWidth = rect.width / daysInView;
                        const dayOffset = Math.floor(clickX / dayWidth);

                        const clickedDate = new Date(startDate);
                        clickedDate.setDate(startDate.getDate() + dayOffset);

                        const dateStr = clickedDate.toISOString().split('T')[0];

                        // Open modal with pre-selected date
                        this.openTaskModal(dateStr);
                    },

                    getDaysInView() {
                        const start = new Date(this.ganttInstance.gantt_start);
                        const end = new Date(this.ganttInstance.gantt_end);
                        const diffTime = Math.abs(end - start);
                        return Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                    }
                };
                console.log('Gantt: ganttChartApp function registered on window');
            });
        });
    </script>
    @endpush
</x-layouts.app>
