<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\ProgressReport;
use App\Models\Task;

class DashboardController extends Controller
{
    public function index()
    {
        $studentIds = $this->effectiveSupervisedStudentsQuery()->pluck('id');

        $stats = [
            'total_students' => $studentIds->count(),
            'active_students' => $this->effectiveSupervisedStudentsQuery()->where('status', 'active')->count(),
            'pending_reviews' => ProgressReport::whereIn('student_id', $studentIds)->where('status', 'submitted')->count(),
            'tasks_waiting_review' => Task::whereIn('student_id', $studentIds)->where('status', 'waiting_review')->count(),
            'upcoming_meetings' => Meeting::whereIn('student_id', $studentIds)->where('scheduled_at', '>=', now())->where('status', 'scheduled')->count(),
        ];

        $students = $this->effectiveSupervisedStudentsQuery()
            ->with(['user', 'programme'])
            ->where('status', 'active')
            ->get();

        // Students pending this supervisor's approval
        $user = \Illuminate\Support\Facades\Auth::user();
        $pendingApprovals = \App\Models\Student::with('user')
            ->where('status', 'pending')
            ->where(function ($q) use ($user) {
                $q->where(function ($q2) use ($user) {
                    $q2->where('supervisor_id', $user->id)
                       ->whereNull('supervisor_approved_at');
                })->orWhere(function ($q2) use ($user) {
                    $q2->where('cosupervisor_id', $user->id)
                       ->whereNull('cosupervisor_approved_at');
                });
            })
            ->get();

        $stats['pending_approvals'] = $pendingApprovals->count();

        $pendingReports = ProgressReport::with('student.user')
            ->whereIn('student_id', $studentIds)
            ->where('status', 'submitted')
            ->latest('submitted_at')
            ->take(5)->get();

        $tasksForReview = Task::with('student.user')
            ->whereIn('student_id', $studentIds)
            ->where('status', 'waiting_review')
            ->latest()->take(5)->get();

        return view('supervisor.dashboard', compact('stats', 'students', 'pendingReports', 'tasksForReview', 'pendingApprovals'));
    }
}
