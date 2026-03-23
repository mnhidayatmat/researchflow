/**
 * Gantt Chart Module - Split Layout with Frappe Gantt
 * Provides MS Project / ClickUp / Notion Timeline style interface
 */

import { taskApi } from './api.js';

/**
 * Status color mapping for UI
 */
const STATUS_COLORS = {
    'planned': { bg: '#3B82F6', label: 'Planned' },
    'in_progress': { bg: '#F59E0B', label: 'In Progress' },
    'waiting_review': { bg: '#F97316', label: 'Review' },
    'revision': { bg: '#8B5CF6', label: 'Revision' },
    'completed': { bg: '#10B981', label: 'Done' }
};

/**
 * Gantt Chart class with split layout support
 */
class GanttChart {
    constructor(options = {}) {
        this.studentId = options.studentId;
        this.onTaskClick = options.onTaskClick || (() => {});
        this.onDateChange = options.onDateChange || (() => {});
        this.onProgressChange = options.onProgressChange || (() => {});
        this.onTasksLoaded = options.onTasksLoaded || (() => {});
        this.onError = options.onError || console.error;
        this.gantt = null;
        this.tasks = [];
        this.rowHeight = 56;
    }

    /**
     * Initialize the Gantt chart
     */
    async init() {
        try {
            console.log('Gantt: Starting initialization');
            await this.loadGanttLibrary();
            await this.loadTasks();
            // Don't call render() here - let Alpine trigger it after DOM is ready
            // this.render() is now called explicitly from Alpine
            this.initScrollSync();
            console.log('Gantt: Initialization complete');
            return this;
        } catch (error) {
            console.error('Gantt: Initialization failed', error);
            this.onError(error);
            return this;
        }
    }

    /**
     * Load Frappe Gantt library dynamically
     */
    loadGanttLibrary() {
        return new Promise((resolve, reject) => {
            if (typeof Gantt !== 'undefined') {
                resolve();
                return;
            }

            // Load CSS
            const css = document.createElement('link');
            css.rel = 'stylesheet';
            css.href = 'https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.1/dist/frappe-gantt.min.css';
            document.head.appendChild(css);

            // Load JS
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.1/dist/frappe-gantt.min.js';
            script.onload = resolve;
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }

    /**
     * Load tasks from API
     */
    async loadTasks() {
        try {
            const data = await taskApi.getGanttData(this.studentId);
            console.log('Gantt: Loaded tasks from API:', data.length, 'tasks');
            console.log('Gantt: Sample task:', data[0]);
            this.tasks = data.sort((a, b) => {
                if (a.start !== b.start) return new Date(a.start) - new Date(b.start);
                return a.name.localeCompare(b.name);
            });
            this.onTasksLoaded(this.tasks);
        } catch (error) {
            console.error('Gantt: Failed to load tasks', error);
            throw error;
        }
    }

    /**
     * Render the Gantt chart with split layout
     * Uses retry logic to wait for Alpine to reveal the DOM
     */
    render(attempt = 0) {
        console.log('Gantt: Starting render with', this.tasks.length, 'tasks', 'attempt:', attempt + 1);

        // Find containers
        const container = document.querySelector('#gantt-container');
        const taskListContainer = document.querySelector('#gantt-task-list');
        const headerContainer = document.querySelector('#gantt-header-container');

        console.log('Gantt: Containers found:', {
            container: !!container,
            taskListContainer: !!taskListContainer,
            headerContainer: !!headerContainer
        });

        // If containers not found, retry after a short delay (Alpine needs time to update DOM)
        if (!container || !taskListContainer) {
            if (attempt < 10) {
                console.log('Gantt: Containers not ready, retrying...');
                setTimeout(() => this.render(attempt + 1), 50);
                return;
            }
            console.error('Gantt: Containers not found after 10 attempts!');
            if (!container) console.error('Gantt: #gantt-container not found!');
            if (!taskListContainer) console.error('Gantt: #gantt-task-list not found!');
            return;
        }

        // Clear containers
        container.innerHTML = '';
        taskListContainer.innerHTML = '';
        if (headerContainer) headerContainer.innerHTML = '';

        if (this.tasks.length === 0) {
            this.renderEmptyState(container);
            return;
        }

        try {
            // Render task list (left panel) first
            this.renderTaskList(taskListContainer);

            // Create Gantt chart (right panel)
            this.gantt = new Gantt(container, this.tasks, {
                view_mode: 'Month',
                date_format: 'YYYY-MM-DD',
                header_height: 48,
                bar_height: 28,
                bar_corner_radius: 4,
                padding: 14,
                arrow_curve: 8,
                language: 'en',
                show_dates: true,

                on_date_change: (task, start, end) => this.handleDateChange(task, start, end),
                on_progress_change: (task, progress) => this.handleProgressChange(task, progress),
                on_click: (task) => this.handleTaskClick(task),
                on_view_change: (mode) => this.handleViewChange(mode),
                custom_popup_html: (task) => this.createPopup(task)
            });

            console.log('Gantt: Chart created successfully');
            this.applyCustomStyles();

            setTimeout(() => this.cloneHeader(headerContainer, container), 50);
        } catch (error) {
            console.error('Gantt: Failed to create chart', error);
            this.onError(error);
        }
    }

    /**
     * Render task list in left panel
     */
    renderTaskList(container) {
        console.log('Gantt: renderTaskList called, container:', container, 'tasks:', this.tasks.length);

        if (!container) {
            console.error('Gantt: renderTaskList - container is null/undefined');
            return;
        }

        const fragment = document.createDocumentFragment();

        this.tasks.forEach((task, index) => {
            const statusInfo = STATUS_COLORS[task.status] || STATUS_COLORS['planned'];
            const row = document.createElement('div');
            row.className = 'gantt-task-row';
            row.dataset.taskId = task.id;
            row.dataset.taskIndex = index;
            row.style.height = `${this.rowHeight}px`;

            // Build HTML
            row.innerHTML = `
                <div class="flex items-center gap-3 flex-1 min-w-0">
                    <span style="width: 8px; height: 8px; border-radius: 50%; background-color: ${statusInfo.bg}; flex-shrink: 0;" title="${statusInfo.label}"></span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-primary truncate" style="display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${this.escapeHtml(task.name)}</p>
                        <p class="text-xs text-secondary">${this.formatDateRange(task.start, task.end)}</p>
                    </div>
                    <span class="text-xs text-tertiary">${task.progress || 0}%</span>
                </div>
            `;

            // Click handler
            row.addEventListener('click', () => {
                const taskId = this.extractTaskId(task.id);
                window.location.href = `/students/${this.studentId}/tasks/${taskId}`;
            });

            // Hover handlers
            row.addEventListener('mouseenter', () => this.highlightGanttBar(index, true));
            row.addEventListener('mouseleave', () => this.highlightGanttBar(index, false));

            fragment.appendChild(row);
        });

        container.innerHTML = '';
        container.appendChild(fragment);
        console.log('Gantt: Task list rendered, child count:', container.children.length);
    }

    /**
     * Clone Gantt header
     */
    cloneHeader(headerContainer, ganttContainer) {
        if (!headerContainer || !ganttContainer) return;

        const ganttSvg = ganttContainer.querySelector('svg');
        if (!ganttSvg) return;

        const headerClone = ganttSvg.cloneNode(true);
        const headerHeight = 48;
        headerClone.setAttribute('viewBox', `0 0 ${ganttSvg.getAttribute('width')} ${headerHeight}`);
        headerClone.style.height = `${headerHeight}px`;

        headerContainer.innerHTML = '';
        headerContainer.appendChild(headerClone);
    }

    /**
     * Highlight Gantt bar
     */
    highlightGanttBar(taskIndex, highlight) {
        const container = document.querySelector('#gantt-container');
        if (!container) return;

        const barWrappers = container.querySelectorAll('.bar-wrapper');
        if (barWrappers[taskIndex]) {
            const bar = barWrappers[taskIndex].querySelector('.bar');
            if (bar) {
                if (highlight) {
                    bar.style.filter = 'brightness(1.15)';
                    bar.style.stroke = 'rgba(0,0,0,0.3)';
                    bar.style.strokeWidth = '2px';
                } else {
                    bar.style.filter = '';
                    bar.style.stroke = '';
                    bar.style.strokeWidth = '';
                }
            }
        }

        const taskRow = document.querySelector(`[data-task-index="${taskIndex}"]`);
        if (taskRow) {
            if (highlight) {
                taskRow.classList.add('active');
            } else {
                taskRow.classList.remove('active');
            }
        }
    }

    /**
     * Initialize scroll sync
     */
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
    }

    /**
     * Handle date change
     */
    async handleDateChange(task, start, end) {
        const startDate = start.toISOString().split('T')[0];
        const dueDate = end.toISOString().split('T')[0];
        const taskId = this.extractTaskId(task.id);

        try {
            await taskApi.updateDates(taskId, startDate, dueDate);
            this.onDateChange(taskId, startDate, dueDate);
            this.showNotification('Dates updated successfully');
            await this.loadTasks();
            this.render();
        } catch (error) {
            this.onError(error);
            this.showNotification('Failed to update dates', 'error');
            this.refresh();
        }
    }

    /**
     * Handle progress change
     */
    async handleProgressChange(task, progress) {
        const taskId = this.extractTaskId(task.id);

        try {
            await taskApi.updateProgress(taskId, progress);
            this.onProgressChange(taskId, progress);
            this.showNotification(`Progress updated to ${progress}%`);

            const taskRow = document.querySelector(`[data-task-id="${task.id}"]`);
            if (taskRow) {
                const progressEl = taskRow.querySelector('.text-xs.text-tertiary');
                if (progressEl) progressEl.textContent = `${progress}%`;
            }
        } catch (error) {
            this.onError(error);
            this.showNotification('Failed to update progress', 'error');
            this.refresh();
        }
    }

    /**
     * Handle task click
     */
    handleTaskClick(task) {
        const taskId = this.extractTaskId(task.id);
        this.onTaskClick({ ...task, taskId });
    }

    /**
     * Handle view change
     */
    handleViewChange(mode) {
        console.log('View mode changed to:', mode);
        setTimeout(() => {
            const headerContainer = document.querySelector('#gantt-header-container');
            const ganttContainer = document.querySelector('#gantt-container');
            this.cloneHeader(headerContainer, ganttContainer);
        }, 100);
    }

    /**
     * Extract task ID
     */
    extractTaskId(ganttTaskId) {
        if (typeof ganttTaskId === 'string' && ganttTaskId.startsWith('task-')) {
            return parseInt(ganttTaskId.replace('task-', ''), 10);
        }
        return parseInt(ganttTaskId, 10);
    }

    /**
     * Format date range
     */
    formatDateRange(start, end) {
        const startDate = new Date(start);
        const endDate = new Date(end);
        const options = { month: 'short', day: 'numeric' };

        if (startDate.getMonth() === endDate.getMonth() && startDate.getFullYear() === endDate.getFullYear()) {
            return `${startDate.toLocaleDateString('en-US', options)} - ${endDate.getDate()}`;
        }
        return `${startDate.toLocaleDateString('en-US', options)} - ${endDate.toLocaleDateString('en-US', options)}`;
    }

    /**
     * Escape HTML
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Create popup
     */
    createPopup(task) {
        const statusInfo = STATUS_COLORS[task.status] || STATUS_COLORS['planned'];
        const taskId = this.extractTaskId(task.id);

        return `
            <div class="gantt-popup bg-white border border-gray-200 rounded-lg shadow-lg p-4 min-w-[220px]">
                <div class="flex items-center justify-between mb-3">
                    <span class="font-semibold text-sm">${this.escapeHtml(task.name)}</span>
                    <span class="text-[10px] px-2 py-0.5 rounded-full" style="background-color: ${statusInfo.bg}20; color: ${statusInfo.bg}">
                        ${statusInfo.label}
                    </span>
                </div>
                <div class="text-xs text-gray-500 space-y-1.5 mb-3">
                    <div class="flex items-center gap-2">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>${task.start} → ${task.end}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <span>Progress: ${task.progress}%</span>
                    </div>
                </div>
                <a href="/students/${this.studentId}/tasks/${taskId}"
                   class="block w-full text-center text-xs font-medium text-accent hover:text-amber-700 py-2 rounded-lg bg-accent/10 hover:bg-accent/20 transition-colors">
                    View Details →
                </a>
            </div>
        `;
    }

    /**
     * Apply custom styles
     */
    applyCustomStyles() {
        const styleId = 'gantt-custom-styles';
        let styleEl = document.getElementById(styleId);

        if (!styleEl) {
            styleEl = document.createElement('style');
            styleEl.id = styleId;
            document.head.appendChild(styleEl);
        }

        styleEl.textContent = `
            .gantt .bar { fill: #D97706; cursor: pointer; transition: fill 0.2s, filter 0.2s; }
            .gantt .bar:hover { filter: brightness(1.1); }
            .gantt .bar-progress { fill: #B45309; }
            .gantt-completed .bar { fill: #10B981; }
            .gantt-completed .bar-progress { fill: #059669; }
            .gantt-in-progress .bar { fill: #F59E0B; }
            .gantt-in-progress .bar-progress { fill: #D97706; }
            .gantt-waiting-review .bar { fill: #F97316; }
            .gantt-waiting-review .bar-progress { fill: #EA580C; }
            .gantt-revision .bar { fill: #8B5CF6; }
            .gantt-revision .bar-progress { fill: #7C3AED; }
            .gantt-planned .bar { fill: #3B82F6; }
            .gantt-planned .bar-progress { fill: #2563EB; }
            .gantt .grid-header { fill: #FAFAF9; stroke: #E5E5E4; }
            .gantt .grid-row { fill: transparent; }
            .gantt .tick { stroke: #E5E5E4; }
            .gantt .today-highlight { fill: rgba(217, 119, 6, 0.08); }
            .gantt .bar-label { display: none; }
            .gantt .grid-text { fill: #78716C; font-size: 11px; font-weight: 500; }
            .gantt-popup { font-family: system-ui, -apple-system, sans-serif; z-index: 1000; }
            .gantt .arrow { stroke: #A8A29E; stroke-width: 1.5; fill: none; }
            .gantt .arrow-head { fill: #A8A29E; }
        `;
    }

    /**
     * Render empty state
     */
    renderEmptyState(container) {
        container.innerHTML = `
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <svg class="w-16 h-16 text-tertiary mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                </svg>
                <h3 class="text-sm font-medium text-primary mb-1">No tasks to display</h3>
                <p class="text-xs text-secondary max-w-xs">
                    Create tasks with start and due dates to see them on the timeline.
                </p>
            </div>
        `;
    }

    /**
     * Show notification
     */
    showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `fixed bottom-4 right-4 px-4 py-3 rounded-xl shadow-lg text-sm font-medium z-50 flex items-center gap-2
            ${type === 'success' ? 'bg-success text-white' : 'bg-danger text-white'}`;
        notification.innerHTML = `
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                ${type === 'success'
                    ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>'
                    : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>'
                }
            </svg>
            ${message}
        `;
        document.body.appendChild(notification);

        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateY(10px)';
            notification.style.transition = 'all 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    /**
     * Refresh
     */
    async refresh() {
        await this.loadTasks();
        this.render();
    }

    /**
     * Set view mode
     */
    setViewMode(mode) {
        if (this.gantt) {
            this.gantt.change_view_mode(mode);
            setTimeout(() => {
                const headerContainer = document.querySelector('#gantt-header-container');
                const ganttContainer = document.querySelector('#gantt-container');
                this.cloneHeader(headerContainer, ganttContainer);
            }, 100);
        }
    }

    /**
     * Destroy
     */
    destroy() {
        if (this.gantt) {
            this.gantt = null;
        }
    }
}

/**
 * Initialize Gantt chart
 */
export function initGantt(options = {}) {
    const chart = new GanttChart(options);
    // Return the promise
    return chart.init().then(() => chart);
}

/**
 * Alpine.js component
 */
export function ganttChart(options = {}) {
    return {
        loading: true,
        chart: null,
        tasks: [],
        viewMode: 'Month',
        taskStats: {
            total: 0,
            completed: 0,
            inProgress: 0,
            overdue: 0
        },

        init() {
            console.log('Alpine: Gantt init starting');

            initGantt({
                studentId: options.studentId,
                onTaskClick: (task) => {
                    const taskId = typeof task === 'object' && task.taskId ? task.taskId : task;
                    if (taskId) {
                        window.location.href = `/students/${options.studentId}/tasks/${taskId}`;
                    }
                },
                onTasksLoaded: (tasks) => {
                    console.log('Alpine: Tasks loaded:', tasks.length);
                    this.tasks = tasks;
                    this.taskStats = {
                        total: tasks.length,
                        completed: tasks.filter(t => t.progress === 100).length,
                        inProgress: tasks.filter(t => t.progress > 0 && t.progress < 100).length,
                        overdue: tasks.filter(t => new Date(t.end) < new Date() && t.progress < 100).length
                    };
                },
                onError: (error) => {
                    console.error('Alpine: Gantt error', error);
                    this.loading = false;
                }
            }).then((chart) => {
                this.chart = chart;
                console.log('Alpine: Chart initialized');

                // Set loading to false to reveal the DOM
                this.loading = false;

                // Wait for DOM to be ready, then render
                setTimeout(() => {
                    console.log('Alpine: Triggering render after timeout');
                    if (this.chart) {
                        this.chart.render();
                    }
                }, 100);
            }).catch((error) => {
                console.error('Alpine: Init failed', error);
                this.loading = false;
            });
        },

        setView(mode) {
            this.viewMode = mode;
            this.chart?.setViewMode(mode);
        },

        refresh() {
            this.chart?.refresh();
        },

        navigate(direction) {
            if (!this.chart?.gantt) return;

            const gantt = this.chart.gantt;
            const shifts = { 'Day': 1, 'Week': 7, 'Month': 30 };
            const days = shifts[this.viewMode] || 7;

            if (direction === 'prev') {
                gantt.gantt_start.setDate(gantt.gantt_start.getDate() - days);
                gantt.gantt_end.setDate(gantt.gantt_end.getDate() - days);
            } else if (direction === 'next') {
                gantt.gantt_start.setDate(gantt.gantt_start.getDate() + days);
                gantt.gantt_end.setDate(gantt.gantt_end.getDate() + days);
            } else if (direction === 'today') {
                const today = new Date();
                gantt.gantt_start = new Date(today);
                gantt.gantt_start.setDate(today.getDate() - days);
                gantt.gantt_end = new Date(today);
                gantt.gantt_end.setDate(today.getDate() + days * 2);
            }

            this.chart.refresh();
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

        showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `fixed bottom-4 right-4 px-4 py-3 rounded-xl shadow-lg text-sm font-medium z-50 flex items-center gap-2
                ${type === 'success' ? 'bg-success text-white' : 'bg-danger text-white'}`;
            notification.innerHTML = `
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    ${type === 'success'
                        ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>'
                        : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>'
                    }
                </svg>
                ${message}
            `;
            document.body.appendChild(notification);

            setTimeout(() => {
                notification.style.opacity = '0';
                notification.style.transform = 'translateY(10px)';
                notification.style.transition = 'all 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        },

        destroy() {
            this.chart?.destroy();
        }
    };
}

export default GanttChart;

// Alias for compatibility
export { ganttChart as initTaskFlowGantt };
