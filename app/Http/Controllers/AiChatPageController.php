<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Services\Storage\StorageManager;
use Illuminate\Support\Facades\Auth;

class AiChatPageController extends Controller
{
    public function __construct(protected StorageManager $storageManager)
    {
    }

    public function index()
    {
        $user = Auth::user();
        $effectiveRole = session()->get('admin_role_switch', $user->role);

        $student = match ($effectiveRole) {
            'student' => $this->effectiveStudent(),
            'supervisor', 'cosupervisor' => session()->has('admin_view_as_student_id')
                ? Student::with(['user', 'programme'])->find(session()->get('admin_view_as_student_id'))
                : null,
            default => null,
        };

        [$files, $folders] = $this->getStudentContextResources($student);

        return view('ai.chat', [
            'student' => $student,
            'files' => $files,
            'folders' => $folders,
            'currentStorageDisk' => $this->storageManager->getCurrentDisk(),
            'effectiveRole' => $effectiveRole,
            'availableStudents' => $this->availableStudentsForRole($effectiveRole),
        ]);
    }

    public function studentContext(Student $student)
    {
        $this->authorize('view', $student);
        [$files, $folders] = $this->getStudentContextResources($student);

        return view('ai.chat', [
            'student' => $student,
            'files' => $files,
            'folders' => $folders,
            'currentStorageDisk' => $this->storageManager->getCurrentDisk(),
            'effectiveRole' => session()->get('admin_role_switch', Auth::user()->role),
            'availableStudents' => $this->availableStudentsForRole(session()->get('admin_role_switch', Auth::user()->role)),
        ]);
    }

    protected function getStudentContextResources(?Student $student): array
    {
        if (!$student) {
            return [collect(), collect()];
        }

        return [
            $student->files()->where('is_latest', true)->get(),
            $student->folders()->get(),
        ];
    }

    protected function availableStudentsForRole(string $effectiveRole)
    {
        $user = Auth::user();

        return match ($effectiveRole) {
            'student' => $user->student
                ? collect([$user->student->loadMissing(['user', 'programme'])])
                : collect(),
            'supervisor', 'cosupervisor' => $this->effectiveSupervisedStudentsQuery()
                ->with(['user', 'programme'])
                ->where('status', 'active')
                ->get(),
            'admin' => Student::with(['user', 'programme'])
                ->where('status', 'active')
                ->orderByDesc('updated_at')
                ->limit(12)
                ->get(),
            default => collect(),
        };
    }
}
