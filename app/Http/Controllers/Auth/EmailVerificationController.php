<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\BrevoTransactionalEmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class EmailVerificationController extends Controller
{
    public function __construct(private BrevoTransactionalEmailService $brevo)
    {
    }

    public function notice(Request $request)
    {
        return view('auth.verify-email', [
            'email' => $request->query('email'),
        ]);
    }

    public function verify(Request $request, int $id, string $hash)
    {
        abort_unless($request->hasValidSignature(), 403, 'Invalid or expired verification link.');

        $user = User::findOrFail($id);

        abort_unless(hash_equals((string) $hash, sha1($user->getEmailForVerification())), 403, 'Invalid verification hash.');

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        if ($user->status === 'pending') {
            $user->forceFill(['status' => 'active'])->save();
        }

        return redirect()->route('login')->with('success', 'Email verified successfully. You can now sign in.');
    }

    public function resend(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'We could not find an account for that email address.',
            ]);
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('login')->with('success', 'That email address is already verified. You can sign in.');
        }

        $this->sendVerificationEmail($user);

        return back()->with('success', 'A new verification email has been sent.');
    }

    public function sendVerificationEmail(User $user): void
    {
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addHours(24),
            [
                'id' => $user->id,
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        $this->brevo->sendEmailVerification($user->email, $user->name, $verificationUrl);
    }
}
