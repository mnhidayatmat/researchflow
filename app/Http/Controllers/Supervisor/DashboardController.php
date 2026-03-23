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

        $pendingReports = ProgressReport::with('student.user')
            ->whereIn('student_id', $studentIds)
            ->where('status', 'submitted')
            ->latest('submitted_at')
            ->take(5)->get();

        $tasksForReview = Task::with('student.user')
            ->whereIn('student_id', $studentIds)
            ->where('status', 'waiting_review')
            ->latest()->take(5)->get();

        return view('supervisor.dashboard', compact('stats', 'students', 'pendingReports', 'tasksForReview'));
    }
}
