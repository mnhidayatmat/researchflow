<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentApprovalController extends Controller
{
    public function approve(Request $request, Student $student)
    {
        if (!$request->hasValidSignature()) {
            abort(403, 'This approval link is invalid or has expired.');
        }

        $role = $request->query('role', 'supervisor');

        if ($role === 'supervisor' && $student->supervisor_approved_at === null) {
            $student->update(['supervisor_approved_at' => now()]);
        } elseif ($role === 'cosupervisor' && $student->cosupervisor_approved_at === null) {
            $student->update(['cosupervisor_approved_at' => now()]);
        }

        // Activate student once supervisor approves
        if ($student->fresh()->supervisor_approved_at !== null && $student->status === 'pending') {
            $student->update(['status' => 'active']);
        }

        return view('supervisor.approval-result', [
            'approved'    => true,
            'studentName' => $student->user->name,
        ]);
    }

    public function deny(Request $request, Student $student)
    {
        if (!$request->hasValidSignature()) {
            abort(403, 'This denial link is invalid or has expired.');
        }

        $role = $request->query('role', 'supervisor');

        // Remove the supervisor/cosupervisor link
        if ($role === 'supervisor') {
            $student->update(['supervisor_id' => null]);
        } else {
            $student->update(['cosupervisor_id' => null]);
        }

        return view('supervisor.approval-result', [
            'approved'    => false,
            'studentName' => $student->user->name,
        ]);
    }
}
