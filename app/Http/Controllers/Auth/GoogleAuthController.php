<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Programme;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $programmes = Programme::where('is_active', true)->orderBy('sort_order')->get();

        return view('auth.google-complete', compact('googleUser', 'programmes'));
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
            'university_name' => 'required|string|max:255',
            'phone'           => 'nullable|string',
        ];

        if ($role === 'student') {
            $rules['matric_number']      = 'nullable|string|unique:users,matric_number';
            $rules['programme_id']       = 'required|exists:programmes,id';
            $rules['supervisor_email']   = 'required|email';
            $rules['cosupervisor_email'] = 'required|email|different:supervisor_email';
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

        $userData = [
            'google_id'       => $googleUser['id'],
            'google_avatar'   => $googleUser['avatar'],
            'name'            => $validated['name'],
            'email'           => $googleUser['email'],
            'password'        => null,
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
            $user->student()->create([
                'programme_id'    => $validated['programme_id'],
                'supervisor_id'   => $supervisor->id,
                'cosupervisor_id' => $cosupervisor->id,
                'status'          => 'pending',
            ]);
        }

        $request->session()->forget('google_user');

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect($this->redirectPath($user));
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
