import './bootstrap';
import { initKanbanBoard } from './modules/kanban.js';

// Export modules for use in Blade views or as global objects
window.TaskFlow = {
    Api: null,
    Store: null,
    Kanban: null
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
// Usage: x-data="initKanbanBoard({ studentId: 1 })"
window.initKanbanBoard = function(options) {
    return initKanbanBoard(options);
};
