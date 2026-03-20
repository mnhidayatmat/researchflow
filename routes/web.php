<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\ProgressReportController;
use App\Http\Controllers\Supervisor;
use App\Http\Controllers\Student;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TimelineController;
use App\Http\Controllers\AiChatPageController;
use App\Http\Controllers\UserSettingsController;
use Illuminate\Support\Facades\Route;

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegister'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Redirect root
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
    return redirect('/login');
});

// Admin routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::post('/role-switch', [Admin\DashboardController::class, 'switchRole'])->name('switch-role');
    Route::post('/role-switch-reset', [Admin\DashboardController::class, 'resetRole'])->name('switch-role-reset');

    Route::resource('students', Admin\StudentManagementController::class);
    Route::post('students/{student}/approve', [Admin\StudentManagementController::class, 'approve'])->name('students.approve');

    Route::resource('programmes', Admin\ProgrammeController::class)->except('show');

    Route::resource('templates', Admin\JourneyTemplateController::class);

    Route::get('/settings/storage', [Admin\SettingsController::class, 'storage'])->name('settings.storage');
    Route::post('/settings/storage', [Admin\SettingsController::class, 'updateStorage'])->name('settings.storage.update');
    Route::post('/settings/storage/test', [Admin\SettingsController::class, 'testStorage'])->name('settings.storage.test');
    Route::get('/settings/storage/stats', [Admin\SettingsController::class, 'getStorageStats'])->name('settings.storage.stats');
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
});

// Student routes
Route::prefix('student')->name('student.')->middleware(['auth', 'role:student'])->group(function () {
    Route::get('/dashboard', [Student\DashboardController::class, 'index'])->name('dashboard');
});

// Shared resource routes (policy-based access)
Route::middleware('auth')->group(function () {
    // Tasks
    Route::get('/students/{student}/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::get('/students/{student}/tasks/kanban', [TaskController::class, 'kanban'])->name('tasks.kanban');
    Route::get('/students/{student}/tasks/gantt', [TaskController::class, 'gantt'])->name('tasks.gantt');
    Route::get('/students/{student}/tasks/timeline', [TaskController::class, 'timeline'])->name('tasks.timeline');
    Route::get('/students/{student}/tasks/timeline-overview', [TaskController::class, 'timelineOverview'])->name('tasks.timeline-overview');
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

    // Meetings
    Route::get('/students/{student}/meetings', [MeetingController::class, 'index'])->name('meetings.index');
    Route::get('/students/{student}/meetings/create', [MeetingController::class, 'create'])->name('meetings.create');
    Route::post('/students/{student}/meetings', [MeetingController::class, 'store'])->name('meetings.store');
    Route::get('/students/{student}/meetings/{meeting}', [MeetingController::class, 'show'])->name('meetings.show');
    Route::put('/students/{student}/meetings/{meeting}', [MeetingController::class, 'update'])->name('meetings.update');

    // Files
    Route::get('/students/{student}/files', [FileController::class, 'index'])->name('files.index');
    Route::post('/students/{student}/files/upload', [FileController::class, 'upload'])->name('files.upload');
    Route::post('/students/{student}/files/{file}/version', [FileController::class, 'uploadVersion'])->name('files.upload-version');
    Route::get('/students/{student}/files/{file}/download', [FileController::class, 'download'])->name('files.download');
    Route::get('/students/{student}/files/{file}/versions', [FileController::class, 'versions'])->name('files.versions');
    Route::post('/students/{student}/files/folder', [FileController::class, 'createFolder'])->name('files.create-folder');
    Route::delete('/students/{student}/files/{file}', [FileController::class, 'destroy'])->name('files.destroy');

    // AI Chat
    Route::get('/ai/chat', [AiChatPageController::class, 'index'])->name('ai.chat');
    Route::get('/ai/chat/student/{student}', [AiChatPageController::class, 'studentContext'])->name('ai.chat.student');

    // User Settings
    Route::post('/settings/theme', [UserSettingsController::class, 'updateTheme'])->name('settings.theme');

    // Global Timeline Overview (all roles)
    Route::get('/timeline', [TimelineController::class, 'index'])->name('timeline.index');
    Route::get('/timeline/{student}', [TimelineController::class, 'show'])->name('timeline.show');
});