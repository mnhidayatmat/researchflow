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
        $stats = [
            'total_students' => Student::count(),
            'active_students' => Student::where('status', 'active')->count(),
            'pending_students' => Student::where('status', 'pending')->count(),
            'total_supervisors' => User::whereIn('role', ['supervisor', 'cosupervisor'])->count(),
            'total_tasks' => Task::count(),
            'pending_reports' => ProgressReport::where('status', 'submitted')->count(),
            'upcoming_meetings' => Meeting::where('scheduled_at', '>=', now())->where('status', 'scheduled')->count(),
        ];

        $recentStudents = Student::with(['user', 'programme', 'supervisor'])
            ->latest()->take(5)->get();

        $pendingApprovals = Student::with(['user', 'programme'])
            ->where('status', 'pending')->latest()->get();

        $tasksByStatus = Task::selectRaw('status, count(*) as count')
            ->groupBy('status')->pluck('count', 'status');

        return view('admin.dashboard', compact('stats', 'recentStudents', 'pendingApprovals', 'tasksByStatus'));
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
