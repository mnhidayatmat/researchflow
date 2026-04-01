<x-layouts.guest :title="$approved ? 'Request Approved' : 'Request Declined'">
    <div class="mb-8 text-center">
        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl {{ $approved ? 'bg-success' : 'bg-tertiary' }} shadow-lg">
            @if($approved)
                <svg class="h-7 w-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            @else
                <svg class="h-7 w-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            @endif
        </div>
        <h1 class="text-xl font-semibold tracking-tight text-primary">
            {{ $approved ? 'Request Approved' : 'Request Declined' }}
        </h1>
        <p class="mt-2 text-sm text-secondary">
            @if($approved)
                You have approved <strong>{{ $studentName }}</strong> as your student.
                They will now be able to access their research workspace.
            @else
                You have declined the supervision request from <strong>{{ $studentName }}</strong>.
            @endif
        </p>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm text-center">
        <p class="text-sm text-secondary mb-4">
            You can now close this page or sign in to manage your students.
        </p>
        <a href="{{ route('login') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold bg-accent text-white hover:bg-amber-700 transition-all">
            Sign in to ResearchFlow
        </a>
    </div>
</x-layouts.guest>
