<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Programme;
use App\Models\Student;
use App\Models\User;
use App\Services\FileService;
use App\Services\JourneyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StudentManagementController extends Controller
{
    public function index(Request $request)
    {
        $students = Student::with(['user', 'programme', 'supervisor'])
            ->when($request->search, fn($q, $s) => $q->whereHas('user', fn($q2) => $q2->where('name', 'like', "%{$s}%")))
            ->when($request->programme, fn($q, $p) => $q->where('programme_id', $p))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(15);

        $programmes = Programme::where('is_active', true)->get();

        return view('admin.students.index', compact('students', 'programmes'));
    }

    public function create()
    {
        $programmes = Programme::where('is_active', true)->get();
        $supervisors = User::whereIn('role', ['supervisor', 'cosupervisor'])->where('status', 'active')->get();
        return view('admin.students.create', compact('programmes', 'supervisors'));
    }

    public function store(Request $request, FileService $fileService, JourneyService $journeyService)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'matric_number' => 'nullable|string|unique:users',
            'programme_id' => 'required|exists:programmes,id',
            'supervisor_id' => 'nullable|exists:users,id',
            'cosupervisor_id' => 'nullable|exists:users,id',
            'research_title' => 'nullable|string|max:500',
            'intake' => 'nullable|string',
            'start_date' => 'nullable|date',
            'expected_completion' => 'nullable|date|after:start_date',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make('password123'),
            'role' => 'student',
            'matric_number' => $validated['matric_number'] ?? null,
            'status' => 'active',
        ]);

        $student = $user->student()->create([
            'programme_id' => $validated['programme_id'],
            'supervisor_id' => $validated['supervisor_id'] ?? null,
            'cosupervisor_id' => $validated['cosupervisor_id'] ?? null,
            'research_title' => $validated['research_title'] ?? null,
            'intake' => $validated['intake'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'expected_completion' => $validated['expected_completion'] ?? null,
            'status' => 'active',
        ]);

        $fileService->createDefaultFolders($student);

        // Auto-assign journey template if programme has a default
        $programme = Programme::find($validated['programme_id']);
        $template = $programme->journeyTemplates()->where('is_default', true)->first();
        if ($template) {
            $journeyService->instantiateFromTemplate($student, $template);
        }

        return redirect()->route('admin.students.index')->with('success', 'Student created successfully.');
    }

    public function show(Student $student)
    {
        $student->load(['user', 'programme', 'supervisor', 'cosupervisor', 'tasks', 'progressReports', 'publicationTracks']);
        return view('admin.students.show', compact('student'));
    }

    public function edit(Student $student)
    {
        $student->load('user');
        $programmes = Programme::where('is_active', true)->get();
        $supervisors = User::whereIn('role', ['supervisor', 'cosupervisor'])->where('status', 'active')->get();
        return view('admin.students.edit', compact('student', 'programmes', 'supervisors'));
    }

    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $student->user_id,
            'programme_id' => 'required|exists:programmes,id',
            'supervisor_id' => 'nullable|exists:users,id',
            'cosupervisor_id' => 'nullable|exists:users,id',
            'research_title' => 'nullable|string|max:500',
            'research_abstract' => 'nullable|string',
            'intake' => 'nullable|string',
            'start_date' => 'nullable|date',
            'expected_completion' => 'nullable|date',
            'status' => 'required|in:pending,active,on_hold,completed,withdrawn',
        ]);

        $student->user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'status' => $validated['status'] === 'active' ? 'active' : 'inactive',
        ]);

        $student->update(collect($validated)->except(['name', 'email'])->toArray());

        return redirect()->route('admin.students.show', $student)->with('success', 'Student updated.');
    }

    public function approve(Student $student)
    {
        $student->update(['status' => 'active']);
        $student->user->update(['status' => 'active']);

        app(FileService::class)->createDefaultFolders($student);

        return back()->with('success', 'Student approved.');
    }

    public function destroy(Student $student)
    {
        $student->user->delete();
        $student->delete();
        return redirect()->route('admin.students.index')->with('success', 'Student removed.');
    }
}
