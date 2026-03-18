/**
 * Kanban Board Module - SortableJS Integration
 * Provides drag-and-drop Kanban functionality for task management
 */

import { taskApi } from './api.js';
import { taskStore } from './store.js';

/**
 * Kanban column configuration
 */
export const KANBAN_COLUMNS = {
    backlog: { label: 'Backlog', color: 'bg-gray-400' },
    planned: { label: 'Planned', color: 'bg-blue-400' },
    in_progress: { label: 'In Progress', color: 'bg-yellow-400' },
    waiting_review: { label: 'Waiting Review', color: 'bg-orange-400' },
    revision: { label: 'Revision', color: 'bg-purple-400' },
    completed: { label: 'Completed', color: 'bg-green-400' }
};

/**
 * Kanban Board class
 */
class KanbanBoard {
    constructor(options = {}) {
        this.container = options.container || document;
        this.studentId = options.studentId;
        this.onTaskClick = options.onTaskClick || (() => {});
        this.onStatusChange = options.onStatusChange || (() => {});
        this.onError = options.onError || console.error;
        this.sortables = new Map();
        this.isUpdating = false;
    }

    /**
     * Initialize the Kanban board
     */
    init() {
        this.columns = this.container.querySelectorAll('[data-kanban-column]');
        this.setupSortable();
        this.bindEvents();
    }

    /**
     * Setup SortableJS for each column
     */
    setupSortable() {
        // Load SortableJS from CDN if not available
        if (typeof Sortable === 'undefined') {
            this.loadSortableLibrary().then(() => {
                this.initializeSortables();
            });
        } else {
            this.initializeSortables();
        }
    }

    /**
     * Load SortableJS library dynamically
     */
    loadSortableLibrary() {
        return new Promise((resolve, reject) => {
            if (typeof Sortable !== 'undefined') {
                resolve();
                return;
            }

            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js';
            script.onload = resolve;
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }

    /**
     * Initialize Sortable instances for each column
     */
    initializeSortables() {
        this.columns.forEach(column => {
            const sortable = new Sortable(column, {
                group: 'kanban',
                animation: 150,
                ghostClass: 'kanban-ghost',
                dragClass: 'kanban-dragging',
                delay: 0,
                delayOnTouchOnly: true,
                touchStartThreshold: 5,
                onEnd: (evt) => this.handleDrop(evt)
            });
            this.sortables.set(column.dataset.kanbanColumn, sortable);
        });
    }

    /**
     * Handle task drop event
     */
    async handleDrop(evt) {
        if (this.isUpdating) return;

        const taskId = parseInt(evt.item.dataset.taskId);
        const newStatus = evt.to.dataset.kanbanColumn;
        const oldStatus = evt.from.dataset.kanbanColumn;

        // No change, skip
        if (newStatus === oldStatus && evt.newIndex === evt.oldIndex) return;

        this.isUpdating = true;
        this.showLoadingState(evt.item);

        try {
            // Build tasks array with new order
            const tasks = this.buildTasksArray();

            // Update order on server
            await taskApi.updateOrder(tasks);

            // Update status if changed
            if (newStatus !== oldStatus) {
                await taskApi.updateStatus(taskId, newStatus);
                this.onStatusChange(taskId, newStatus);
            }

            // Update local store
            taskStore.reorderTasks(tasks);

            // Update UI counts
            this.updateColumnCounts();

            this.hideLoadingState(evt.item);
        } catch (error) {
            this.onError(error);
            // Revert on error
            evt.from.insertBefore(evt.item, evt.from.children[evt.oldIndex]);
            this.hideLoadingState(evt.item);
        } finally {
            this.isUpdating = false;
        }
    }

    /**
     * Build tasks array from current DOM state
     */
    buildTasksArray() {
        const tasks = [];
        let sortOrder = 0;

        this.columns.forEach(column => {
            const status = column.dataset.kanbanColumn;
            const cards = column.querySelectorAll('[data-task-id]');

            cards.forEach(card => {
                tasks.push({
                    id: parseInt(card.dataset.taskId),
                    sort_order: sortOrder++,
                    status: status
                });
            });
        });

        return tasks;
    }

    /**
     * Bind additional events
     */
    bindEvents() {
        // Task click events
        this.container.addEventListener('click', (e) => {
            const card = e.target.closest('[data-task-id]');
            if (card && !e.target.closest('a, button')) {
                const taskId = parseInt(card.dataset.taskId);
                this.onTaskClick(taskId, card);
            }
        });
    }

    /**
     * Update column task counts
     */
    updateColumnCounts() {
        this.columns.forEach(column => {
            const countEl = column.querySelector('[data-column-count]');
            if (countEl) {
                const count = column.querySelectorAll('[data-task-id]').length;
                countEl.textContent = count;
            }
        });
    }

    /**
     * Show loading state on a card
     */
    showLoadingState(card) {
        card.classList.add('kanban-loading');
        card.style.opacity = '0.6';
    }

    /**
     * Hide loading state on a card
     */
    hideLoadingState(card) {
        card.classList.remove('kanban-loading');
        card.style.opacity = '';
    }

    /**
     * Add a new task card to a column
     */
    addTaskCard(task, status) {
        const column = this.container.querySelector(`[data-kanban-column="${status}"]`);
        if (!column) return;

        const card = this.createTaskCard(task);
        column.appendChild(card);
        this.updateColumnCounts();
    }

    /**
     * Create a task card element
     */
    createTaskCard(task) {
        const card = document.createElement('div');
        card.dataset.taskId = task.id;
        card.className = 'kanban-card bg-white border border-border rounded-lg p-3 cursor-move hover:shadow-sm transition-shadow';

        const priorityColors = {
            urgent: 'bg-red-100 text-red-700',
            high: 'bg-orange-100 text-orange-700',
            medium: 'bg-yellow-100 text-yellow-700',
            low: 'bg-gray-100 text-gray-700'
        };

        const dueDateHtml = task.due_date
            ? `<span class="text-[10px] text-secondary flex items-center gap-1">
                 <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                   <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                 </svg>
                 ${new Date(task.due_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}
               </span>`
            : '';

        card.innerHTML = `
            <a href="/students/${this.studentId}/tasks/${task.id}" class="text-sm font-medium text-primary hover:text-accent block mb-2">${task.title}</a>
            <div class="flex items-center justify-between gap-2">
                <span class="text-[10px] px-2 py-0.5 rounded-full ${priorityColors[task.priority] || priorityColors.medium}">
                    ${task.priority ? task.priority.charAt(0).toUpperCase() + task.priority.slice(1) : 'Medium'}
                </span>
                ${dueDateHtml}
            </div>
            ${task.progress > 0 ? `
                <div class="mt-2 w-full bg-gray-100 rounded-full h-1.5">
                    <div class="bg-accent h-1.5 rounded-full transition-all" style="width: ${task.progress}%"></div>
                </div>
            ` : ''}
        `;

        return card;
    }

    /**
     * Destroy the Kanban board
     */
    destroy() {
        this.sortables.forEach(sortable => sortable.destroy());
        this.sortables.clear();
    }
}

/**
 * Initialize Kanban board from DOM
 * @param {Object} options - Configuration options
 * @returns {KanbanBoard} Kanban board instance
 */
export function initKanban(options = {}) {
    const board = new KanbanBoard(options);
    board.init();
    return board;
}

/**
 * Alpine.js component for Kanban board
 * Usage: x-data="kanbanBoard({ studentId: {{ $student->id }} })"
 */
export function kanbanBoard(options = {}) {
    return {
        loading: false,
        board: null,

        async init() {
            this.board = initKanban({
                container: this.$root,
                studentId: options.studentId,
                onTaskClick: (taskId) => {
                    // Navigate to task detail
                    window.location.href = `/students/${options.studentId}/tasks/${taskId}`;
                },
                onStatusChange: (taskId, newStatus) => {
                    console.log(`Task ${taskId} moved to ${newStatus}`);
                },
                onError: (error) => {
                    console.error('Kanban error:', error);
                }
            });
        },

        destroy() {
            this.board?.destroy();
        }
    };
}

export default KanbanBoard;
