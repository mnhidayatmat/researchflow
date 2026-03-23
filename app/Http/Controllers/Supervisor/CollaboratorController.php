<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Collaborator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CollaboratorController extends Controller
{
    public function index(Request $request): View
    {
        $query = Collaborator::query()
            ->where('user_id', Auth::id())
            ->latest('updated_at');

        if ($search = trim((string) $request->get('search'))) {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('institution_name', 'like', "%{$search}%")
                    ->orWhere('department', 'like', "%{$search}%")
                    ->orWhere('faculty', 'like', "%{$search}%")
                    ->orWhere('expertise_area', 'like', "%{$search}%")
                    ->orWhere('research_field', 'like', "%{$search}%")
                    ->orWhere('country', 'like', "%{$search}%");
            });
        }

        if ($category = $request->get('category')) {
            $query->where('category', $category);
        }

        if ($country = trim((string) $request->get('country'))) {
            $query->where('country', 'like', "%{$country}%");
        }

        if ($request->boolean('reviewer_only')) {
            $query->where('suggested_reviewer', true);
        }

        $collaborators = $query->paginate(12);

        $statsQuery = Collaborator::where('user_id', Auth::id());
        $stats = [
            'total' => (clone $statsQuery)->count(),
            'academic' => (clone $statsQuery)->where('category', 'academic')->count(),
            'industry' => (clone $statsQuery)->where('category', 'industry')->count(),
            'reviewers' => (clone $statsQuery)->where('suggested_reviewer', true)->count(),
        ];

        return view('supervisor.collaborators.index', compact('collaborators', 'stats'));
    }

    public function create(): View
    {
        $collaborator = new Collaborator([
            'category' => 'academic',
            'suitable_for_grant' => true,
            'suitable_for_publication' => true,
            'suggested_reviewer' => false,
        ]);

        return view('supervisor.collaborators.create', [
            'collaborator' => $collaborator,
            'categoryOptions' => $this->categoryOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateCollaborator($request);

        $collaborator = Collaborator::create([
            ...$validated,
            'user_id' => Auth::id(),
        ]);

        return redirect()
            ->route('supervisor.collaborators.show', $collaborator)
            ->with('success', 'Collaborator record created.');
    }

    public function show(Collaborator $collaborator): View
    {
        $collaborator = $this->ownedCollaborator($collaborator);

        return view('supervisor.collaborators.show', compact('collaborator'));
    }

    public function edit(Collaborator $collaborator): View
    {
        $collaborator = $this->ownedCollaborator($collaborator);

        return view('supervisor.collaborators.edit', [
            'collaborator' => $collaborator,
            'categoryOptions' => $this->categoryOptions(),
        ]);
    }

    public function update(Request $request, Collaborator $collaborator): RedirectResponse
    {
        $collaborator = $this->ownedCollaborator($collaborator);
        $validated = $this->validateCollaborator($request);

        $collaborator->update($validated);

        return redirect()
            ->route('supervisor.collaborators.show', $collaborator)
            ->with('success', 'Collaborator record updated.');
    }

    public function destroy(Collaborator $collaborator): RedirectResponse
    {
        $collaborator = $this->ownedCollaborator($collaborator);
        $collaborator->delete();

        return redirect()
            ->route('supervisor.collaborators.index')
            ->with('success', 'Collaborator record deleted.');
    }

    protected function validateCollaborator(Request $request): array
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:academic,industry,government,ngo,other',
            'category_other' => 'nullable|required_if:category,other|string|max:120',
            'institution_name' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'faculty' => 'nullable|string|max:255',
            'position_title' => 'nullable|string|max:255',
            'expertise_area' => 'nullable|string|max:255',
            'research_field' => 'nullable|string|max:255',
            'working_email' => 'required|email|max:255',
            'phone_number' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:120',
            'suitable_for_grant' => 'nullable|boolean',
            'suitable_for_publication' => 'nullable|boolean',
            'suggested_reviewer' => 'nullable|boolean',
            'notes' => 'nullable|string|max:5000',
        ]);

        if (($validated['category'] ?? null) !== 'other') {
            $validated['category_other'] = null;
        }

        $validated['suitable_for_grant'] = $request->boolean('suitable_for_grant');
        $validated['suitable_for_publication'] = $request->boolean('suitable_for_publication');
        $validated['suggested_reviewer'] = $request->boolean('suggested_reviewer');

        return $validated;
    }

    protected function ownedCollaborator(Collaborator $collaborator): Collaborator
    {
        abort_unless($collaborator->user_id === Auth::id(), 403);

        return $collaborator;
    }

    protected function categoryOptions(): array
    {
        return [
            'academic' => 'Academic',
            'industry' => 'Industry',
            'government' => 'Government',
            'ngo' => 'NGO',
            'other' => 'Other',
        ];
    }
}
