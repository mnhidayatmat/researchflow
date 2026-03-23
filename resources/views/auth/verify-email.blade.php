<x-layouts.guest title="Verify Email">
    @if(session('success'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="rounded-2xl border border-border bg-white p-6 shadow-sm">
        <h2 class="mb-2 text-lg font-semibold text-primary">Verify Your Email</h2>
        <p class="text-sm text-secondary">
            We sent a verification link to <span class="font-medium text-primary">{{ $email ?: 'your email address' }}</span>.
            Open the email and click the verification link before signing in.
        </p>

        <form method="POST" action="{{ route('verification.send') }}" class="mt-5 space-y-4">
            @csrf
            <x-input name="email" type="email" label="Email" required :value="$email" placeholder="you@university.edu" />
            <x-button type="submit" variant="accent" class="w-full">Resend Verification Email</x-button>
        </form>

        <p class="mt-4 text-center text-sm text-secondary">
            Already verified? <a href="{{ route('login') }}" class="font-medium text-accent hover:underline">Sign in</a>
        </p>
    </div>
</x-layouts.guest>
