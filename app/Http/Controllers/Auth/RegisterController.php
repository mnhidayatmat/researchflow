<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Models\Programme;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function __construct(private EmailVerificationController $emailVerificationController)
    {
    }

    public function showRegister()
    {
        $programmes = Programme::where('is_active', true)->orderBy('sort_order')->get();
        return view('auth.register', compact('programmes'));
    }

    public function register(Request $request)
    {
        $role = $request->input('role', 'student');

        // Base validation rules
        $rules = [
            'role' => 'required|in:student,supervisor',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'phone' => 'nullable|string',
            'university_name' => 'required|string|max:255',
        ];

        // Role-specific validation
        if ($role === 'student') {
            $rules['matric_number'] = 'nullable|string|unique:users,matric_number';
            $rules['programme_id'] = 'required|exists:programmes,id';
            $rules['supervisor_email'] = 'required|email';
            $rules['cosupervisor_email'] = 'required|email|different:supervisor_email';
        } else {
            $rules['staff_id'] = 'required|string|unique:users,staff_id';
            $rules['department'] = 'required|string|max:255';
            $rules['faculty'] = 'required|string|max:255';
        }

        $validated = $request->validate($rules);
        $supervisor = null;
        $cosupervisor = null;

        if ($role === 'student') {
            $supervisor = User::whereIn('role', ['supervisor', 'cosupervisor'])
                ->where('email', $validated['supervisor_email'])
                ->first();

            $cosupervisor = User::whereIn('role', ['supervisor', 'cosupervisor'])
                ->where('email', $validated['cosupervisor_email'])
                ->first();

            $errors = [];

            if (!$supervisor) {
                $errors['supervisor_email'] = 'We could not find a supervisor account with that email address.';
            }

            if (!$cosupervisor) {
                $errors['cosupervisor_email'] = 'We could not find a co-supervisor account with that email address.';
            }

            if ($supervisor && $cosupervisor && $supervisor->is($cosupervisor)) {
                $errors['cosupervisor_email'] = 'Supervisor and co-supervisor must be different users.';
            }

            if ($errors) {
                return back()->withErrors($errors)->withInput();
            }
        }

        // Create user
        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $role === 'supervisor' ? 'supervisor' : 'student',
            'phone' => $validated['phone'] ?? null,
            'university_name' => $validated['university_name'],
            'status' => 'active',
        ];

        if ($role === 'student') {
            $userData['matric_number'] = $validated['matric_number'] ?? null;
        } else {
            $userData['staff_id'] = $validated['staff_id'];
            $userData['department'] = $validated['department'];
            $userData['faculty'] = $validated['faculty'];
        }

        $user = User::create($userData);

        // Create student profile if registering as student
        if ($role === 'student') {
            $user->student()->create([
                'programme_id' => $validated['programme_id'],
                'supervisor_id' => $supervisor->id,
                'cosupervisor_id' => $cosupervisor->id,
                'status' => 'pending',
            ]);
        }

        $this->emailVerificationController->sendVerificationEmail($user);

        return redirect()
            ->route('verification.notice', ['email' => $user->email])
            ->with('success', 'Registration submitted. Please verify your email to continue.');
    }
}
