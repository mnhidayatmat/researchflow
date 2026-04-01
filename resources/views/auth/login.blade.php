<x-layouts.guest title="Sign In">
    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-8 text-center">
        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-accent shadow-[0_18px_45px_rgba(217,119,6,0.28)]">
            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
        </div>
        <h1 class="text-2xl font-semibold tracking-tight text-primary">ResearchFlow</h1>
        <p class="mt-2 text-sm text-secondary">Academic supervision workspace for students, supervisors, and administrators.</p>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
        <h2 class="text-sm font-semibold text-gray-900 mb-4">Sign in to your account</h2>

        {{-- Google Sign In --}}
        <a href="{{ route('auth.google') }}"
           class="flex items-center justify-center gap-3 w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm mb-4">
            <svg class="w-5 h-5" viewBox="0 0 24 24">
                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
            </svg>
            Continue with Google
        </a>

        <div class="relative mb-4">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-200"></div>
            </div>
            <div class="relative flex justify-center text-xs">
                <span class="bg-white px-2 text-gray-400">or sign in with email</span>
            </div>
        </div>

        <form method="POST" action="/login" class="space-y-4">
            @csrf
            <x-input name="email" type="email" label="Email" required placeholder="you@university.edu" />
            <x-input name="password" type="password" label="Password" required />

            <label class="flex items-center gap-2">
                <input type="checkbox" name="remember" class="rounded border-gray-300 text-accent focus:ring-accent">
                <span class="text-sm text-gray-600">Remember me</span>
            </label>

            @if($errors->any())
                <p class="text-sm text-red-500">{{ $errors->first() }}</p>
            @endif

            <x-button type="submit" variant="accent" class="w-full">Sign in</x-button>
        </form>
    </div>

    <p class="text-center text-sm text-gray-500 mt-4">
        Don't have an account? <a href="/register" class="text-accent hover:underline">Register</a>
    </p>
</x-layouts.guest>
