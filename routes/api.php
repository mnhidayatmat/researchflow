<?php

use App\Http\Controllers\Api\AiChatController;
use App\Http\Controllers\Api\AiFeatureController;
use App\Http\Controllers\Api\FileApiController;
use App\Http\Controllers\Api\NotificationApiController;
use App\Http\Controllers\Api\ProgressReportApiController;
use App\Http\Controllers\Api\TaskApiController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    // Tasks API (Kanban + Gantt)
    Route::get('/students/{student}/tasks', [TaskApiController::class, 'index']);
    Route::put('/tasks/{task}/status', [TaskApiController::class, 'updateStatus']);
    Route::put('/tasks/{task}/progress', [TaskApiController::class, 'updateProgress']);
    Route::post('/tasks/reorder', [TaskApiController::class, 'updateOrder']);
    Route::get('/students/{student}/tasks/gantt', [TaskApiController::class, 'ganttData']);
    Route::put('/tasks/{task}/dates', [TaskApiController::class, 'updateDates']);

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

    // Files API
    Route::get('/students/{student}/files', [FileApiController::class, 'index']);
    Route::post('/students/{student}/files', [FileApiController::class, 'upload']);
    Route::post('/students/{student}/files/upload-multiple', [FileApiController::class, 'uploadMultiple']);
    Route::get('/students/{student}/files/{file}', [FileApiController::class, 'show']);
    Route::put('/students/{student}/files/{file}', [FileApiController::class, 'update']);
    Route::delete('/students/{student}/files/{file}', [FileApiController::class, 'destroy']);
    Route::post('/students/{student}/files/{file}/restore', [FileApiController::class, 'restore']);
    Route::get('/students/{student}/files/{file}/download', [FileApiController::class, 'download']);
    Route::get('/students/{student}/files/{file}/url', [FileApiController::class, 'getUrl']);
    Route::post('/students/{student}/files/{file}/temp-url', [FileApiController::class, 'getTemporaryUrl']);
    Route::post('/students/{student}/files/{file}/versions', [FileApiController::class, 'uploadVersion']);
    Route::get('/students/{student}/files/{file}/versions', [FileApiController::class, 'versions']);
    Route::post('/students/{student}/files/{file}/move', [FileApiController::class, 'move']);
    Route::post('/students/{student}/files/{file}/copy', [FileApiController::class, 'copy']);

    // Folders API
    Route::get('/students/{student}/folders', [FileApiController::class, 'folders']);
    Route::get('/students/{student}/folders/tree', [FileApiController::class, 'folderTree']);
    Route::post('/students/{student}/folders', [FileApiController::class, 'createFolder']);
    Route::put('/students/{student}/folders/{folder}', [FileApiController::class, 'updateFolder']);
    Route::delete('/students/{student}/folders/{folder}', [FileApiController::class, 'deleteFolder']);

    // Files Search
    Route::get('/students/{student}/files/search', [FileApiController::class, 'search']);
    Route::get('/students/{student}/storage/usage', [FileApiController::class, 'usage']);

    // AI Chat API
    Route::get('/ai/conversations', [AiChatController::class, 'conversations']);
    Route::post('/ai/conversations', [AiChatController::class, 'createConversation']);
    Route::get('/ai/conversations/{conversation}/messages', [AiChatController::class, 'messages']);
    Route::post('/ai/conversations/{conversation}/messages', [AiChatController::class, 'sendMessage']);

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
