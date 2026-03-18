/**
 * Timeline Module - vis-timeline Integration
 * Provides interactive timeline visualization with drag-and-drop support
 */

import { taskApi } from './api.js';
import { taskStore } from './store.js';

/**
 * Timeline class using vis-timeline
 */
class Timeline {
    constructor(options = {}) {
        this.container = typeof options.container === 'string'
            ? document.querySelector(options.container)
            : options.container;
        this.studentId = options.studentId;
        this.onTaskClick = options.onTaskClick || (() => {});
        this.onDateChange = options.onDateChange || (() => {});
        this.onError = options.onError || console.error;
        this.timeline = null;
        this.tasks = [];
        this.groups = [];
    }

    /**
     * Initialize the timeline
     */
    async init() {
        try {
            await this.loadTimelineLibrary();
            await this.loadTasks();
            this.render();
            this.setupEventListeners();
        } catch (error) {
            this.onError(error);
        }
    }

    /**
     * Load vis-timeline library dynamically
     */
    loadTimelineLibrary() {
        return new Promise((resolve, reject) => {
            if (typeof vis !== 'undefined') {
                resolve();
                return;
            }

            // Load CSS
            const css = document.createElement('link');
            css.rel = 'stylesheet';
            css.href = 'https://cdn.jsdelivr.net/npm/vis-timeline@7.7.3/styles/vis-timeline-graph2d.min.css';
            document.head.appendChild(css);

            // Load JS
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/vis-timeline@7.7.3/vis-timeline-graph2d.min.js';
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
        this.tasks = this.transformTasks(data);
        this.groups = this.createGroups(data);
    }

    /**
     * Transform tasks for vis-timeline format
     */
    transformTasks(tasks) {
        return tasks.map(task => ({
            id: task.id,
            content: this.createTaskContent(task),
            start: task.start,
            end: task.end,
            group: this.getGroupId(task),
            className: `timeline-task timeline-task-${task.custom_class?.replace('gantt-', '') || 'default'}`,
            // Custom data
            data: task
        }));
    }

    /**
     * Create task content HTML
     */
    createTaskContent(task) {
        const dependencyBadge = task.dependencies
            ? `<span class="ml-1 text-[10px] opacity-60" title="Dependencies: ${task.dependencies}">🔗</span>`
            : '';

        return `
            <div class="flex items-center gap-1 text-xs">
                <span class="truncate max-w-[120px]">${task.name}</span>
                ${dependencyBadge}
            </div>
        `;
    }

    /**
     * Get group ID for a task (based on milestone or status)
     */
    getGroupId(task) {
        // You can group by milestone, status, priority, etc.
        return task.data?.status || 'default';
    }

    /**
     * Create groups for timeline
     */
    createGroups(tasks) {
        const statusGroups = {
            backlog: { id: 'backlog', content: 'Backlog', order: 1 },
            planned: { id: 'planned', content: 'Planned', order: 2 },
            in_progress: { id: 'in_progress', content: 'In Progress', order: 3 },
            waiting_review: { id: 'waiting_review', content: 'Waiting Review', order: 4 },
            revision: { id: 'revision', content: 'Revision', order: 5 },
            completed: { id: 'completed', content: 'Completed', order: 6 }
        };

        return Object.values(statusGroups);
    }

    /**
     * Render the timeline
     */
    render() {
        if (!this.container) {
            console.error('Timeline container not found');
            return;
        }

        const options = {
            height: '400px',
            margin: {
                item: 10,
                axis: 5
            },
            orientation: 'top',
            moveable: true,
            zoomable: true,
            stack: true,
            showCurrentTime: true,
            editable: {
                updateTime: true,
                updateGroup: false,
                add: false,
                remove: false
            },
            groupOrder: 'order',
            format: {
                minorLabels: {
                    day: 'D MMM',
                    weekday: 'ddd D',
                    hour: 'h:mm a'
                },
                majorLabels: {
                    day: 'MMMM YYYY',
                    week: 'MMMM YYYY',
                    month: 'YYYY',
                    year: 'YYYY'
                }
            },
            locale: 'en',
            tooltip: {
                followMouse: true
            },
            template: (item, element) => {
                // Custom template for items
                return item.data?.name || '';
            }
        };

        this.timeline = new vis.Timeline(this.container, this.tasks, this.groups, options);
        this.applyCustomStyles();
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        if (!this.timeline) return;

        // Task click
        this.timeline.on('click', (properties) => {
            if (properties.item) {
                const task = this.tasks.find(t => t.id === properties.item);
                if (task) {
                    this.onTaskClick(task);
                }
            }
        });

        // Double click to edit/view
        this.timeline.on('doubleClick', (properties) => {
            if (properties.item) {
                const task = this.tasks.find(t => t.id === properties.item);
                if (task) {
                    // Navigate to task detail
                    window.location.href = `/students/${this.studentId}/tasks/${task.id}`;
                }
            }
        });

        // Date/time change
        this.timeline.on('onMoving', (properties) => {
            this.handleDateChange(properties);
        });
    }

    /**
     * Handle date change from drag
     */
    async handleDateChange(properties) {
        const { item, start, end } = properties;

        if (!item) return;

        const startDate = start.toISOString().split('T')[0];
        const endDate = end.toISOString().split('T')[0];

        try {
            await taskApi.updateDates(item, startDate, endDate);
            this.onDateChange(item, startDate, endDate);
        } catch (error) {
            this.onError(error);
            // Refresh to revert
            this.refresh();
        }
    }

    /**
     * Apply custom styles to timeline
     */
    applyCustomStyles() {
        const styleId = 'timeline-custom-styles';
        let styleEl = document.getElementById(styleId);

        if (!styleEl) {
            styleEl = document.createElement('style');
            styleEl.id = styleId;
            document.head.appendChild(styleEl);
        }

        styleEl.textContent = `
            /* Base timeline styles */
            .vis-timeline {
                border: 1px solid #E5E7EB;
                border-radius: 8px;
                font-family: system-ui, -apple-system, sans-serif;
            }

            .vis-panel.vis-background {
                background: #F7F7F5;
            }

            /* Time axis */
            .vis-time-axis .vis-text {
                color: #6B7280;
                font-size: 11px;
            }

            /* Grid lines */
            .vis-time-axis .vis-grid.vis-saturday,
            .vis-time-axis .vis-grid.vis-sunday {
                background: rgba(217, 119, 6, 0.03);
            }

            /* Current time indicator */
            .vis-current-time {
                background-color: #D97706;
            }

            /* Task items */
            .vis-item {
                border-radius: 6px;
                border: none;
                font-size: 12px;
                color: #1F2937;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                transition: box-shadow 0.2s, transform 0.2s;
            }

            .vis-item:hover {
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.15);
                transform: translateY(-1px);
                z-index: 999;
            }

            /* Status-based colors */
            .timeline-task-backlog {
                background-color: #9CA3AF;
                color: white;
            }
            .timeline-task-backlog.vis-selected {
                background-color: #6B7280;
            }

            .timeline-task-planned {
                background-color: #3B82F6;
                color: white;
            }
            .timeline-task-planned.vis-selected {
                background-color: #2563EB;
            }

            .timeline-task-in_progress {
                background-color: #F59E0B;
                color: white;
            }
            .timeline-task-in_progress.vis-selected {
                background-color: #D97706;
            }

            .timeline-task-waiting_review {
                background-color: #F97316;
                color: white;
            }
            .timeline-task-waiting_review.vis-selected {
                background-color: #EA580C;
            }

            .timeline-task-revision {
                background-color: #8B5CF6;
                color: white;
            }
            .timeline-task-revision.vis-selected {
                background-color: #7C3AED;
            }

            .timeline-task-completed {
                background-color: #10B981;
                color: white;
            }
            .timeline-task-completed.vis-selected {
                background-color: #059669;
            }

            /* Selected item */
            .vis-item.vis-selected {
                box-shadow: 0 0 0 2px #D97706, 0 4px 6px rgba(0, 0, 0, 0.15);
            }

            /* Groups */
            .vis-group {
                border-bottom: 1px solid #E5E7EB;
            }

            .vis-label.vis-nesting-group {
                background: #F7F7F5;
            }

            .vis-label {
                color: #374151;
                font-size: 12px;
                font-weight: 500;
                padding: 4px 8px;
            }

            /* Custom tooltip */
            .vis-tooltip {
                background: white;
                border: 1px solid #E5E7EB;
                border-radius: 8px;
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
                padding: 12px;
                font-size: 12px;
                max-width: 250px;
            }

            /* Range inputs (custom time) */
            .vis-custom-time {
                background-color: #D97706;
                width: 2px;
            }

            /* Navigation buttons */
            .vis-navigation .vis-button {
                background: white;
                border: 1px solid #E5E7EB;
                border-radius: 6px;
                color: #6B7280;
                padding: 4px 8px;
                font-size: 12px;
                transition: all 0.2s;
            }

            .vis-navigation .vis-button:hover {
                background: #F7F7F5;
                border-color: #D97706;
                color: #D97706;
            }
        `;
    }

    /**
     * Fit timeline to show all tasks
     */
    fit() {
        if (this.timeline) {
            this.timeline.fit();
        }
    }

    /**
     * Set custom time range
     */
    setWindow(start, end) {
        if (this.timeline) {
            this.timeline.setWindow(start, end);
        }
    }

    /**
     * Move timeline
     */
    move(amount) {
        if (this.timeline) {
            const range = this.timeline.getWindow();
            const interval = range.end - range.start;
            this.timeline.setWindow(
                range.start.valueOf() + amount * interval,
                range.end.valueOf() + amount * interval
            );
        }
    }

    /**
     * Zoom timeline
     */
    zoom(factor) {
        if (this.timeline) {
            const range = this.timeline.getWindow();
            const interval = range.end - range.start;
            const newInterval = interval * factor;
            const center = (range.start.valueOf() + range.end.valueOf()) / 2;
            this.timeline.setWindow(
                center - newInterval / 2,
                center + newInterval / 2
            );
        }
    }

    /**
     * Refresh timeline with latest data
     */
    async refresh() {
        await this.loadTasks();
        if (this.timeline) {
            this.timeline.setItems(this.tasks);
            this.timeline.setGroups(this.groups);
        }
    }

    /**
     * Destroy the timeline
     */
    destroy() {
        if (this.timeline) {
            this.timeline.destroy();
            this.timeline = null;
        }
    }
}

/**
 * Initialize Timeline
 * @param {Object} options - Configuration options
 * @returns {Timeline} Timeline instance
 */
export function initTimeline(options = {}) {
    const timeline = new Timeline(options);
    timeline.init();
    return timeline;
}

/**
 * Alpine.js component for Timeline
 * Usage: x-data="timeline({ studentId: {{ $student->id }} })"
 */
export function timeline(options = {}) {
    return {
        loading: true,
        timeline: null,

        async init() {
            this.timeline = initTimeline({
                container: '#timeline-container',
                studentId: options.studentId,
                onTaskClick: (task) => {
                    console.log('Task clicked:', task);
                },
                onDateChange: (taskId, start, end) => {
                    console.log(`Task ${taskId} dates changed: ${start} - ${end}`);
                },
                onError: (error) => {
                    console.error('Timeline error:', error);
                }
            });
            this.loading = false;

            // Auto fit after render
            setTimeout(() => this.timeline?.fit(), 100);
        },

        fit() {
            this.timeline?.fit();
        },

        move(amount) {
            this.timeline?.move(amount);
        },

        zoom(factor) {
            this.timeline?.zoom(factor);
        },

        refresh() {
            this.timeline?.refresh();
        },

        destroy() {
            this.timeline?.destroy();
        }
    };
}

export default Timeline;
