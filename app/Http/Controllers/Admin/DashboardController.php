<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\ProgressReport;
use App\Models\Student;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Calculate trends (compare with last month)
        $lastMonth = now()->subMonth();
        $studentTrend = Student::where('created_at', '>=', $lastMonth)->count();

        $stats = [
            'total_students' => Student::count(),
            'active_students' => Student::where('status', 'active')->count(),
            'pending_reviews' => ProgressReport::where('status', 'submitted')->count(),
            'tasks_due' => Task::where('due_date', '<=', now()->addWeek())->where('status', '!=', 'completed')->count(),
            'student_trend' => "+{$studentTrend} this month",
        ];

        // Recent students (for table)
        $recentStudents = Student::with(['user', 'programme'])
            ->latest()->take(5)->get();

        // Pending approvals
        $pendingApprovals = Student::with(['user', 'programme'])
            ->where('status', 'pending')->latest()->take(5)->get();

        // Recent activity (latest submissions, updates)
        $recentActivity = ProgressReport::with(['student.user'])
            ->where('status', 'submitted')
            ->latest('submitted_at')
            ->take(3)
            ->get()
            ->map(fn($report) => [
                'type' => 'report',
                'title' => $report->title,
                'student' => $report->student->user->name,
                'time' => $report->submitted_at->diffForHumans(),
                'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
            ]);

        // Tasks due soon
        $tasksDue = Task::with(['student.user'])
            ->where('due_date', '<=', now()->addWeek())
            ->where('status', '!=', 'completed')
            ->orderBy('due_date')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'stats',
            'recentStudents',
            'pendingApprovals',
            'recentActivity',
            'tasksDue'
        ));
    }

    public function switchRole(Request $request)
    {
        $validated = $request->validate([
            'role' => 'required|in:student,supervisor,cosupervisor,admin',
        ]);

        // Store the role switch in session
        session()->put('admin_role_switch', $validated['role']);

        return back()->with('success', "Switched to {$validated['role']} view.");
    }

    public function resetRole()
    {
        session()->forget('admin_role_switch');

        return back()->with('success', 'Returned to admin view.');
    }
}
