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

        return view('supervisor.students.index', compact('students'));
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
