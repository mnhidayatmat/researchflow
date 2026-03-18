/**
 * API Client for Laravel Task Management
 * Handles all API communication with the backend
 */

import axios from 'axios';

// Base configuration
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.headers.common['Accept'] = 'application/json';

// Get CSRF token from meta tag
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
if (csrfToken) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
}

/**
 * Task API endpoints
 */
export const taskApi = {
    /**
     * Fetch all tasks for a student
     * @param {number} studentId - Student ID
     * @returns {Promise<Array>} Tasks array
     */
    async getTasks(studentId) {
        const response = await axios.get(`/api/students/${studentId}/tasks`);
        return response.data;
    },

    /**
     * Fetch Gantt data for a student
     * @param {number} studentId - Student ID
     * @returns {Promise<Array>} Gantt-formatted tasks
     */
    async getGanttData(studentId) {
        const response = await axios.get(`/api/students/${studentId}/tasks/gantt`);
        return response.data;
    },

    /**
     * Update task status
     * @param {number} taskId - Task ID
     * @param {string} status - New status
     * @returns {Promise<Object>} Updated task
     */
    async updateStatus(taskId, status) {
        const response = await axios.put(`/api/tasks/${taskId}/status`, { status });
        return response.data;
    },

    /**
     * Update task order and optionally status
     * @param {Array} tasks - Array of {id, sort_order, status?}
     * @returns {Promise<Object>} Success response
     */
    async updateOrder(tasks) {
        const response = await axios.post('/api/tasks/reorder', { tasks });
        return response.data;
    },

    /**
     * Update task dates
     * @param {number} taskId - Task ID
     * @param {string} startDate - Start date (YYYY-MM-DD)
     * @param {string} dueDate - Due date (YYYY-MM-DD)
     * @returns {Promise<Object>} Updated task
     */
    async updateDates(taskId, startDate, dueDate) {
        const response = await axios.put(`/api/tasks/${taskId}/dates`, {
            start_date: startDate,
            due_date: dueDate
        });
        return response.data;
    },

    /**
     * Update task progress
     * @param {number} taskId - Task ID
     * @param {number} progress - Progress percentage (0-100)
     * @returns {Promise<Object>} Updated task
     */
    async updateProgress(taskId, progress) {
        const response = await axios.put(`/api/tasks/${taskId}/progress`, { progress });
        return response.data;
    }
};

/**
 * Notification API endpoints
 */
export const notificationApi = {
    /**
     * Mark notification as read
     * @param {number} id - Notification ID
     */
    async markAsRead(id) {
        await axios.post(`/api/notifications/${id}/read`);
    },

    /**
     * Mark all notifications as read
     */
    async markAllRead() {
        await axios.post('/api/notifications/read-all');
    }
};

/**
 * Error handler wrapper
 * @param {Function} fn - Async function to wrap
 * @param {Function} onError - Error callback
 * @returns {Function} Wrapped function
 */
export function withErrorHandling(fn, onError = console.error) {
    return async (...args) => {
        try {
            return await fn(...args);
        } catch (error) {
            if (error.response?.status === 422) {
                // Validation errors
                console.error('Validation Error:', error.response.data.errors);
            } else if (error.response?.status === 403) {
                console.error('Authorization Error:', error.response.data.message);
            } else {
                console.error('API Error:', error.message);
            }
            onError(error);
            throw error;
        }
    };
}

/**
 * Export configured axios instance for custom requests
 */
export { axios };
