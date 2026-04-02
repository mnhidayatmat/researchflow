<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use App\Services\BrevoTransactionalEmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors(['email' => 'Google authentication failed. Please try again.']);
        }

        // Find existing user by google_id or email
        $user = User::where('google_id', $googleUser->getId())->first()
            ?? User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            // Link google_id if not already set
            if (!$user->google_id) {
                $user->forceFill(['google_id' => $googleUser->getId()])->save();
            }

            if ($user->status === 'inactive') {
                return redirect()->route('login')->withErrors(['email' => 'Your account is inactive. Please contact support.']);
            }

            // Mark email as verified since Google verified it
            if (!$user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();
            }

            if ($user->status === 'pending') {
                $user->forceFill(['status' => 'active'])->save();
            }

            Auth::login($user, true);
            $request->session()->regenerate();

            return redirect($this->redirectPath($user));
        }

        // New user — store Google data in session and prompt for role/profile completion
        $request->session()->put('google_user', [
            'id'     => $googleUser->getId(),
            'name'   => $googleUser->getName(),
            'email'  => $googleUser->getEmail(),
            'avatar' => $googleUser->getAvatar(),
        ]);

        return redirect()->route('auth.google.complete');
    }

    public function showComplete(Request $request)
    {
        if (!$request->session()->has('google_user')) {
            return redirect()->route('login');
        }

        $googleUser = $request->session()->get('google_user');

        return view('auth.google-complete', compact('googleUser'));
    }

    public function complete(Request $request)
    {
        if (!$request->session()->has('google_user')) {
            return redirect()->route('login');
        }

        $googleUser = $request->session()->get('google_user');
        $role = $request->input('role', 'student');

        $rules = [
            'role'            => 'required|in:student,supervisor',
            'name'            => 'required|string|max:255',
            'university_name' => 'nullable|string|max:255',
            'phone'           => ['nullable', 'regex:/^\+?[0-9\s\-\(\)]{7,20}$/'],
        ];

        if ($role === 'student') {
            $rules['matric_number']           = 'nullable|string|unique:users,matric_number';
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
            $supervisor = User::whereIn('role', ['supervisor', 'cosupervisor'])
                ->where('email', $validated['supervisor_email'])
                ->first();

            if (!empty($validated['cosupervisor_email'])) {
                $cosupervisor = User::whereIn('role', ['supervisor', 'cosupervisor'])
                    ->where('email', $validated['cosupervisor_email'])
                    ->first();
            }

            if ($supervisor && $cosupervisor && $supervisor->is($cosupervisor)) {
                return back()->withErrors([
                    'cosupervisor_email' => 'Supervisor and co-supervisor must be different users.',
                ])->withInput();
            }
        }

        $userData = [
            'google_id'       => $googleUser['id'],
            'google_avatar'   => $googleUser['avatar'],
            'name'            => $validated['name'],
            'email'           => $googleUser['email'],
            'password'        => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(32)),
            'role'            => $role === 'supervisor' ? 'supervisor' : 'student',
            'phone'           => $validated['phone'] ?? null,
            'university_name' => $validated['university_name'],
            'status'          => 'active',
            'email_verified_at' => now(),
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

            $brevo = app(BrevoTransactionalEmailService::class);
            $approvalRecipients = [];
            if ($supervisor) {
                $approvalRecipients[] = ['user' => $supervisor, 'role' => 'supervisor'];
            }
            if ($cosupervisor) {
                $approvalRecipients[] = ['user' => $cosupervisor, 'role' => 'cosupervisor'];
            }
            foreach ($approvalRecipients as $item) {
                $approveUrl = URL::temporarySignedRoute('supervisor.student.approve', now()->addDays(7), ['student' => $student->id, 'role' => $item['role']]);
                $denyUrl    = URL::temporarySignedRoute('supervisor.student.deny', now()->addDays(7), ['student' => $student->id, 'role' => $item['role']]);
                $roleLabel  = $item['role'] === 'supervisor' ? 'Supervisor' : 'Co-Supervisor';
                try {
                    $brevo->sendSupervisorApprovalRequest($item['user']->email, $item['user']->name, $student->user->name, $student->programme_name, $roleLabel, $approveUrl, $denyUrl);
                } catch (\Throwable) {}
            }
        }

        if ($role === 'supervisor') {
            $this->linkPendingStudents($user);
        }

        $request->session()->forget('google_user');

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect($this->redirectPath($user));
    }

    private function linkPendingStudents(User $supervisor): void
    {
        $brevo = app(BrevoTransactionalEmailService::class);

        // Link as primary supervisor
        $students = Student::whereNull('supervisor_id')
            ->where('supervisor_email', $supervisor->email)
            ->get();

        foreach ($students as $student) {
            $student->update(['supervisor_id' => $supervisor->id]);
            $approveUrl = URL::temporarySignedRoute('supervisor.student.approve', now()->addDays(7), ['student' => $student->id, 'role' => 'supervisor']);
            $denyUrl    = URL::temporarySignedRoute('supervisor.student.deny', now()->addDays(7), ['student' => $student->id, 'role' => 'supervisor']);
            try {
                $brevo->sendSupervisorApprovalRequest($supervisor->email, $supervisor->name, $student->user->name, $student->programme_name, 'Supervisor', $approveUrl, $denyUrl);
            } catch (\Throwable) {}
        }

        // Link as co-supervisor
        $coStudents = Student::whereNull('cosupervisor_id')
            ->where('cosupervisor_email', $supervisor->email)
            ->get();

        foreach ($coStudents as $student) {
            $student->update(['cosupervisor_id' => $supervisor->id]);
            $approveUrl = URL::temporarySignedRoute('supervisor.student.approve', now()->addDays(7), ['student' => $student->id, 'role' => 'cosupervisor']);
            $denyUrl    = URL::temporarySignedRoute('supervisor.student.deny', now()->addDays(7), ['student' => $student->id, 'role' => 'cosupervisor']);
            try {
                $brevo->sendSupervisorApprovalRequest($supervisor->email, $supervisor->name, $student->user->name, $student->programme_name, 'Co-Supervisor', $approveUrl, $denyUrl);
            } catch (\Throwable) {}
        }
    }

    private function redirectPath(User $user): string
    {
        return match ($user->role) {
            'admin'                     => '/admin/dashboard',
            'supervisor', 'cosupervisor' => '/supervisor/dashboard',
            'student'                   => '/student/dashboard',
        };
    }
}
