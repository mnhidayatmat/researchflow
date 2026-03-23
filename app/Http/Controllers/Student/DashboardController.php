<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        $student = $this->effectiveStudent();

        if (!$student) {
            if (
                auth()->user()?->role === 'admin'
                && session()->get('admin_role_switch') === 'student'
            ) {
                return redirect()
                    ->route('admin.role-switch-select-student', ['role' => 'student'])
                    ->with('error', 'Select a student before opening the student dashboard.');
            }

            abort(404, 'Student profile not found for this account.');
        }

        $tasks = $student->tasks()
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();

        $tasksByStatus = $tasks->groupBy('status');

        $upcomingTasks = $student->tasks()
            ->whereNotNull('due_date')
            ->where('due_date', '>=', now())
            ->where('status', '!=', 'completed')
            ->orderBy('due_date')
            ->take(5)->get();

        $recentReports = $student->progressReports()->latest()->take(3)->get();
        $upcomingMeetings = $student->meetings()->where('scheduled_at', '>=', now())->where('status', 'scheduled')->orderBy('scheduled_at')->take(3)->get();
        $recentPublications = $student->publicationTracks()->latest('submission_date')->latest()->take(3)->get();

        return view('student.dashboard', compact('student', 'tasks', 'tasksByStatus', 'upcomingTasks', 'recentReports', 'upcomingMeetings', 'recentPublications'));
    }
}
