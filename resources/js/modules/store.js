/**
 * Task Store - Centralized state management for tasks
 * Provides reactive state management using a simple pub/sub pattern
 */

import { taskApi } from './api.js';

/**
 * Task Store class
 */
class TaskStore {
    constructor() {
        this.state = {
            tasks: [],
            loading: false,
            error: null,
            currentStudentId: null,
            lastFetched: null
        };
        this.listeners = new Set();
    }

    /**
     * Subscribe to state changes
     * @param {Function} listener - Callback function
     * @returns {Function} Unsubscribe function
     */
    subscribe(listener) {
        this.listeners.add(listener);
        return () => this.listeners.delete(listener);
    }

    /**
     * Notify all listeners of state change
     */
    notify() {
        this.listeners.forEach(listener => listener(this.state));
    }

    /**
     * Update state and notify listeners
     * @param {Object} updates - State updates
     */
    setState(updates) {
        this.state = { ...this.state, ...updates };
        this.notify();
    }

    /**
     * Fetch tasks for a student
     * @param {number} studentId - Student ID
     */
    async fetchTasks(studentId) {
        this.setState({ loading: true, error: null });
        try {
            const tasks = await taskApi.getTasks(studentId);
            this.setState({
                tasks,
                currentStudentId: studentId,
                loading: false,
                lastFetched: new Date()
            });
        } catch (error) {
            this.setState({ loading: false, error: error.message });
            throw error;
        }
    }

    /**
     * Get tasks grouped by status for Kanban
     * @returns {Object} Tasks grouped by status
     */
    getTasksByStatus() {
        const grouped = {
            backlog: [],
            planned: [],
            in_progress: [],
            waiting_review: [],
            revision: [],
            completed: []
        };

        this.state.tasks.forEach(task => {
            if (grouped[task.status]) {
                grouped[task.status].push(task);
            }
            // Also add subtasks
            if (task.subtasks) {
                task.subtasks.forEach(subtask => {
                    if (grouped[subtask.status]) {
                        grouped[subtask.status].push(subtask);
                    }
                });
            }
        });

        return grouped;
    }

    /**
     * Get tasks with dates for Gantt/Timeline
     * @returns {Array} Tasks with start and end dates
     */
    getTasksWithDates() {
        const tasks = [];

        const addTask = (task) => {
            if (task.start_date && task.due_date) {
                tasks.push({
                    id: String(task.id),
                    name: task.title,
                    start: task.start_date,
                    end: task.due_date,
                    progress: task.progress || 0,
                    status: task.status,
                    dependencies: task.dependencies?.map(d => String(d.id)).join(',') || '',
                    custom_class: `gantt-${task.status}`,
                    // Extra data for custom handling
                    _task: task
                });
            }
            if (task.subtasks) {
                task.subtasks.forEach(addTask);
            }
        };

        this.state.tasks.forEach(addTask);
        return tasks;
    }

    /**
     * Update a task in local state
     * @param {number} taskId - Task ID
     * @param {Object} updates - Task updates
     */
    updateLocalTask(taskId, updates) {
        const updateTask = (tasks) => {
            for (const task of tasks) {
                if (task.id === taskId) {
                    Object.assign(task, updates);
                    return true;
                }
                if (task.subtasks?.length > 0) {
                    if (updateTask(task.subtasks)) return true;
                }
            }
            return false;
        };

        const newTasks = [...this.state.tasks];
        updateTask(newTasks);
        this.setState({ tasks: newTasks });
    }

    /**
     * Reorder tasks in local state
     * @param {Array} reorderedTasks - Array of {id, sort_order, status?}
     */
    reorderTasks(reorderedTasks) {
        const taskMap = new Map();

        // Flatten all tasks into a map
        const flattenTasks = (tasks) => {
            tasks.forEach(task => {
                taskMap.set(task.id, task);
                if (task.subtasks) {
                    flattenTasks(task.subtasks);
                }
            });
        };
        flattenTasks(this.state.tasks);

        // Update sort order and optionally status
        reorderedTasks.forEach(({ id, sort_order, status }) => {
            const task = taskMap.get(id);
            if (task) {
                task.sort_order = sort_order;
                if (status) task.status = status;
            }
        });

        // Re-sort tasks by sort_order within each status
        const sortTasks = (tasks) => {
            return tasks.sort((a, b) => a.sort_order - b.sort_order)
                .map(task => ({
                    ...task,
                    subtasks: task.subtasks ? sortTasks(task.subtasks) : []
                }));
        };

        this.setState({ tasks: sortTasks([...this.state.tasks]) });
    }

    /**
     * Clear all state
     */
    clear() {
        this.state = {
            tasks: [],
            loading: false,
            error: null,
            currentStudentId: null,
            lastFetched: null
        };
        this.notify();
    }
}

/**
 * Create singleton instance
 */
export const taskStore = new TaskStore();

/**
 * Export class for creating additional instances if needed
 */
export { TaskStore };
