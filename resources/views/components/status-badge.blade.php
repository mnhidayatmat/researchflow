@props([
    'status' => null,
    'size' => 'md',
])

@php
$statuses = [
    'backlog' => ['variant' => 'neutral', 'label' => 'Backlog', 'dot' => true],
    'planned' => ['variant' => 'info', 'label' => 'Planned', 'dot' => true],
    'in_progress' => ['variant' => 'primary', 'label' => 'In Progress', 'dot' => true],
    'waiting_review' => ['variant' => 'warning', 'label' => 'Waiting Review', 'dot' => true],
    'revision' => ['variant' => 'danger', 'label' => 'Revision', 'dot' => true],
    'completed' => ['variant' => 'success', 'label' => 'Completed', 'dot' => true],
    'draft' => ['variant' => 'neutral', 'label' => 'Draft', 'dot' => true],
    'submitted' => ['variant' => 'info', 'label' => 'Submitted', 'dot' => true],
    'reviewed' => ['variant' => 'success', 'label' => 'Reviewed', 'dot' => true],
    'revision_needed' => ['variant' => 'warning', 'label' => 'Revision Needed', 'dot' => true],
    'accepted' => ['variant' => 'success', 'label' => 'Accepted', 'dot' => true],
    'pending' => ['variant' => 'warning', 'label' => 'Pending', 'dot' => true],
    'active' => ['variant' => 'success', 'label' => 'Active', 'dot' => true],
    'inactive' => ['variant' => 'neutral', 'label' => 'Inactive', 'dot' => true],
    'on_hold' => ['variant' => 'warning', 'label' => 'On Hold', 'dot' => true],
    'withdrawn' => ['variant' => 'danger', 'label' => 'Withdrawn', 'dot' => true],
    'not_started' => ['variant' => 'neutral', 'label' => 'Not Started', 'dot' => true],
    'scheduled' => ['variant' => 'info', 'label' => 'Scheduled', 'dot' => true],
    'cancelled' => ['variant' => 'danger', 'label' => 'Cancelled', 'dot' => true],
    'verified' => ['variant' => 'success', 'label' => 'Verified', 'dot' => true],
];

$config = $statuses[$status] ?? ['variant' => 'default', 'label' => $status, 'dot' => false];
@endphp

<x-badge :variant="$config['variant']" :size="$size" :dot="$config['dot']">
    {{ $config['label'] }}
</x-badge>
