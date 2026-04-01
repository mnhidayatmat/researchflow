<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\SupervisorPublication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PublicationController extends Controller
{
    public function index(Request $request): View
    {
        $userEmail = Auth::user()->email;

        $query = SupervisorPublication::with(['user', 'authors'])
            ->where(function ($q) use ($userEmail) {
                $q->where('user_id', Auth::id())
                  ->orWhereHas('authors', fn ($a) => $a->where('email', $userEmail));
            })
            ->latest('submission_date')
            ->latest('updated_at');

        if ($search = trim((string) $request->get('search'))) {
            $query->where(function ($builder) use ($search) {
                $builder->where('title', 'like', "%{$search}%")
                        ->orWhere('journal', 'like', "%{$search}%");
            });
        }

        if ($stage = $request->get('stage')) {
            $query->where('stage', $stage);
        }

        if ($quartile = $request->get('quartile')) {
            $query->where('quartile', $quartile);
        }

        if ($journalIndex = $request->get('journal_index')) {
            $query->where('journal_index', $journalIndex);
        }

        $publications = $query->paginate(10);

        // Stats: only own publications count toward personal stats
        $statsQuery = SupervisorPublication::where('user_id', Auth::id());
        $stats = [
            'total'              => (clone $statsQuery)->count(),
            'published'          => (clone $statsQuery)->where('stage', 'published')->count(),
            'under_review'       => (clone $statsQuery)->where('stage', 'under_review')->count(),
            'revision_required'  => (clone $statsQuery)->where('stage', 'revision_required')->count(),
        ];

        return view('supervisor.publications.index', [
            'publications'  => $publications,
            'stats'         => $stats,
            'stages'        => SupervisorPublication::STAGES,
            'quartiles'     => SupervisorPublication::QUARTILES,
            'journalIndexes' => SupervisorPublication::JOURNAL_INDEXES,
        ]);
    }

    public function create(): View
    {
        return view('supervisor.publications.create', [
            'publication' => new SupervisorPublication(['stage' => 'draft']),
            'stages'        => SupervisorPublication::STAGES,
            'quartiles'     => SupervisorPublication::QUARTILES,
            'journalIndexes' => SupervisorPublication::JOURNAL_INDEXES,
            'submitLabel'   => 'Create Record',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $authors = $this->validatedAuthors($request);

        $publication = SupervisorPublication::create([
            ...$data,
            'user_id' => Auth::id(),
        ]);

        $this->syncAuthors($publication, $authors);

        return redirect()
            ->route('supervisor.publications.index')
            ->with('success', 'Publication record created.');
    }

    public function edit(SupervisorPublication $publication): View
    {
        $publication = $this->ownedPublication($publication);
        $publication->load('authors');

        return view('supervisor.publications.edit', [
            'publication'   => $publication,
            'stages'        => SupervisorPublication::STAGES,
            'quartiles'     => SupervisorPublication::QUARTILES,
            'journalIndexes' => SupervisorPublication::JOURNAL_INDEXES,
            'submitLabel'   => 'Update Record',
        ]);
    }

    public function update(Request $request, SupervisorPublication $publication): RedirectResponse
    {
        $publication = $this->ownedPublication($publication);
        $data    = $this->validatedData($request);
        $authors = $this->validatedAuthors($request);

        $publication->update($data);
        $this->syncAuthors($publication, $authors);

        return redirect()
            ->route('supervisor.publications.index')
            ->with('success', 'Publication record updated.');
    }

    public function destroy(SupervisorPublication $publication): RedirectResponse
    {
        $publication = $this->ownedPublication($publication);
        $publication->delete();

        return redirect()
            ->route('supervisor.publications.index')
            ->with('success', 'Publication record deleted.');
    }

    protected function validatedData(Request $request): array
    {
        return $request->validate([
            'title'                      => ['required', 'string', 'max:500'],
            'journal'                    => ['required', 'string', 'max:255'],
            'journal_index'              => ['required', Rule::in(array_keys(SupervisorPublication::JOURNAL_INDEXES))],
            'journal_index_other'        => ['nullable', 'string', 'max:255', 'required_if:journal_index,others'],
            'quartile'                   => ['nullable', Rule::in(array_keys(SupervisorPublication::QUARTILES))],
            'impact_factor'              => ['nullable', 'numeric', 'min:0', 'max:9999.999'],
            'stage'                      => ['required', Rule::in(array_keys(SupervisorPublication::STAGES))],
            'submission_date'            => ['nullable', 'date'],
            'rejected_1_date'            => ['nullable', 'date'],
            'rejected_1_reviewer_input'  => ['nullable', 'string'],
            'rejected_2_date'            => ['nullable', 'date'],
            'rejected_2_reviewer_input'  => ['nullable', 'string'],
            'rejected_3_date'            => ['nullable', 'date'],
            'rejected_3_reviewer_input'  => ['nullable', 'string'],
        ]);
    }

    protected function validatedAuthors(Request $request): array
    {
        $request->validate([
            'authors'                => ['nullable', 'array'],
            'authors.*.name'         => ['required_with:authors.*', 'string', 'max:255'],
            'authors.*.email'        => ['nullable', 'email', 'max:255'],
            'authors.*.department'   => ['nullable', 'string', 'max:255'],
            'authors.*.institution'  => ['nullable', 'string', 'max:255'],
        ]);

        return collect($request->input('authors', []))
            ->filter(fn ($a) => filled($a['name'] ?? ''))
            ->values()
            ->all();
    }

    protected function syncAuthors(SupervisorPublication $publication, array $authors): void
    {
        $publication->authors()->delete();

        foreach ($authors as $index => $author) {
            $publication->authors()->create([
                'name'        => $author['name'],
                'email'       => $author['email'] ?? null,
                'department'  => $author['department'] ?? null,
                'institution' => $author['institution'] ?? null,
                'order'       => $index,
            ]);
        }
    }

    protected function ownedPublication(SupervisorPublication $publication): SupervisorPublication
    {
        abort_unless($publication->user_id === Auth::id(), 403);

        return $publication;
    }
}
