<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\View\View;

class ReportsController extends Controller
{
    public function index(): View
    {
        $student = auth()->user()->student;

        if (!$student) {
            abort(403, 'Student profile not found.');
        }

        $reports = $student->progressReports()
            ->withCount('revisions')
            ->latest()
            ->paginate(10);

        return view('student.reports.index', compact('student', 'reports'));
    }
}
