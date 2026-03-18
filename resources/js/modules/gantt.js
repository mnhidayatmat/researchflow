/**
 * Gantt Chart Module - Frappe Gantt Integration
 * Provides interactive Gantt chart visualization for task timelines
 */

import { taskApi } from './api.js';
import { taskStore } from './store.js';

/**
 * Gantt Chart class
 */
class GanttChart {
    constructor(options = {}) {
        this.container = typeof options.container === 'string'
            ? document.querySelector(options.container)
            : options.container;
        this.studentId = options.studentId;
        this.onTaskClick = options.onTaskClick || (() => {});
        this.onDateChange = options.onDateChange || (() => {});
        this.onProgressChange = options.onProgressChange || (() => {});
        this.onError = options.onError || console.error;
        this.gantt = null;
        this.tasks = [];
    }

    /**
     * Initialize the Gantt chart
     */
    async init() {
        try {
            await this.loadGanttLibrary();
            await this.loadTasks();
            this.render();
        } catch (error) {
            this.onError(error);
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
        const data = await taskApi.getGanttData(this.studentId);
        this.tasks = data;
    }

    /**
     * Render the Gantt chart
     */
    render() {
        if (!this.container || this.tasks.length === 0) {
            this.renderEmptyState();
            return;
        }

        // Clear container
        this.container.innerHTML = '';

        // Create Gantt chart
        this.gantt = new Gantt(this.container, this.tasks, {
            view_mode: this.getViewMode(),
            date_format: 'YYYY-MM-DD',
            bar_height: 28,
            bar_corner_radius: 4,
            padding: 18,
            arrow_curve: 8,
            language: 'en',

            // Event handlers
            on_date_change: (task, start, end) => this.handleDateChange(task, start, end),
            on_progress_change: (task, progress) => this.handleProgressChange(task, progress),
            on_click: (task) => this.handleTaskClick(task),
            on_view_change: (mode) => this.handleViewChange(mode),

            // Custom popup
            custom_popup_html: (task) => this.createPopup(task)
        });

        this.applyCustomStyles();
    }

    /**
     * Get appropriate view mode based on screen size
     */
    getViewMode() {
        const width = window.innerWidth;
        if (width < 768) return 'Day';
        if (width < 1024) return 'Week';
        return 'Month';
    }

    /**
     * Handle date change from drag
     */
    async handleDateChange(task, start, end) {
        const startDate = start.toISOString().split('T')[0];
        const dueDate = end.toISOString().split('T')[0];

        try {
            await taskApi.updateDates(task.id, startDate, dueDate);
            this.onDateChange(task.id, startDate, dueDate);

            // Show success indicator
            this.showNotification('Dates updated successfully');
        } catch (error) {
            this.onError(error);
            this.showNotification('Failed to update dates', 'error');
            // Refresh to revert
            this.refresh();
        }
    }

    /**
     * Handle progress change
     */
    async handleProgressChange(task, progress) {
        try {
            await taskApi.updateProgress(task.id, progress);
            this.onProgressChange(task.id, progress);
            this.showNotification(`Progress updated to ${progress}%`);
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
        this.onTaskClick(task);
    }

    /**
     * Handle view mode change
     */
    handleViewChange(mode) {
        console.log('View mode changed to:', mode);
    }

    /**
     * Create custom popup HTML
     */
    createPopup(task) {
        const statusLabels = {
            backlog: 'Backlog',
            planned: 'Planned',
            in_progress: 'In Progress',
            waiting_review: 'Waiting Review',
            revision: 'Revision',
            completed: 'Completed'
        };

        const statusColors = {
            backlog: 'bg-gray-100 text-gray-700',
            planned: 'bg-blue-100 text-blue-700',
            in_progress: 'bg-yellow-100 text-yellow-700',
            waiting_review: 'bg-orange-100 text-orange-700',
            revision: 'bg-purple-100 text-purple-700',
            completed: 'bg-green-100 text-green-700'
        };

        const statusClass = task.custom_class?.replace('gantt-', '') || 'backlog';

        return `
            <div class="gantt-popup bg-white border border-gray-200 rounded-lg shadow-lg p-4 min-w-[200px]">
                <div class="flex items-center justify-between mb-2">
                    <span class="font-semibold text-sm">${task.name}</span>
                    <span class="text-[10px] px-2 py-0.5 rounded-full ${statusColors[statusClass]}">
                        ${statusLabels[statusClass] || statusClass}
                    </span>
                </div>
                <div class="text-xs text-gray-500 space-y-1">
                    <p>Start: ${task.start}</p>
                    <p>End: ${task.end}</p>
                    <p>Progress: ${task.progress}%</p>
                    ${task.dependencies ? `<p class="text-gray-400">Dependencies: ${task.dependencies}</p>` : ''}
                </div>
                <a href="/students/${this.studentId}/tasks/${task.id}"
                   class="mt-3 block text-center text-xs font-medium text-accent hover:underline">
                    View Details
                </a>
            </div>
        `;
    }

    /**
     * Apply custom styles to Gantt chart
     */
    applyCustomStyles() {
        if (!this.container) return;

        // Inject custom styles
        const styleId = 'gantt-custom-styles';
        let styleEl = document.getElementById(styleId);

        if (!styleEl) {
            styleEl = document.createElement('style');
            styleEl.id = styleId;
            document.head.appendChild(styleEl);
        }

        styleEl.textContent = `
            /* Base Gantt styles */
            .gantt .bar {
                fill: #D97706;
                cursor: pointer;
                transition: fill 0.2s, filter 0.2s;
            }
            .gantt .bar:hover {
                filter: brightness(1.1);
            }
            .gantt .bar-progress {
                fill: #B45309;
            }

            /* Status-based colors */
            .gantt-completed .bar { fill: #10B981; }
            .gantt-completed .bar-progress { fill: #059669; }
            .gantt-in_progress .bar { fill: #F59E0B; }
            .gantt-in_progress .bar-progress { fill: #D97706; }
            .gantt-waiting_review .bar { fill: #F97316; }
            .gantt-waiting_review .bar-progress { fill: #EA580C; }
            .gantt-revision .bar { fill: #8B5CF6; }
            .gantt-revision .bar-progress { fill: #7C3AED; }
            .gantt-planned .bar { fill: #3B82F6; }
            .gantt-planned .bar-progress { fill: #2563EB; }
            .gantt-backlog .bar { fill: #9CA3AF; }
            .gantt-backlog .bar-progress { fill: #6B7280; }

            /* Grid styles */
            .gantt .grid-header {
                fill: #F7F7F5;
                stroke: #E5E7EB;
            }
            .gantt .grid-row {
                fill: #ffffff;
            }
            .gantt .grid-row:nth-child(even) {
                fill: #FAFAFA;
            }
            .gantt .tick {
                stroke: #E5E7EB;
            }
            .gantt .today-highlight {
                fill: rgba(217, 119, 6, 0.08);
            }

            /* Text styles */
            .gantt .bar-label {
                fill: #ffffff;
                font-size: 11px;
                font-weight: 500;
            }
            .gantt .grid-text {
                fill: #6B7280;
                font-size: 11px;
            }

            /* Popup styles */
            .gantt-popup {
                font-family: system-ui, -apple-system, sans-serif;
                z-index: 1000;
            }

            /* Dependency arrows */
            .gantt .arrow {
                stroke: #9CA3AF;
                stroke-width: 1.5;
                fill: none;
            }
            .gantt .arrow-head {
                fill: #9CA3AF;
            }
        `;
    }

    /**
     * Render empty state
     */
    renderEmptyState() {
        this.container.innerHTML = `
            <div class="flex flex-col items-center justify-center py-16 text-center">
                <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <h3 class="text-sm font-medium text-gray-600 mb-1">No tasks to display</h3>
                <p class="text-xs text-gray-400 max-w-xs">
                    Create tasks with start and due dates to see them on the timeline.
                </p>
            </div>
        `;
    }

    /**
     * Show notification message
     */
    showNotification(message, type = 'success') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `fixed bottom-4 right-4 px-4 py-2 rounded-lg shadow-lg text-sm font-medium z-50
            ${type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'}`;
        notification.textContent = message;
        document.body.appendChild(notification);

        // Remove after delay
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    /**
     * Refresh the chart with latest data
     */
    async refresh() {
        await this.loadTasks();
        this.render();
    }

    /**
     * Change view mode
     */
    setViewMode(mode) {
        if (this.gantt) {
            this.gantt.change_view_mode(mode);
        }
    }

    /**
     * Destroy the Gantt chart
     */
    destroy() {
        if (this.gantt) {
            this.gantt = null;
        }
        if (this.container) {
            this.container.innerHTML = '';
        }
    }
}

/**
 * Initialize Gantt chart
 * @param {Object} options - Configuration options
 * @returns {GanttChart} Gantt chart instance
 */
export function initGantt(options = {}) {
    const chart = new GanttChart(options);
    chart.init();
    return chart;
}

/**
 * Alpine.js component for Gantt chart
 * Usage: x-data="ganttChart({ studentId: {{ $student->id }} })"
 */
export function ganttChart(options = {}) {
    return {
        loading: true,
        chart: null,
        viewMode: 'Week',

        async init() {
            this.chart = initGantt({
                container: '#gantt-container',
                studentId: options.studentId,
                onTaskClick: (task) => {
                    window.location.href = `/students/${options.studentId}/tasks/${task.id}`;
                },
                onDateChange: (taskId, start, end) => {
                    console.log(`Task ${taskId} dates changed: ${start} - ${end}`);
                },
                onProgressChange: (taskId, progress) => {
                    console.log(`Task ${taskId} progress: ${progress}%`);
                },
                onError: (error) => {
                    console.error('Gantt error:', error);
                }
            });
            this.loading = false;
        },

        setView(mode) {
            this.viewMode = mode;
            this.chart?.setViewMode(mode);
        },

        refresh() {
            this.chart?.refresh();
        },

        destroy() {
            this.chart?.destroy();
        }
    };
}

export default GanttChart;
