<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Api\TaskApiController;
use App\Http\Controllers\Api\NotificationApiController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\PublicationTrackController;
use App\Http\Controllers\ProgressReportController;
use App\Http\Controllers\Supervisor\CollaboratorController;
use App\Http\Controllers\Supervisor\PublicationController;
use App\Http\Controllers\Supervisor\StudentApprovalController;
use App\Http\Controllers\Supervisor;
use App\Http\Controllers\Supervisor\GrantController;
use App\Http\Controllers\Supervisor\GrantDocumentController;
use App\Http\Controllers\Student;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TimelineController;
use App\Http\Controllers\AiChatPageController;
use App\Http\Controllers\LiteratureMatrixController;
use App\Http\Controllers\UserStorageController;
use App\Http\Controllers\UserSettingsController;
use Illuminate\Support\Facades\Route;

// Supervisor student approval routes — signed URLs, no auth required (sent via email)
Route::get('/supervisor/approve/{student}', [StudentApprovalController::class, 'approve'])->name('supervisor.student.approve');
Route::get('/supervisor/deny/{student}', [StudentApprovalController::class, 'deny'])->name('supervisor.student.deny');

// Google OAuth routes (outside guest middleware — callback must be accessible)
Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
Route::get('/auth/google/complete', [GoogleAuthController::class, 'showComplete'])->name('auth.google.complete');
Route::post('/auth/google/complete', [GoogleAuthController::class, 'complete'])->name('auth.google.complete.post');

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegister'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
});

Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware('signed')
    ->name('verification.verify');

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Landing page
Route::get('/', function () {
    if (auth()->check()) {
        $role = auth()->user()->role;
        $effectiveRole = session()->get('admin_role_switch', $role);
        $targetRole = $effectiveRole ?: $role;

        return redirect(match ($targetRole) {
            'admin' => '/admin/dashboard',
            'supervisor', 'cosupervisor' => '/supervisor/dashboard',
            'student' => '/student/dashboard',
        });
    }
    return view('landing');
})->name('landing');

// Admin routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::post('/role-switch', [Admin\DashboardController::class, 'switchRole'])->name('switch-role');
    Route::post('/role-switch-reset', [Admin\DashboardController::class, 'resetRole'])->name('switch-role-reset');
    Route::get('/role-switch/{role}', [Admin\DashboardController::class, 'showStudentSelection'])->name('role-switch-select-student');
    Route::post('/role-switch-student', [Admin\DashboardController::class, 'storeStudentSelection'])->name('role-switch-student');

    Route::resource('students', Admin\StudentManagementController::class);
    Route::post('students/{student}/approve', [Admin\StudentManagementController::class, 'approve'])->name('students.approve');

    Route::resource('programmes', Admin\ProgrammeController::class)->except('show');

    Route::get('/settings/storage', [UserStorageController::class, 'edit'])->name('settings.storage');
    Route::post('/settings/storage', [UserStorageController::class, 'update'])->name('settings.storage.update');
    Route::post('/settings/storage/test', [UserStorageController::class, 'test'])->name('settings.storage.test');
    Route::get('/settings/ai', [Admin\SettingsController::class, 'ai'])->name('settings.ai');
    Route::post('/settings/ai', [Admin\SettingsController::class, 'updateAi'])->name('settings.ai.update');
    Route::get('/settings/users', [Admin\SettingsController::class, 'users'])->name('settings.users');
    Route::put('/settings/users/{user}/role', [Admin\SettingsController::class, 'updateRole'])->name('settings.users.role');
    Route::put('/settings/users/{user}/status', [Admin\SettingsController::class, 'updateStatus'])->name('settings.users.status');
});

// Supervisor routes
Route::prefix('supervisor')->name('supervisor.')->middleware(['auth', 'role:supervisor,cosupervisor'])->group(function () {
    Route::get('/dashboard', [Supervisor\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/students', [Supervisor\StudentViewController::class, 'index'])->name('students.index');
    Route::get('/students/{student}', [Supervisor\StudentViewController::class, 'show'])->name('students.show');
    Route::resource('grants', GrantController::class);
    Route::post('grants/{grant}/documents', [GrantDocumentController::class, 'store'])->name('grants.documents.store');
    Route::delete('grants/{grant}/documents/{document}', [GrantDocumentController::class, 'destroy'])->name('grants.documents.destroy');
    Route::get('grants/{grant}/documents/{document}/download', [GrantDocumentController::class, 'download'])->name('grants.documents.download');
    Route::resource('collaborators', CollaboratorController::class);
    Route::resource('publications', PublicationController::class)->except('show');
    Route::get('/storage', [UserStorageController::class, 'edit'])->name('storage.edit');
    Route::post('/storage', [UserStorageController::class, 'update'])->name('storage.update');
    Route::post('/storage/test', [UserStorageController::class, 'test'])->name('storage.test');
});

// Student routes
Route::prefix('student')->name('student.')->middleware(['auth', 'role:student'])->group(function () {
    Route::get('/dashboard', [Student\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/reports', [Student\ReportsController::class, 'index'])->name('reports.index');
});

// Shared resource routes (policy-based access)
Route::middleware('auth')->group(function () {
    Route::get('/storage/google/connect', [UserStorageController::class, 'redirectToGoogle'])->name('storage.google.connect');
    Route::get('/storage/google/callback', [UserStorageController::class, 'handleGoogleCallback'])->name('storage.google.callback');
    Route::post('/storage/google/disconnect', [UserStorageController::class, 'disconnectGoogle'])->name('storage.google.disconnect');

    // Tasks
    Route::get('/students/{student}/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::get('/students/{student}/tasks/kanban', [TaskController::class, 'kanban'])->name('tasks.kanban');
    Route::get('/students/{student}/tasks/gantt', [TaskController::class, 'gantt'])->name('tasks.gantt');
    Route::get('/students/{student}/tasks/create', [TaskController::class, 'create'])->name('tasks.create');
    Route::post('/students/{student}/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::get('/students/{student}/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
    Route::get('/students/{student}/tasks/{task}/edit', [TaskController::class, 'edit'])->name('tasks.edit');
    Route::put('/students/{student}/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/students/{student}/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');

    // Progress reports
    Route::get('/students/{student}/reports', [ProgressReportController::class, 'index'])->name('reports.index');
    Route::get('/students/{student}/reports/create', [ProgressReportController::class, 'create'])->name('reports.create');
    Route::post('/students/{student}/reports', [ProgressReportController::class, 'store'])->name('reports.store');
    Route::get('/students/{student}/reports/{report}', [ProgressReportController::class, 'show'])->name('reports.show');
    Route::get('/students/{student}/reports/{report}/edit', [ProgressReportController::class, 'edit'])->name('reports.edit');
    Route::put('/students/{student}/reports/{report}', [ProgressReportController::class, 'update'])->name('reports.update');
    Route::post('/students/{student}/reports/{report}/review', [ProgressReportController::class, 'review'])->name('reports.review');
    Route::get('/students/{student}/reports/{report}/attachment', [ProgressReportController::class, 'downloadAttachment'])->name('reports.download-attachment');

    // Meetings
    Route::get('/students/{student}/meetings', [MeetingController::class, 'index'])->name('meetings.index');
    Route::get('/students/{student}/meetings/create', [MeetingController::class, 'create'])->name('meetings.create');
    Route::post('/students/{student}/meetings', [MeetingController::class, 'store'])->name('meetings.store');
    Route::get('/students/{student}/meetings/{meeting}', [MeetingController::class, 'show'])->name('meetings.show');
    Route::put('/students/{student}/meetings/{meeting}', [MeetingController::class, 'update'])->name('meetings.update');

    // Publication tracking
    Route::get('/students/{student}/publications', [PublicationTrackController::class, 'index'])->name('publications.index');
    Route::get('/students/{student}/publications/create', [PublicationTrackController::class, 'create'])->name('publications.create');
    Route::post('/students/{student}/publications', [PublicationTrackController::class, 'store'])->name('publications.store');
    Route::get('/students/{student}/publications/{publicationTrack}/edit', [PublicationTrackController::class, 'edit'])->name('publications.edit');
    Route::put('/students/{student}/publications/{publicationTrack}', [PublicationTrackController::class, 'update'])->name('publications.update');
    Route::delete('/students/{student}/publications/{publicationTrack}', [PublicationTrackController::class, 'destroy'])->name('publications.destroy');

    // Literature Matrix
    Route::get('/students/{student}/literature', [LiteratureMatrixController::class, 'index'])->name('literature.index');
    Route::post('/students/{student}/literature', [LiteratureMatrixController::class, 'store'])->name('literature.store');
    Route::put('/students/{student}/literature/{entry}', [LiteratureMatrixController::class, 'update'])->name('literature.update');
    Route::delete('/students/{student}/literature/{entry}', [LiteratureMatrixController::class, 'destroy'])->name('literature.destroy');
    Route::post('/students/{student}/literature/reorder', [LiteratureMatrixController::class, 'reorder'])->name('literature.reorder');
    Route::post('/students/{student}/literature/config', [LiteratureMatrixController::class, 'updateConfig'])->name('literature.config');
    Route::get('/students/{student}/literature/export', [LiteratureMatrixController::class, 'export'])->name('literature.export');
    Route::get('/students/{student}/literature/template', [LiteratureMatrixController::class, 'template'])->name('literature.template');
    Route::post('/students/{student}/literature/import/preview', [LiteratureMatrixController::class, 'importPreview'])->name('literature.import.preview');
    Route::post('/students/{student}/literature/import', [LiteratureMatrixController::class, 'import'])->name('literature.import');

    // Files
    Route::get('/students/{student}/files', [FileController::class, 'index'])->name('files.index');
    Route::post('/students/{student}/files/upload', [FileController::class, 'upload'])->name('files.upload');
    Route::post('/students/{student}/files/{file}/version', [FileController::class, 'uploadVersion'])->name('files.upload-version');
    Route::get('/students/{student}/files/{file}/download', [FileController::class, 'download'])->name('files.download');
    Route::get('/students/{student}/files/{file}/versions', [FileController::class, 'versions'])->name('files.versions');
    Route::post('/students/{student}/files/folder', [FileController::class, 'createFolder'])->name('files.create-folder');
    Route::post('/students/{student}/files/default-folders', [FileController::class, 'createDefaultFolders'])->name('files.create-default-folders');
    Route::delete('/students/{student}/folders/{folder}', [FileController::class, 'deleteFolder'])->name('folders.delete');
    Route::delete('/students/{student}/files/{file}', [FileController::class, 'destroy'])->name('files.destroy');

    // AI Chat
    Route::get('/ai/chat', [AiChatPageController::class, 'index'])->name('ai.chat');
    Route::get('/ai/chat/student/{student}', [AiChatPageController::class, 'studentContext'])->name('ai.chat.student');

    // User Settings
    Route::post('/settings/theme', [UserSettingsController::class, 'updateTheme'])->name('settings.theme');

    // API routes for AJAX requests (using web middleware for session auth)
    Route::prefix('api')->group(function () {
        // Tasks API (Kanban + Gantt)
        Route::get('/students/{student}/tasks', [TaskApiController::class, 'index']);
        Route::put('/tasks/{task}/status', [TaskApiController::class, 'updateStatus']);
        Route::put('/tasks/{task}/progress', [TaskApiController::class, 'updateProgress']);
        Route::post('/tasks/reorder', [TaskApiController::class, 'updateOrder']);
        Route::get('/students/{student}/tasks/gantt', [TaskApiController::class, 'ganttData']);
        Route::put('/tasks/{task}/dates', [TaskApiController::class, 'updateDates']);
        Route::post('/students/{student}/tasks/activity', [TaskApiController::class, 'storeActivity']);
        Route::get('/students/{student}/milestones', [TaskApiController::class, 'milestones']);

        // AI Chat API
        Route::get('/ai/conversations', [\App\Http\Controllers\Api\AiChatController::class, 'conversations']);
        Route::post('/ai/context-files', [\App\Http\Controllers\Api\AiChatController::class, 'uploadContextFile']);
        Route::delete('/ai/context-files/{contextFile}', [\App\Http\Controllers\Api\AiChatController::class, 'deleteContextFile']);
        Route::get('/ai/projects', [\App\Http\Controllers\Api\AiChatController::class, 'projects']);
        Route::post('/ai/projects', [\App\Http\Controllers\Api\AiChatController::class, 'createProject']);
        Route::delete('/ai/projects/{project}', [\App\Http\Controllers\Api\AiChatController::class, 'deleteProject']);
        Route::post('/ai/conversations', [\App\Http\Controllers\Api\AiChatController::class, 'createConversation']);
        Route::get('/ai/conversations/{conversation}/messages', [\App\Http\Controllers\Api\AiChatController::class, 'messages']);
        Route::get('/ai/cowork/directories', [\App\Http\Controllers\Api\AiChatController::class, 'browseCoworkDirectories']);
        Route::post('/ai/conversations/{conversation}/cowork-plan', [\App\Http\Controllers\Api\AiChatController::class, 'coworkPlan']);
        Route::post('/ai/conversations/{conversation}/cowork-complete', [\App\Http\Controllers\Api\AiChatController::class, 'coworkComplete']);
        Route::post('/ai/conversations/{conversation}/messages', [\App\Http\Controllers\Api\AiChatController::class, 'sendMessage']);
        Route::post('/ai/conversations/{conversation}/cowork', [\App\Http\Controllers\Api\AiChatController::class, 'coworkMessage']);
        Route::delete('/ai/conversations/{conversation}', [\App\Http\Controllers\Api\AiChatController::class, 'deleteConversation']);

        // Files API
        Route::get('/students/{student}/files', [\App\Http\Controllers\Api\FileApiController::class, 'index']);
        Route::post('/students/{student}/files/upload-multiple', [\App\Http\Controllers\Api\FileApiController::class, 'uploadMultiple']);

        // Notifications API
        Route::get('/notifications', [NotificationApiController::class, 'index']);
        Route::post('/notifications/{id}/read', [NotificationApiController::class, 'markAsRead']);
        Route::post('/notifications/read-all', [NotificationApiController::class, 'markAllRead']);
    });

    // Global Timeline Overview (all roles)
    Route::get('/timeline', [TimelineController::class, 'index'])->name('timeline.index');
    Route::get('/timeline/{student}', [TimelineController::class, 'show'])->name('timeline.show');
});
