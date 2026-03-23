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
