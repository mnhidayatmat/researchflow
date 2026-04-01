<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Grant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GrantController extends Controller
{
    public function index(Request $request): View
    {
        $query = Grant::with('checklistItems')
            ->where('user_id', Auth::id())
            ->latest('updated_at');

        if ($search = trim((string) $request->get('search'))) {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('proposal_title', 'like', "%{$search}%")
                    ->orWhere('grant_name', 'like', "%{$search}%")
                    ->orWhere('grant_type', 'like', "%{$search}%");
            });
        }

        if ($stage = $request->get('stage')) {
            $query->where('stage', $stage);
        }

        if ($scope = $request->get('scope')) {
            $query->where('scope', $scope);
        }

        $grants = $query->paginate(12);

        $statsQuery = Grant::where('user_id', Auth::id());
        $stats = [
            'total' => (clone $statsQuery)->count(),
            'submitted' => (clone $statsQuery)->whereNotNull('submission_date')->count(),
            'open' => (clone $statsQuery)->whereIn('stage', ['draft', 'waiting to open', 'preparing', 'submitted'])->count(),
            'total_amount' => (float) ((clone $statsQuery)->sum('amount') ?? 0),
        ];

        return view('supervisor.grants.index', compact('grants', 'stats'));
    }

    public function create(): View
    {
        $grant = new Grant([
            'scope' => 'national',
            'stage' => 'draft',
            'rejection_count' => 0,
        ]);

        $checklistItems = $this->defaultChecklistItems();
        $stages = Grant::STAGES;

        return view('supervisor.grants.create', compact('grant', 'checklistItems', 'stages'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateGrant($request);

        $grant = Grant::create([
            ...$validated,
            'user_id' => Auth::id(),
        ]);

        $this->syncChecklistItems($grant, $validated['checklist_items'] ?? []);

        return redirect()
            ->route('supervisor.grants.show', $grant)
            ->with('success', 'Grant record created.');
    }

    public function show(Grant $grant): View
    {
        $grant = $this->ownedGrant($grant);
        $grant->load('checklistItems', 'documents');

        return view('supervisor.grants.show', compact('grant'));
    }

    public function edit(Grant $grant): View
    {
        $grant = $this->ownedGrant($grant);
        $grant->load('checklistItems', 'documents');

        $checklistItems = $grant->checklistItems->map(fn ($item) => [
            'title' => $item->title,
            'is_completed' => $item->is_completed,
            'notes' => $item->notes,
        ])->all();

        if (empty($checklistItems)) {
            $checklistItems = $this->defaultChecklistItems();
        }

        $stages = Grant::STAGES;

        return view('supervisor.grants.edit', compact('grant', 'checklistItems', 'stages'));
    }

    public function update(Request $request, Grant $grant): RedirectResponse
    {
        $grant = $this->ownedGrant($grant);
        $validated = $this->validateGrant($request);

        $grant->update($validated);
        $this->syncChecklistItems($grant, $validated['checklist_items'] ?? []);

        return redirect()
            ->route('supervisor.grants.show', $grant)
            ->with('success', 'Grant record updated.');
    }

    public function destroy(Grant $grant): RedirectResponse
    {
        $grant = $this->ownedGrant($grant);
        $grant->delete();

        return redirect()
            ->route('supervisor.grants.index')
            ->with('success', 'Grant record deleted.');
    }

    protected function validateGrant(Request $request): array
    {
        return $request->validate([
            'proposal_title' => 'required|string|max:255',
            'grant_type' => 'required|string|max:100',
            'grant_name' => 'required|string|max:255',
            'duration' => 'nullable|string|max:100',
            'scope' => 'required|in:international,national',
            'amount' => 'nullable|numeric|min:0',
            'stage' => ['required', \Illuminate\Validation\Rule::in(array_keys(Grant::STAGES))],
            'submission_date' => 'nullable|date',
            'deadline' => 'nullable|date',
            'announcement_date' => 'nullable|date',
            'rejection_count' => 'nullable|integer|min:0|max:4',
            'notes' => 'nullable|string|max:5000',
            'checklist_items' => 'nullable|array',
            'checklist_items.*.title' => 'required|string|max:255',
            'checklist_items.*.is_completed' => 'nullable|boolean',
            'checklist_items.*.notes' => 'nullable|string|max:1000',
        ]);
    }

    protected function syncChecklistItems(Grant $grant, array $items): void
    {
        $grant->checklistItems()->delete();

        foreach (array_values($items) as $index => $item) {
            $title = trim((string) ($item['title'] ?? ''));
            if ($title === '') {
                continue;
            }

            $isCompleted = filter_var($item['is_completed'] ?? false, FILTER_VALIDATE_BOOL);

            $grant->checklistItems()->create([
                'title' => $title,
                'is_completed' => $isCompleted,
                'completed_at' => $isCompleted ? now() : null,
                'notes' => $item['notes'] ?? null,
                'sort_order' => $index,
            ]);
        }
    }

    protected function defaultChecklistItems(): array
    {
        return [
            ['title' => 'Call document reviewed', 'is_completed' => false, 'notes' => ''],
            ['title' => 'Eligibility confirmed', 'is_completed' => false, 'notes' => ''],
            ['title' => 'Proposal narrative drafted', 'is_completed' => false, 'notes' => ''],
            ['title' => 'Budget prepared', 'is_completed' => false, 'notes' => ''],
            ['title' => 'Supporting documents attached', 'is_completed' => false, 'notes' => ''],
            ['title' => 'Submission completed', 'is_completed' => false, 'notes' => ''],
        ];
    }

    protected function ownedGrant(Grant $grant): Grant
    {
        abort_unless($grant->user_id === Auth::id(), 403);

        return $grant;
    }
}
