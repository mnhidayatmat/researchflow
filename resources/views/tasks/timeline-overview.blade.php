<x-layouts.app title="Timeline Overview" :header="'Gantt Timeline Overview'">
    <div class="max-w-[1600px] mx-auto" x-data="timelineOverview({{ $student->id }})" x-init="init()">
        {{-- Page Header --}}
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-6">
            <div>
                <h2 class="text-base font-semibold text-primary">Gantt Timeline Overview</h2>
                <p class="text-xs text-secondary mt-0.5">
                    {{ $student->user->name }} · {{ $student->programme->name ?? 'Research' }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                <button @click="exportImage()" :disabled="loading || !ganttInstance"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium
                               text-secondary hover:text-primary border border-border hover:bg-surface
                               disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Export Image
                </button>
                <button @click="exportPdf()" :disabled="loading || !ganttInstance"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium
                               text-secondary hover:text-primary border border-border hover:bg-surface
                               disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    Export PDF
                </button>
            </div>
        </div>

        <div class="grid lg:grid-cols-4 gap-6">
            {{-- Left Panel: Activity Form --}}
            <div class="lg:col-span-1 space-y-6">
                {{-- Add Activity Card --}}
                <div class="bg-card rounded-2xl border border-border overflow-hidden">
                    <div class="px-6 py-4 border-b border-border">
                        <h3 class="text-sm font-semibold text-primary">Add Activity</h3>
                    </div>
                    <form @submit.prevent="addActivity()" class="p-6 space-y-4">
                        {{-- Title --}}
                        <div>
                            <label class="block text-xs font-medium text-secondary mb-1.5">Activity Title</label>
                            <input type="text" x-model="form.title" required
                                   class="w-full px-4 py-2.5 rounded-xl border border-border
                                          focus:border-accent focus:ring-2 focus:ring-accent/20 outline-none
                                          text-sm text-primary placeholder-tertiary"
                                   placeholder="e.g., Literature Review">
                        </div>

                        {{-- Description --}}
                        <div>
                            <label class="block text-xs font-medium text-secondary mb-1.5">Description</label>
                            <textarea x-model="form.description" rows="2"
                                      class="w-full px-4 py-2.5 rounded-xl border border-border
                                             focus:border-accent focus:ring-2 focus:ring-accent/20 outline-none
                                             text-sm text-primary placeholder-tertiary resize-none"
                                      placeholder="Optional description..."></textarea>
                        </div>

                        {{-- Start Date --}}
                        <div>
                            <label class="block text-xs font-medium text-secondary mb-1.5">Start Date</label>
                            <input type="date" x-model="form.start_date" required
                                   @change="calculateDuration()"
                                   class="w-full px-4 py-2.5 rounded-xl border border-border
                                          focus:border-accent focus:ring-2 focus:ring-accent/20 outline-none
                                          text-sm text-primary">
                        </div>

                        {{-- Duration --}}
                        <div>
                            <label class="block text-xs font-medium text-secondary mb-1.5">
                                Duration: <span x-text="form.duration_days"></span> days
                            </label>
                            <input type="range" min="1" max="90" x-model="form.duration_days"
                                   @change="calculateDuration()"
                                   class="w-full h-2 bg-border-light rounded-xl appearance-none cursor-pointer accent-accent">
                        </div>

                        {{-- Milestone Dropdown --}}
                        <div>
                            <label class="block text-xs font-medium text-secondary mb-1.5">Link to Milestone</label>
                            <select x-model="form.milestone_id"
                                    class="w-full px-4 py-2.5 rounded-xl border border-border
                                           focus:border-accent focus:ring-2 focus:ring-accent/20 outline-none
                                           text-sm text-primary bg-card">
                                <option value="">None</option>
                                <template x-for="milestone in milestones" :key="milestone.id">
                                    <option :value="milestone.id" x-text="milestone.name"></option>
                                </template>
                            </select>
                        </div>

                        {{-- Is Milestone Toggle --}}
                        <div class="flex items-center justify-between p-3 bg-surface rounded-xl">
                            <div>
                                <span class="text-sm font-medium text-primary">Mark as Milestone</span>
                                <p class="text-xs text-secondary mt-0.5">Key project milestone</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" x-model="form.is_milestone" class="sr-only peer">
                                <div class="w-11 h-6 bg-border peer-focus:ring-2 peer-focus:ring-accent/20
                                            rounded-full peer peer-checked:after:translate-x-full
                                            peer-checked:bg-accent after:content-[''] after:absolute
                                            after:top-0.5 after:left-[2px] after:bg-white after:rounded-full
                                            after:h-5 after:w-5 after:transition-all"></div>
                            </label>
                        </div>

                        {{-- Progress --}}
                        <div x-show="!form.is_milestone" x-transition>
                            <label class="block text-xs font-medium text-secondary mb-1.5">
                                Initial Progress: <span x-text="form.progress"></span>%
                            </label>
                            <input type="range" min="0" max="100" x-model="form.progress"
                                   class="w-full h-2 bg-border-light rounded-xl appearance-none cursor-pointer accent-accent">
                        </div>

                        {{-- Submit Button --}}
                        <button type="submit" :disabled="submitting"
                                class="w-full inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl
                                       text-sm font-semibold bg-accent text-white hover:bg-amber-700
                                       disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-sm">
                            <svg x-show="!submitting" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            <svg x-show="submitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="submitting ? 'Adding...' : 'Add Activity'"></span>
                        </button>
                    </form>
                </div>

                {{-- Legend Card --}}
                <div class="bg-card rounded-2xl border border-border overflow-hidden">
                    <div class="px-6 py-4 border-b border-border">
                        <h3 class="text-sm font-semibold text-primary">Legend</h3>
                    </div>
                    <div class="p-6 space-y-3">
                        <div class="flex items-center gap-3">
                            <div class="w-4 h-4 rounded bg-gradient-to-r from-success/80 to-success"></div>
                            <span class="text-xs text-secondary">Completed</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-4 h-4 rounded bg-gradient-to-r from-accent/80 to-accent"></div>
                            <span class="text-xs text-secondary">In Progress</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-4 h-4 rounded bg-gradient-to-r from-info/80 to-info"></div>
                            <span class="text-xs text-secondary">Planned</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-4 h-4 rounded bg-gradient-to-r from-warning/80 to-warning"></div>
                            <span class="text-xs text-secondary">Waiting Review</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-4 h-4 rounded-full bg-gradient-to-r from-danger to-rose-600 ring-4 ring-danger/20"></div>
                            <span class="text-xs text-secondary">Milestone</span>
                        </div>
                    </div>
                </div>

                <!-- Stats Card -->
                <div class="bg-card rounded-2xl border border-border overflow-hidden">
                    <div class="px-6 py-4 border-b border-border">
                        <h3 class="text-sm font-semibold text-primary">Overview Stats</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-secondary">Total Activities</span>
                            <span class="text-sm font-semibold text-primary" x-text="stats.total"></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-secondary">Completed</span>
                            <span class="text-sm font-semibold text-success" x-text="stats.completed"></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-secondary">In Progress</span>
                            <span class="text-sm font-semibold text-accent" x-text="stats.inProgress"></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-secondary">Milestones</span>
                            <span class="text-sm font-semibold text-danger" x-text="stats.milestones"></span>
                        </div>
                        <div class="pt-4 border-t border-border">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs text-secondary">Overall Progress</span>
                                <span class="text-xs font-semibold text-primary" x-text="stats.overallProgress + '%'"></span>
                            </div>
                            <div class="h-2 bg-border-light rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-accent to-success rounded-full transition-all duration-500"
                                     :style="'width: ' + stats.overallProgress + '%'"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Panel: Gantt Chart --}}
            <div class="lg:col-span-3 space-y-6">
                {{-- View Mode Selector --}}
                <div class="bg-card rounded-2xl border border-border p-2 flex items-center gap-2">
                    <template x-for="mode in ['Day', 'Week', 'Month']" :key="mode">
                        <button @click="changeViewMode(mode)"
                                :class="viewMode === mode ? 'bg-accent text-white' : 'text-secondary hover:text-primary hover:bg-surface'"
                                class="flex-1 px-4 py-2 rounded-xl text-sm font-medium transition-all">
                            <span x-text="mode"></span>
                        </button>
                    </template>
                </div>

                <!-- Gantt Chart Container -->
                <div class="bg-card rounded-2xl border border-border overflow-hidden">
                    <div class="px-6 py-4 border-b border-border flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-primary">Timeline</h3>
                        <button @click="refresh()" :disabled="loading"
                                class="text-xs text-accent hover:text-amber-700 disabled:opacity-50 flex items-center gap-1">
                            <svg class="w-4 h-4" :class="{ 'animate-spin': loading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Refresh
                        </button>
                    </div>

                    <div class="p-6" style="min-height: 500px;">
                        <!-- Loading State -->
                        <div x-show="loading" class="flex items-center justify-center h-96">
                            <div class="flex flex-col items-center">
                                <svg class="w-10 h-10 text-accent animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <p class="text-sm text-secondary mt-4">Loading timeline...</p>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div x-show="!loading && tasks.length === 0" class="flex flex-col items-center justify-center h-96">
                            <svg class="w-16 h-16 text-tertiary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                            </svg>
                            <h3 class="text-base font-semibold text-primary mt-4">No Activities Yet</h3>
                            <p class="text-sm text-secondary mt-2 text-center max-w-xs">
                                Add your first activity using the form on the left to get started with your timeline.
                            </p>
                        </div>

                        <!-- Gantt Chart -->
                        <div x-show="!loading && tasks.length > 0" class="gantt-container">
                            <svg id="gantt-chart" class="w-full rounded-xl"></svg>
                        </div>
                    </div>
                </div>

                <!-- Activities List -->
                <div class="bg-card rounded-2xl border border-border overflow-hidden" x-show="tasks.length > 0">
                    <div class="px-6 py-4 border-b border-border">
                        <h3 class="text-sm font-semibold text-primary">Recent Activities</h3>
                    </div>
                    <div class="divide-y divide-border max-h-80 overflow-y-auto">
                        <template x-for="task in tasks.slice(0, 10)" :key="task.id">
                            <div class="flex items-center gap-4 p-4 hover:bg-surface transition-colors cursor-pointer"
                                 @click="editTask(task)">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                                     :class="task.is_milestone ? 'bg-danger/10' : 'bg-accent/10'">
                                    <svg class="w-5 h-5" :class="task.is_milestone ? 'text-danger' : 'text-accent'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path x-show="task.is_milestone" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                        <path x-show="!task.is_milestone" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-primary truncate" x-text="task.name"></p>
                                    <div class="flex items-center gap-3 mt-1">
                                        <span class="text-xs text-secondary" x-text="task.start + ' → ' + task.end"></span>
                                        <span class="text-xs text-tertiary" x-text="task.progress + '% complete'"></span>
                                    </div>
                                </div>
                                <div class="w-16">
                                    <div class="h-1.5 bg-border-light rounded-full overflow-hidden">
                                        <div class="h-full bg-accent rounded-full transition-all duration-300"
                                             :style="'width: ' + task.progress + '%'"></div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
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
                ganttInstance: null,
                viewMode: 'Day',
                tasks: [],
                milestones: [],

                form: {
                    title: '',
                    description: '',
                    start_date: new Date().toISOString().split('T')[0],
                    duration_days: 7,
                    milestone_id: '',
                    is_milestone: false,
                    progress: 0,
                },

                stats: {
                    total: 0,
                    completed: 0,
                    inProgress: 0,
                    milestones: 0,
                    overallProgress: 0,
                },

                async init() {
                    await Promise.all([this.loadTasks(), this.loadMilestones()]);
                    this.loading = false;
                },

                async loadTasks() {
                    try {
                        const response = await fetch(`/api/students/${this.studentId}/tasks/gantt`);
                        this.tasks = await response.json();
                        this.calculateStats();
                        this.$nextTick(() => this.renderGantt());
                    } catch (error) {
                        console.error('Error loading tasks:', error);
                    }
                },

                async loadMilestones() {
                    try {
                        const response = await fetch(`/api/students/${this.studentId}/milestones`);
                        this.milestones = await response.json();
                    } catch (error) {
                        console.error('Error loading milestones:', error);
                    }
                },

                calculateStats() {
                    this.stats.total = this.tasks.length;
                    this.stats.completed = this.tasks.filter(t => t.progress === 100).length;
                    this.stats.inProgress = this.tasks.filter(t => t.progress > 0 && t.progress < 100).length;
                    this.stats.milestones = this.tasks.filter(t => t.is_milestone).length;
                    this.stats.overallProgress = this.tasks.length > 0
                        ? Math.round(this.tasks.reduce((sum, t) => sum + t.progress, 0) / this.tasks.length)
                        : 0;
                },

                renderGantt() {
                    if (this.tasks.length === 0) return;

                    const chartElement = document.getElementById('gantt-chart');
                    if (!chartElement) return;

                    // Clear existing chart
                    chartElement.innerHTML = '';

                    this.ganttInstance = new Gantt(chartElement, this.tasks, {
                        header_height: 50,
                        column_width: 30,
                        step: 1,
                        view_modes: ['Day', 'Week', 'Month'],
                        bar_height: 28,
                        bar_corner_radius: 6,
                        arrow_curve: 5,
                        padding: 18,
                        view_mode: this.viewMode,
                        date_format: 'YYYY-MM-DD',
                        language: 'en',
                        custom_popup_html: (task) => this.createPopupHtml(task),

                        // Enable progress editing
                        draggable_progress: true,
                        draggable_progress_use_ATTRIBUTE: true,

                        // Enable date drag update
                        draggable_update: true,
                        drag_listener: (task, start, end) => this.handleDateChange(task, start, end),

                        // Progress change handler
                        progress_change_listener: (task, progress) => this.handleProgressChange(task, progress),
                    });

                    // Apply milestone styling
                    this.$nextTick(() => this.applyMilestoneStyles());
                },

                createPopupHtml(task) {
                    return `
                        <div class="gantt-popup p-4">
                            <h5 class="font-semibold text-primary mb-2">${task.name}</h5>
                            <div class="space-y-1 text-xs text-secondary">
                                <p>Start: ${task.start}</p>
                                <p>End: ${task.end}</p>
                                <p>Progress: ${task.progress}%</p>
                            </div>
                        </div>
                    `;
                },

                applyMilestoneStyles() {
                    const style = document.createElement('style');
                    style.innerHTML = `
                        .gantt-milestone .gantt-bar-progress {
                            background: linear-gradient(135deg, #DC2626 0%, #E11D48 100%) !important;
                            border-radius: 50% !important;
                            transform: scale(1.2);
                            box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.2);
                        }
                        .gantt-task-completed { opacity: 0.7; }
                        .gantt-task-in_progress .gantt-bar-progress {
                            background: linear-gradient(90deg, #D97706 0%, #F59E0B 100%) !important;
                        }
                        .gantt-task-planned .gantt-bar-progress {
                            background: linear-gradient(90deg, #2563EB 0%, #3B82F6 100%) !important;
                        }
                        .gantt-task-waiting_review .gantt-bar-progress {
                            background: linear-gradient(90deg, #F59E0B 0%, #FBBF24 100%) !important;
                        }
                        .gantt-bar {
                            transition: all 0.3s ease;
                        }
                        .gantt-bar:hover {
                            transform: scaleY(1.1);
                            filter: brightness(1.05);
                        }
                    `;
                    document.head.appendChild(style);
                },

                async handleDateChange(task, start, end) {
                    try {
                        const taskId = task.task_id || task.id;
                        await fetch(`/api/tasks/${taskId}/dates`, {
                            method: 'PUT',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.getCsrfToken() },
                            body: JSON.stringify({ start_date: start, due_date: end })
                        });
                    } catch (error) {
                        console.error('Error updating dates:', error);
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
                        console.error('Error updating progress:', error);
                    }
                },

                async addActivity() {
                    this.submitting = true;
                    try {
                        const response = await fetch(`/api/students/${this.studentId}/tasks/activity`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.getCsrfToken() },
                            body: JSON.stringify(this.form)
                        });

                        if (response.ok) {
                            await this.loadTasks();
                            this.resetForm();
                        }
                    } catch (error) {
                        console.error('Error adding activity:', error);
                    } finally {
                        this.submitting = false;
                    }
                },

                resetForm() {
                    this.form = {
                        title: '',
                        description: '',
                        start_date: new Date().toISOString().split('T')[0],
                        duration_days: 7,
                        milestone_id: '',
                        is_milestone: false,
                        progress: 0,
                    };
                },

                changeViewMode(mode) {
                    this.viewMode = mode;
                    if (this.ganttInstance) {
                        this.ganttInstance.change_view_mode(mode);
                    }
                },

                async refresh() {
                    this.loading = true;
                    await this.loadTasks();
                    this.loading = false;
                },

                editTask(task) {
                    // Navigate to task edit or show modal
                    const taskId = task.task_id || task.id;
                    window.location.href = `/students/${this.studentId}/tasks/${taskId}`;
                },

                calculateDuration() {
                    // Auto-update duration based on start date if needed
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
                        ctx.drawImage(img, 0, 0);
                        URL.revokeObjectURL(url);

                        const link = document.createElement('a');
                        link.download = `timeline-${new Date().toISOString().split('T')[0]}.png`;
                        link.href = canvas.toDataURL('image/png');
                        link.click();
                    };
                    img.src = url;
                },

                exportPdf() {
                    if (!this.ganttInstance) return;

                    const element = document.querySelector('.gantt-container');
                    if (!element) return;

                    const opt = {
                        margin: 10,
                        filename: `timeline-${new Date().toISOString().split('T')[0]}.pdf`,
                        image: { type: 'jpeg', quality: 0.98 },
                        html2canvas: { scale: 2 },
                        jsPDF: { unit: 'mm', format: 'a4', orientation: 'landscape' }
                    };

                    html2pdf().set(opt).from(element).save();
                },

                getCsrfToken() {
                    return document.querySelector('meta[name="csrf-token"]')?.content || '';
                }
            };
        }
    </script>

    @style
    <style>
        .gantt-popup {
            min-width: 200px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            border: 1px solid #E5E5E4;
        }
        .gantt-container {
            background: linear-gradient(135deg, #FAFAF9 0%, #F5F5F4 100%);
            border-radius: 12px;
            padding: 16px;
        }
        .gantt-chart {
            font-family: system-ui, -apple-system, sans-serif;
        }
        .gantt-chart .grid-row {
            fill: none;
            stroke: #E5E5E4;
            stroke-width: 1px;
        }
        .gantt-chart .today-line {
            stroke: #D97706;
            stroke-width: 2px;
            stroke-dasharray: 4;
        }
    </style>
    @endstyle
</x-layouts.app>
