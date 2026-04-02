<x-layouts.app title="AI Assistant - Upgrade Required">

<div class="flex items-center justify-center min-h-[60vh]">
    <div class="max-w-md w-full text-center px-6">
        <div class="w-20 h-20 rounded-3xl bg-gradient-to-br from-accent/15 to-accent/5 flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z"/>
            </svg>
        </div>

        <h2 class="text-xl font-semibold text-primary dark:text-dark-primary mb-2">AI Assistant is a Pro Feature</h2>
        <p class="text-sm text-secondary dark:text-dark-secondary mb-8 max-w-sm mx-auto">
            Get access to the AI Assistant to help with your research, writing, analysis, and more. Upgrade to Pro to unlock this feature.
        </p>

        <div class="bg-card dark:bg-dark-card rounded-2xl border border-border dark:border-dark-border p-6 mb-6 text-left">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-accent/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-primary dark:text-dark-primary">Pro Plan</p>
                    <p class="text-xs text-secondary dark:text-dark-secondary">Full AI-powered research tools</p>
                </div>
            </div>
            <ul class="space-y-2.5">
                @foreach([
                    'AI research assistant & chat',
                    'Report summarization & feedback',
                    'Task suggestions & deadline analysis',
                    'Document comparison & analysis',
                    'Context-aware research guidance',
                ] as $feature)
                <li class="flex items-center gap-2 text-sm text-primary dark:text-dark-primary">
                    <svg class="w-4 h-4 text-success shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ $feature }}
                </li>
                @endforeach
            </ul>
        </div>

        <p class="text-xs text-secondary dark:text-dark-secondary">
            Contact your administrator to upgrade your account to Pro.
        </p>

        <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('student.dashboard') }}"
           class="inline-flex items-center gap-1.5 mt-4 text-sm text-accent hover:text-amber-700 font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Go back
        </a>
    </div>
</div>

</x-layouts.app>
