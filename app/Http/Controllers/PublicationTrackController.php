<?php

namespace App\Http\Controllers;

use App\Models\PublicationTrack;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PublicationTrackController extends Controller
{
    public function index(Student $student)
    {
        $this->authorize('view', $student);

        $publicationTracks = $student->publicationTracks()
            ->latest('submission_date')
            ->latest('created_at')
            ->paginate(10);

        return view('publications.index', compact('student', 'publicationTracks'));
    }

    public function create(Student $student)
    {
        $this->authorize('update', $student);

        return view('publications.create', [
            'student' => $student,
            'publicationTrack' => new PublicationTrack(),
            'stages' => PublicationTrack::STAGES,
            'quartiles' => PublicationTrack::QUARTILES,
        ]);
    }

    public function store(Request $request, Student $student)
    {
        $this->authorize('update', $student);

        $student->publicationTracks()->create($this->validatedData($request));

        return redirect()
            ->route('publications.index', $student)
            ->with('success', 'Publication track created.');
    }

    public function edit(Student $student, PublicationTrack $publicationTrack)
    {
        $this->authorize('update', $student);
        abort_unless($publicationTrack->student_id === $student->id, 404);

        return view('publications.edit', [
            'student' => $student,
            'publicationTrack' => $publicationTrack,
            'stages' => PublicationTrack::STAGES,
            'quartiles' => PublicationTrack::QUARTILES,
        ]);
    }

    public function update(Request $request, Student $student, PublicationTrack $publicationTrack)
    {
        $this->authorize('update', $student);
        abort_unless($publicationTrack->student_id === $student->id, 404);

        $publicationTrack->update($this->validatedData($request));

        return redirect()
            ->route('publications.index', $student)
            ->with('success', 'Publication track updated.');
    }

    public function destroy(Student $student, PublicationTrack $publicationTrack)
    {
        $this->authorize('update', $student);
        abort_unless($publicationTrack->student_id === $student->id, 404);

        $publicationTrack->delete();

        return redirect()
            ->route('publications.index', $student)
            ->with('success', 'Publication track removed.');
    }

    protected function validatedData(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:500'],
            'journal' => ['required', 'string', 'max:255'],
            'quartile' => ['nullable', Rule::in(array_keys(PublicationTrack::QUARTILES))],
            'impact_factor' => ['nullable', 'numeric', 'min:0', 'max:9999.999'],
            'stage' => ['required', Rule::in(array_keys(PublicationTrack::STAGES))],
            'submission_date' => ['nullable', 'date'],
            'rejected_1_date' => ['nullable', 'date'],
            'rejected_1_reviewer_input' => ['nullable', 'string'],
            'rejected_2_date' => ['nullable', 'date'],
            'rejected_2_reviewer_input' => ['nullable', 'string'],
            'rejected_3_date' => ['nullable', 'date'],
            'rejected_3_reviewer_input' => ['nullable', 'string'],
        ]);
    }
}
