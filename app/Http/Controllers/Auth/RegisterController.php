<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Models\Student;
use App\Models\User;
use App\Services\BrevoTransactionalEmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;

class RegisterController extends Controller
{
    public function __construct(
        private EmailVerificationController $emailVerificationController,
        private BrevoTransactionalEmailService $brevo,
    ) {}

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $role = $request->input('role', 'student');

        $rules = [
            'role'            => 'required|in:student,supervisor',
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|unique:users',
            'password'        => 'required|min:8|confirmed',
            'phone'           => ['nullable', 'regex:/^\+?[0-9\s\-\(\)]{7,20}$/'],
            'university_name' => 'nullable|string|max:255',
        ];

        if ($role === 'student') {
            $rules['matric_number']           = 'nullable|string|max:255';
            $rules['student_category']        = 'nullable|in:fyp,master,phd,other';
            $rules['student_category_other']  = 'required_if:student_category,other|nullable|string|max:255';
            $rules['programme_name']          = 'required|string|max:255';
            $rules['supervisor_email']        = 'required|email';
            $rules['cosupervisor_email']      = 'nullable|email|different:supervisor_email';
        } else {
            $rules['title']      = 'required|string|max:50';
            $rules['staff_id']   = 'required|string|unique:users,staff_id';
            $rules['department'] = 'required|string|max:255';
            $rules['faculty']    = 'required|string|max:255';
        }

        $validated = $request->validate($rules);
        $supervisor   = null;
        $cosupervisor = null;

        if ($role === 'student') {
            // Look up supervisor/cosupervisor but don't block registration if not found
            $supervisor = User::whereIn('role', ['supervisor', 'cosupervisor'])
                ->where('email', $validated['supervisor_email'])
                ->first();

            if (!empty($validated['cosupervisor_email'])) {
                $cosupervisor = User::whereIn('role', ['supervisor', 'cosupervisor'])
                    ->where('email', $validated['cosupervisor_email'])
                    ->first();
            }

            // Only validate that SV and co-SV are different if both exist
            if ($supervisor && $cosupervisor && $supervisor->is($cosupervisor)) {
                return back()->withErrors([
                    'cosupervisor_email' => 'Supervisor and co-supervisor must be different users.',
                ])->withInput();
            }
        }

        $userData = [
            'name'            => $validated['name'],
            'email'           => $validated['email'],
            'password'        => Hash::make($validated['password']),
            'role'            => $role === 'supervisor' ? 'supervisor' : 'student',
            'phone'           => $validated['phone'] ?? null,
            'university_name' => $validated['university_name'],
            'status'          => 'active',
        ];

        if ($role === 'student') {
            $userData['matric_number'] = $validated['matric_number'] ?? null;
        } else {
            $userData['title']      = $validated['title'];
            $userData['staff_id']   = $validated['staff_id'];
            $userData['department'] = $validated['department'];
            $userData['faculty']    = $validated['faculty'];
        }

        $user = User::create($userData);

        if ($role === 'student') {
            $category = ($validated['student_category'] ?? null) === 'other'
                ? ($validated['student_category_other'] ?? null)
                : ($validated['student_category'] ?? null);

            $student = $user->student()->create([
                'programme_name'    => $validated['programme_name'],
                'student_category'  => $category,
                'supervisor_id'     => $supervisor?->id,
                'cosupervisor_id'   => $cosupervisor?->id,
                'supervisor_email'  => $validated['supervisor_email'],
                'cosupervisor_email' => $validated['cosupervisor_email'] ?? null,
                'status'            => 'pending',
            ]);

            // Send approval request emails only if supervisor/cosupervisor accounts exist
            if ($supervisor) {
                $this->sendApprovalRequest($student, $supervisor, 'supervisor');
            }
            if ($cosupervisor) {
                $this->sendApprovalRequest($student, $cosupervisor, 'cosupervisor');
            }
        }

        if ($role === 'supervisor') {
            $this->linkPendingStudents($user);
        }

        $this->emailVerificationController->sendVerificationEmail($user);

        return redirect()
            ->route('verification.notice', ['email' => $user->email])
            ->with('success', 'Registration submitted. Please verify your email to continue. Your supervisor has also been notified to approve your request.');
    }

    private function sendApprovalRequest($student, User $supervisor, string $role): void
    {
        $approveUrl = URL::temporarySignedRoute(
            'supervisor.student.approve',
            now()->addDays(7),
            ['student' => $student->id, 'role' => $role]
        );

        $denyUrl = URL::temporarySignedRoute(
            'supervisor.student.deny',
            now()->addDays(7),
            ['student' => $student->id, 'role' => $role]
        );

        $roleLabel = $role === 'supervisor' ? 'Supervisor' : 'Co-Supervisor';

        try {
            $this->brevo->sendSupervisorApprovalRequest(
                supervisorEmail: $supervisor->email,
                supervisorName:  $supervisor->name,
                studentName:     $student->user->name,
                programmeName:   $student->programme_name,
                roleLabel:       $roleLabel,
                approveUrl:      $approveUrl,
                denyUrl:         $denyUrl,
            );
        } catch (\Throwable) {
            // Email failure should not block registration
        }
    }

    private function linkPendingStudents(User $supervisor): void
    {
        // Link as primary supervisor
        $students = Student::whereNull('supervisor_id')
            ->where('supervisor_email', $supervisor->email)
            ->get();

        foreach ($students as $student) {
            $student->update(['supervisor_id' => $supervisor->id]);
            $this->sendApprovalRequest($student, $supervisor, 'supervisor');
        }

        // Link as co-supervisor
        $coStudents = Student::whereNull('cosupervisor_id')
            ->where('cosupervisor_email', $supervisor->email)
            ->get();

        foreach ($coStudents as $student) {
            $student->update(['cosupervisor_id' => $supervisor->id]);
            $this->sendApprovalRequest($student, $supervisor, 'cosupervisor');
        }
    }
}
