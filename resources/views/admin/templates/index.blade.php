<x-layouts.app title="Journey Templates">
    <x-slot:header>Journey Templates</x-slot:header>

    {{-- Page header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-base font-semibold text-primary">Journey Templates</h2>
            <p class="text-xs text-secondary mt-0.5">Define reusable research journey structures for programmes</p>
        </div>
        <x-button href="{{ route('admin.templates.create') }}" variant="primary" size="sm">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Template
        </x-button>
    </div>

    @if($templates->isEmpty())
        <x-card>
            <div class="py-12 text-center text-secondary">
                <svg class="w-10 h-10 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                <p class="text-sm font-medium">No templates yet</p>
                <p class="text-xs mt-1">Create your first journey template to assign structured research paths to students</p>
                <div class="mt-4">
                    <x-button href="{{ route('admin.templates.create') }}" variant="primary" size="sm">Create Template</x-button>
                </div>
            </div>
        </x-card>
    @else
        <div class="grid lg:grid-cols-2 gap-4">
            @foreach($templates as $template)
                <x-card :padding='false' class="hover:shadow-sm transition-shadow">
                    {{-- Template header --}}
                    <div class="px-5 py-4 border-b border-border">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <a href="{{ route('admin.templates.show', $template) }}" class="text-sm font-semibold text-primary hover:text-accent transition-colors">
                                    {{ $template->name }}
                                </a>
                                @if($template->programme)
                                    <div class="flex items-center gap-1.5 mt-1">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100 text-xs font-mono text-gray-600">{{ $template->programme->code }}</span>
                                        <span class="text-xs text-secondary">{{ $template->programme->name }}</span>
                                    </div>
                                @else
                                    <p class="text-xs text-secondary mt-1">No programme assigned</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-1 flex-shrink-0">
                                <a href="{{ route('admin.templates.show', $template) }}" class="p-1.5 text-secondary hover:text-primary hover:bg-gray-100 rounded transition-colors" title="View">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <a href="{{ route('admin.templates.edit', $template) }}" class="p-1.5 text-secondary hover:text-primary hover:bg-gray-100 rounded transition-colors" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form method="POST" action="{{ route('admin.templates.destroy', $template) }}" onsubmit="return confirm('Delete this template?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-secondary hover:text-red-600 hover:bg-red-50 rounded transition-colors" title="Delete">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- Stages summary --}}
                    <div class="px-5 py-3">
                        <p class="text-xs font-medium text-secondary uppercase tracking-wide mb-2">
                            {{ $template->stages->count() }} {{ Str::plural('Stage', $template->stages->count()) }}
                        </p>
                        @if($template->stages->isNotEmpty())
                            <div class="space-y-1.5">
                                @foreach($template->stages->sortBy('order')->take(4) as $stage)
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="w-4 h-4 rounded-full bg-surface border border-border text-[10px] font-medium text-secondary flex items-center justify-center flex-shrink-0">{{ $stage->order }}</span>
                                            <span class="text-xs text-primary">{{ $stage->name }}</span>
                                        </div>
                                        <span class="text-xs text-secondary">{{ $stage->milestones->count() }} milestones</span>
                                    </div>
                                @endforeach
                                @if($template->stages->count() > 4)
                                    <p class="text-xs text-secondary pl-6">+ {{ $template->stages->count() - 4 }} more stages</p>
                                @endif
                            </div>
                        @else
                            <p class="text-xs text-secondary">No stages defined</p>
                        @endif
                    </div>
                </x-card>
            @endforeach
        </div>
    @endif
</x-layouts.app>
