<?php

use App\Http\Controllers\Api\AiChatController;
use App\Http\Controllers\Api\AiFeatureController;
use App\Http\Controllers\Api\FileApiController;
use App\Http\Controllers\Api\NotificationApiController;
use App\Http\Controllers\Api\ProgressReportApiController;
use App\Http\Controllers\Api\TaskApiController;
use Illuminate\Support\Facades\Route;

// Use session authentication with web guard
Route::middleware(['auth:web'])->group(function () {
    // Tasks API (Kanban + Gantt)
    Route::get('/students/{student}/tasks', [TaskApiController::class, 'index']);
    Route::put('/tasks/{task}/status', [TaskApiController::class, 'updateStatus']);
    Route::put('/tasks/{task}/progress', [TaskApiController::class, 'updateProgress']);
    Route::post('/tasks/reorder', [TaskApiController::class, 'updateOrder']);
    Route::get('/students/{student}/tasks/gantt', [TaskApiController::class, 'ganttData']);
    Route::put('/tasks/{task}/dates', [TaskApiController::class, 'updateDates']);
    Route::post('/students/{student}/tasks/activity', [TaskApiController::class, 'storeActivity']);
    Route::get('/students/{student}/milestones', [TaskApiController::class, 'milestones']);

    // Progress Reports API
    Route::get('/students/{student}/reports', [ProgressReportApiController::class, 'index']);
    Route::get('/students/{student}/reports/stats', [ProgressReportApiController::class, 'stats']);
    Route::get('/students/{student}/reports/{report}', [ProgressReportApiController::class, 'show']);
    Route::post('/students/{student}/reports', [ProgressReportApiController::class, 'store']);
    Route::put('/students/{student}/reports/{report}', [ProgressReportApiController::class, 'update']);
    Route::delete('/students/{student}/reports/{report}', [ProgressReportApiController::class, 'destroy']);
    Route::post('/students/{student}/reports/{report}/submit', [ProgressReportApiController::class, 'submit']);
    Route::post('/students/{student}/reports/{report}/review', [ProgressReportApiController::class, 'review']);
    Route::get('/students/{student}/reports/{report}/revisions', [ProgressReportApiController::class, 'revisions']);

    // AI Features API
    Route::post('/ai/reports/{report}/summarize', [AiFeatureController::class, 'summarizeReport']);
    Route::post('/ai/students/{student}/reports/summarize', [AiFeatureController::class, 'summarizeReports']);
    Route::get('/ai/students/{student}/deadline-risks', [AiFeatureController::class, 'analyzeDeadlineRisks']);
    Route::post('/ai/students/{student}/suggest-tasks', [AiFeatureController::class, 'suggestTasks']);
    Route::post('/ai/students/{student}/tasks/{task}/suggest-subtasks', [AiFeatureController::class, 'suggestSubtasks']);
    Route::post('/ai/documents/compare', [AiFeatureController::class, 'compareDocuments']);
    Route::get('/ai/students/{student}/files/{file}/compare-versions', [AiFeatureController::class, 'compareVersions']);
    Route::get('/ai/students/{student}/files/{file}/changes-summary', [AiFeatureController::class, 'summarizeFileChanges']);

    // Notifications API
    Route::get('/notifications', [NotificationApiController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationApiController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationApiController::class, 'markAllRead']);
});
