<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            if (!$user->hasVerifiedEmail()) {
                Auth::logout();
                return back()->withErrors(['email' => 'Please verify your email before signing in.'])->onlyInput('email');
            }

            if ($user->status === 'pending') {
                $user->forceFill(['status' => 'active'])->save();
            }

            if ($user->status === 'inactive') {
                Auth::logout();
                return back()->withErrors(['email' => 'Your account is inactive. Please contact support.']);
            }

            return redirect()->to($this->redirectDestination($request, $user));
        }

        return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    private function redirectPath($user): string
    {
        return match ($user->role) {
            'admin' => '/admin/dashboard',
            'supervisor', 'cosupervisor' => '/supervisor/dashboard',
            'student' => '/student/dashboard',
        };
    }

    private function redirectDestination(Request $request, $user): string
    {
        $fallback = $this->redirectPath($user);
        $intended = $request->session()->pull('url.intended');

        if (!$intended) {
            return $fallback;
        }

        $path = parse_url($intended, PHP_URL_PATH) ?: '';

        if ($path === '' || $path === '/') {
            return $fallback;
        }

        return match ($user->role) {
            'admin' => $intended,
            'supervisor', 'cosupervisor' => str_starts_with($path, '/supervisor') ? $intended : $fallback,
            'student' => str_starts_with($path, '/student') ? $intended : $fallback,
            default => $fallback,
        };
    }
}
