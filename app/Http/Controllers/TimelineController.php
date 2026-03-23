<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TimelineController extends Controller
{
    /**
     * Display timeline overview with student selection.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $effectiveRole = $this->getEffectiveRole($user);
        $students = $this->getAccessibleStudents($user);

        // Get selected student from query param or first available
        $selectedStudentId = $request->query('student');
        if ($selectedStudentId) {
            $selectedStudent = $students->firstWhere('id', $selectedStudentId);
        } else {
            $selectedStudent = $students->first();
        }

        if ($effectiveRole === 'student' && $selectedStudent) {
            return redirect()->route('timeline.show', $selectedStudent->id);
        }

        if (!$selectedStudent) {
            return view('timeline.index', compact('students', 'selectedStudent'));
        }

        return redirect()->route('timeline.show', $selectedStudent->id);
    }

    /**
     * Display timeline for a specific student.
     */
    public function show(Student $student)
    {
        $this->authorize('view', $student);

        $user = Auth::user();
        $students = $this->getAccessibleStudents($user);

        // Verify access to this student
        if (!$students->contains('id', $student->id)) {
            abort(403, 'You do not have access to this student\'s timeline.');
        }

        $student->load(['user', 'programme', 'tasks' => function ($query) {
            $query->orderBy('due_date');
        }]);

        return view('timeline.show', compact('student', 'students'));
    }

    /**
     * Get students accessible to the current user.
     */
    protected function getAccessibleStudents($user)
    {
        $effectiveRole = $this->getEffectiveRole($user);

        return match ($effectiveRole) {
            'admin' => Student::with(['user', 'programme'])
                ->where('status', 'active')
                ->orderBy('created_at', 'desc')
                ->get(),

            'supervisor', 'cosupervisor' => Student::with(['user', 'programme'])
                ->where(function ($query) use ($user) {
                    $query->where('supervisor_id', $user->id)
                        ->orWhere('cosupervisor_id', $user->id);
                })
                ->where('status', 'active')
                ->orderBy('created_at', 'desc')
                ->get(),

            'student' => $this->getStudentScopedQuery($user)->get(),

            default => collect(),
        };
    }

    protected function getEffectiveRole($user): string
    {
        if ($user->role === 'admin') {
            return session()->get('admin_role_switch', $user->role);
        }

        return $user->role;
    }

    protected function getStudentScopedQuery($user)
    {
        $studentId = $user->role === 'admin' && session()->has('admin_view_as_student_id')
            ? session()->get('admin_view_as_student_id')
            : $user->student?->id;

        return Student::with(['user', 'programme'])
            ->when($studentId, fn ($query) => $query->where('id', $studentId), fn ($query) => $query->whereRaw('1 = 0'));
    }

    /**
     * Get students list for AJAX requests.
     */
    public function students(Request $request)
    {
        $user = Auth::user();
        $students = $this->getAccessibleStudents($user);

        return response()->json([
            'students' => $students->map(fn ($student) => [
                'id' => $student->id,
                'name' => $student->user->name,
                'email' => $student->user->email,
                'programme' => $student->programme?->name,
                'status' => $student->status,
                'avatar' => $student->user->avatar,
            ])
        ]);
    }
}
