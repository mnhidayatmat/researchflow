<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProgressReport;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProgressReportApiController extends Controller
{
    public function index(Request $request, Student $student)
    {
        $this->authorize('view', $student);

        $query = $student->progressReports()->with(['reviewer', 'revisions']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Search by title
        if ($request->has('search')) {
            $query->where('title', 'like', "%{$request->search}%");
        }

        $reports = $query->latest()
            ->paginate($request->per_page ?? 15);

        return response()->json($reports);
    }

    public function show(Student $student, ProgressReport $report)
    {
        $this->authorize('view', $report);

        $report->load(['reviewer', 'revisions.comments.user', 'student']);

        return response()->json($report);
    }

    public function store(Request $request, Student $student)
    {
        $this->authorize('update', $student);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:5000',
            'achievements' => 'nullable|string|max:2000',
            'challenges' => 'nullable|string|max:2000',
            'next_steps' => 'nullable|string|max:2000',
            'type' => ['required', Rule::in(array_keys(ProgressReport::typeOptions()))],
            'custom_type' => 'nullable|string|max:255|required_if:type,other',
            'period_start' => 'nullable|date',
            'period_end' => 'nullable|date|after_or_equal:period_start',
        ]);

        $report = $student->progressReports()->create([
            ...$validated,
            'custom_type' => $validated['type'] === 'other' ? ($validated['custom_type'] ?? null) : null,
            'status' => $request->boolean('submit') ? 'submitted' : 'draft',
            'submitted_at' => $request->boolean('submit') ? now() : null,
        ]);

        return response()->json($report->load('reviewer'), 201);
    }

    public function update(Request $request, Student $student, ProgressReport $report)
    {
        $this->authorize('update', $report);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string|max:5000',
            'achievements' => 'nullable|string|max:2000',
            'challenges' => 'nullable|string|max:2000',
            'next_steps' => 'nullable|string|max:2000',
            'type' => ['sometimes', 'required', Rule::in(array_keys(ProgressReport::typeOptions()))],
            'custom_type' => 'nullable|string|max:255|required_if:type,other',
            'period_start' => 'nullable|date',
            'period_end' => 'nullable|date|after_or_equal:period_start',
        ]);

        $wasDraft = $report->status === 'draft';
        $shouldSubmit = $request->boolean('submit');

        $report->update([
            ...$validated,
            'custom_type' => ($validated['type'] ?? $report->type) === 'other' ? ($validated['custom_type'] ?? $report->custom_type) : null,
            'status' => $shouldSubmit ? 'submitted' : ($wasDraft ? 'draft' : $report->status),
            'submitted_at' => ($shouldSubmit && !$report->submitted_at) ? now() : $report->submitted_at,
        ]);

        return response()->json($report->load('reviewer'));
    }

    public function destroy(Student $student, ProgressReport $report)
    {
        $this->authorize('delete', $report);

        // Only allow deletion of draft reports
        if ($report->status !== 'draft') {
            return response()->json([
                'message' => 'Only draft reports can be deleted.'
            ], 403);
        }

        $report->delete();

        return response()->json(['message' => 'Report deleted.']);
    }

    public function submit(Request $request, Student $student, ProgressReport $report)
    {
        $this->authorize('update', $report);

        if ($report->status === 'submitted') {
            return response()->json([
                'message' => 'Report has already been submitted.'
            ], 400);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:5000',
        ]);

        $report->update([
            ...$validated,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        return response()->json($report->load('reviewer'));
    }

    public function review(Request $request, Student $student, ProgressReport $report)
    {
        $this->authorize('review', $report);

        $validated = $request->validate([
            'supervisor_feedback' => 'required|string|max:5000',
            'decision' => 'required|in:accepted,revision_needed',
        ]);

        $report->update([
            'supervisor_feedback' => $validated['supervisor_feedback'],
            'status' => $validated['decision'],
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        if ($validated['decision'] === 'revision_needed') {
            $report->revisions()->create([
                'requested_by' => Auth::id(),
                'assigned_to' => $student->user_id,
                'description' => $validated['supervisor_feedback'],
            ]);
        }

        return response()->json($report->load(['reviewer', 'revisions']));
    }

    public function stats(Student $student)
    {
        $this->authorize('view', $student);

        $stats = [
            'total' => $student->progressReports()->count(),
            'draft' => $student->progressReports()->where('status', 'draft')->count(),
            'submitted' => $student->progressReports()->where('status', 'submitted')->count(),
            'accepted' => $student->progressReports()->where('status', 'accepted')->count(),
            'revision_needed' => $student->progressReports()->where('status', 'revision_needed')->count(),
            'pending_review' => $student->progressReports()->where('status', 'submitted')->count(),
            'latest_submission' => $student->progressReports()
                ->where('status', 'submitted')
                ->latest('submitted_at')
                ->first(),
        ];

        return response()->json($stats);
    }

    public function revisions(Student $student, ProgressReport $report)
    {
        $this->authorize('view', $report);

        $revisions = $report->revisions()
            ->with(['comments.user', 'requestedBy', 'assignedTo'])
            ->latest()
            ->get();

        return response()->json($revisions);
    }
}
