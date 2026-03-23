<x-layouts.guest title="Sign In">
    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

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
