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
            ->whereHas('student.user')
            ->latest('submitted_at')
            ->take(3)
            ->get()
            ->map(fn($report) => [
                'type' => 'report',
                'title' => $report->title,
                'student' => $report->student->user->name ?? 'Unknown',
                'time' => $report->submitted_at->diffForHumans(),
                'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
            ]);

        // Tasks due soon
        $tasksDue = Task::with(['student.user'])
            ->where('due_date', '<=', now()->addWeek())
            ->where('status', '!=', 'completed')
            ->whereHas('student.user')
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

        $role = $validated['role'];

        // Reset any existing role switch
        session()->forget('admin_role_switch');
        session()->forget('admin_view_as_student_id');

        // For admin role, just return to admin dashboard
        if ($role === 'admin') {
            return redirect()->route('admin.dashboard')->with('success', 'Returned to admin view.');
        }

        // For admin switching roles, redirect to student selection page
        // Admins can view any student regardless of supervisor assignment
        session()->put('admin_role_switch', $role);

        return redirect()->route('admin.role-switch-select-student', ['role' => $role]);
    }

    public function resetRole()
    {
        session()->forget('admin_role_switch');
        session()->forget('admin_view_as_student_id');

        return redirect()->route('admin.dashboard')->with('success', 'Returned to admin view.');
    }

    /**
     * Show student selection page for role switching
     */
    public function showStudentSelection(Request $request, string $role)
    {
        // For admin role switching, show all active students regardless of supervisor assignment
        $isAdminSwitching = session()->get('admin_role_switch') && auth()->user()->role === 'admin';

        $students = match($role) {
            'student' => \App\Models\Student::with(['user', 'programme'])->where('status', 'active')->get(),
            'supervisor', 'cosupervisor' => $isAdminSwitching
                ? \App\Models\Student::with(['user', 'programme'])->where('status', 'active')->get()
                : \App\Models\Student::with(['user', 'programme'])->where('status', 'active')
                    ->where(function($query) {
                        $query->where('supervisor_id', auth()->id())
                              ->orWhere('cosupervisor_id', auth()->id());
                    })->get(),
            default => collect(),
        };

        return view('admin.role-switch-student', compact('role', 'students'));
    }

    /**
     * Store the selected student for role viewing
     */
    public function storeStudentSelection(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
        ]);

        session()->put('admin_view_as_student_id', $validated['student_id']);

        // Redirect to appropriate dashboard
        $role = session()->get('admin_role_switch');

        return match($role) {
            'student' => redirect()->route('student.dashboard'),
            'supervisor', 'cosupervisor' => redirect()->route('supervisor.students.index'),
            default => redirect()->route('admin.dashboard'),
        };
    }
}
