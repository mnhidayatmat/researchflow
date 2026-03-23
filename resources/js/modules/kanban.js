/**
 * Kanban Board Module - SortableJS Integration
 * Provides drag-and-drop Kanban functionality for task management
 */

import { taskApi } from './api.js';

/**
 * Kanban column configuration
 */
export const KANBAN_COLUMNS = {
    planned: { label: 'Planned', color: 'bg-blue-400', next: 'in_progress' },
    in_progress: { label: 'In Progress', color: 'bg-yellow-400', next: 'waiting_review' },
    waiting_review: { label: 'Waiting Review', color: 'bg-orange-400', next: 'completed' },
    revision: { label: 'Revision', color: 'bg-purple-400', next: 'waiting_review' },
    completed: { label: 'Completed', color: 'bg-green-400', next: null }
};

/**
 * Initialize Kanban Board - Alpine.js component
 * Usage: x-data="initKanbanBoard({ studentId: {{ $student->id }} })"
 */
export function initKanbanBoard(options = {}) {
    return {
        studentId: options.studentId,
        loading: false,
        sortables: new Map(),
        csrfToken: document.querySelector('meta[name="csrf-token"]')?.content || '',
        columnCounts: {},

        async init() {
            await this.loadSortableLibrary();
            this.initializeSortables();
            this.updateAllColumnCounts();
        },

        async loadSortableLibrary() {
            return new Promise((resolve) => {
                if (typeof Sortable !== 'undefined') {
                    resolve();
                    return;
                }
                const script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js';
                script.onload = resolve;
                document.head.appendChild(script);
            });
        },

        initializeSortables() {
            const columns = this.$root.querySelectorAll('[data-kanban-column]');
            columns.forEach(column => {
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
        },

        async handleDrop(evt) {
            if (this.loading) return;

            const taskId = parseInt(evt.item.dataset.taskId);
            const newStatus = evt.to.dataset.kanbanColumn;
            const oldStatus = evt.from.dataset.kanbanColumn;

            // No change, skip
            if (newStatus === oldStatus && evt.newIndex === evt.oldIndex) return;

            this.loading = true;
            this.showLoadingState(evt.item);

            try {
                // Build tasks array with new order
                const tasks = this.buildTasksArray();

                // Update order on server
                await taskApi.updateOrder(tasks);

                // Update status if changed
                if (newStatus !== oldStatus) {
                    await this.updateTaskStatus(taskId, newStatus);
                }

                this.updateAllColumnCounts();
                this.hideLoadingState(evt.item);
            } catch (error) {
                console.error('Kanban error:', error);
                // Revert on error
                evt.from.insertBefore(evt.item, evt.from.children[evt.oldIndex]);
                this.hideLoadingState(evt.item);
            } finally {
                this.loading = false;
            }
        },

        buildTasksArray() {
            const tasks = [];
            let sortOrder = 0;
            const columns = this.$root.querySelectorAll('[data-kanban-column]');

            columns.forEach(column => {
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
        },

        async updateTaskStatus(taskId, status) {
            const response = await fetch(`/api/tasks/${taskId}/status`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                },
                body: JSON.stringify({ status })
            });

            if (!response.ok) {
                throw new Error('Failed to update status');
            }

            // Update the card's data attribute
            const card = this.$root.querySelector(`[data-task-id="${taskId}"]`);
            if (card) {
                card.dataset.taskStatus = status;
            }
        },

        async moveToNext(taskId, nextStatus) {
            if (this.loading) return;

            const card = this.$root.querySelector(`[data-task-id="${taskId}"]`);
            if (!card) return;

            this.loading = true;
            this.showLoadingState(card);

            try {
                // Update status on server
                await this.updateTaskStatus(taskId, nextStatus);

                // Move the card to the new column
                const newColumn = this.$root.querySelector(`[data-kanban-column="${nextStatus}"]`);

                if (card && newColumn) {
                    // Update data attribute
                    card.dataset.taskStatus = nextStatus;

                    // Add animation
                    card.style.transform = 'scale(0.95)';
                    card.style.opacity = '0.5';

                    // Move to new column
                    setTimeout(() => {
                        newColumn.appendChild(card);

                        // Update counts
                        this.updateAllColumnCounts();

                        // Reset animation
                        setTimeout(() => {
                            card.style.transform = '';
                            card.style.opacity = '';
                        }, 50);
                    }, 150);
                }
            } catch (error) {
                console.error('Failed to move task:', error);
                alert('Failed to move task. Please try again.');
                this.hideLoadingState(card);
            } finally {
                this.loading = false;
            }
        },

        getColumnCount(status) {
            const column = this.$root?.querySelector(`[data-kanban-column="${status}"]`);
            if (!column) return 0;
            return column.querySelectorAll('[data-task-id]').length;
        },

        updateAllColumnCounts() {
            const statuses = ['planned', 'in_progress', 'waiting_review', 'revision', 'completed'];
            statuses.forEach(status => {
                this.columnCounts[status] = this.getColumnCount(status);
            });

            // Also update the DOM elements with x-text
            this.$nextTick(() => {
                const columns = this.$root?.querySelectorAll('[data-kanban-column]');
                if (columns) {
                    columns.forEach(column => {
                        const count = column.querySelectorAll('[data-task-id]').length;
                        const status = column.dataset.kanbanColumn;
                        const countEl = column.querySelector('.column-count');
                        if (countEl) {
                            countEl.textContent = count;
                        }
                    });
                }
            });
        },

        showLoadingState(card) {
            if (!card) return;
            card.classList.add('kanban-loading');
            card.style.opacity = '0.6';
        },

        hideLoadingState(card) {
            if (!card) return;
            card.classList.remove('kanban-loading');
            card.style.opacity = '';
        },

        destroy() {
            this.sortables.forEach(sortable => sortable?.destroy());
            this.sortables.clear();
        }
    };
}

export default initKanbanBoard;
