import './bootstrap';

// Export modules for use in Blade views or as global objects
window.TaskFlow = {
    Api: null,
    Store: null,
    Kanban: null,
    Gantt: null,
    Timeline: null
};

// Dynamic import function for on-demand loading
async function loadModule(name) {
    if (window.TaskFlow[name]) {
        return window.TaskFlow[name];
    }

    try {
        const module = await import(`./modules/${name.toLowerCase()}.js`);
        window.TaskFlow[name] = module;
        return module;
    } catch (error) {
        console.error(`Failed to load module: ${name}`, error);
        return null;
    }
}

// Global initialization helpers - these can be called from Blade views
// Usage: x-data="initTaskFlowKanban({ studentId: 1 })"
window.initTaskFlowKanban = async function(options) {
    const { kanbanBoard } = await import('./modules/kanban.js');
    return kanbanBoard(options);
};

window.initTaskFlowGantt = async function(options) {
    const { ganttChart } = await import('./modules/gantt.js');
    return ganttChart(options);
};

window.initTaskFlowTimeline = async function(options) {
    const { timeline } = await import('./modules/timeline.js');
    return timeline(options);
};
