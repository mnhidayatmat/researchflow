<x-layouts.app title="Progress Reports">
    <x-slot:header>Progress Reports</x-slot:header>

    <div class="flex items-center justify-between mb-6">
        <p class="text-sm text-secondary">{{ $student->user->name }}'s reports</p>
        <x-button href="{{ route('reports.create', $student) }}" variant="accent" size="sm">+ New Report</x-button>
    </div>

    <div class="space-y-3">
        @forelse($reports as $report)
            <a href="{{ route('reports.show', [$student, $report]) }}" class="block">
                <x-card class="hover:shadow-sm transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-semibold">{{ $report->title }}</h3>
                            <p class="text-xs text-secondary mt-0.5">
                                {{ $report->type_label }} &middot;
                                {{ $report->created_at->format('d M Y') }}
                                @if($report->period_start && $report->period_end)
                                    &middot; {{ $report->period_start->format('d M') }} — {{ $report->period_end->format('d M') }}
                                @endif
                            </p>
                            @if($report->attachment_path)
                                <p class="text-[11px] text-secondary mt-1">Attachment: {{ $report->attachment_original_name }}</p>
                            @endif
                        </div>
                        <x-status-badge :status="$report->status" />
                    </div>
                </x-card>
            </a>
        @empty
            <x-card>
                <p class="text-sm text-secondary text-center py-4">No reports yet.</p>
            </x-card>
        @endforelse
    </div>

    <div class="mt-4">{{ $reports->links() }}</div>
</x-layouts.app>
