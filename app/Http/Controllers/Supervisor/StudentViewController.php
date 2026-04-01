<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Student;

class StudentViewController extends Controller
{
    public function index()
    {
        $students = $this->effectiveSupervisedStudentsQuery()
            ->with(['user', 'programme'])
            ->paginate(15);

        $user = \Illuminate\Support\Facades\Auth::user();
        $pendingApprovals = Student::with('user')
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

        return view('supervisor.students.index', compact('students', 'pendingApprovals'));
    }

    public function show(Student $student)
    {
        $this->authorize('view', $student);
        $student->load([
            'user', 'programme', 'supervisor', 'cosupervisor',
            'tasks' => fn($q) => $q->whereNull('parent_id')->orderBy('sort_order'),
            'progressReports' => fn($q) => $q->latest(),
            'meetings' => fn($q) => $q->latest('scheduled_at')->take(5),
            'publicationTracks' => fn($q) => $q->latest('submission_date')->latest(),
        ]);

        return view('supervisor.students.show', compact('student'));
    }
}
