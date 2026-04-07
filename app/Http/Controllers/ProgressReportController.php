<?php

namespace App\Http\Controllers;

use App\Models\ProgressReport;
use App\Models\Student;
use App\Services\UserStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProgressReportController extends Controller
{
    public function __construct(protected UserStorageService $storageService)
    {
    }

    public function index(Student $student)
    {
        $this->authorize('view', $student);
        $reports = $student->progressReports()->latest()->paginate(10);
        return view('reports.index', compact('student', 'reports'));
    }

    public function create(Student $student)
    {
        $this->authorize('view', $student);

        $storageOwner = $this->resolveStorageOwner($student);
        $storageProfile = $storageOwner ? $this->storageService->profileFor($storageOwner) : null;
        $reportTypeOptions = ProgressReport::typeOptions();

        return view('reports.create', compact('student', 'storageOwner', 'storageProfile', 'reportTypeOptions'));
    }

    public function store(Request $request, Student $student)
    {
        $this->authorize('view', $student);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'achievements' => 'nullable|string',
            'challenges' => 'nullable|string',
            'next_steps' => 'nullable|string',
            'type' => ['required', Rule::in(array_keys(ProgressReport::typeOptions()))],
            'custom_type' => 'nullable|string|max:255|required_if:type,other',
            'period_start' => 'nullable|date',
            'period_end' => 'nullable|date',
            'attachment' => 'nullable|file|max:51200|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,zip,jpg,jpeg,png',
        ]);

        $reportData = [
            ...$validated,
            'status' => $request->has('submit') ? 'submitted' : 'draft',
            'submitted_at' => $request->has('submit') ? now() : null,
        ];
        unset($reportData['attachment']);
        $reportData['custom_type'] = $validated['type'] === 'other'
            ? ($validated['custom_type'] ?? null)
            : null;

        if ($request->hasFile('attachment')) {
            $storageOwner = $this->resolveStorageOwner($student);
            if (!$storageOwner) {
                throw ValidationException::withMessages([
                    'attachment' => 'No supervisor storage owner is assigned for this student.',
                ]);
            }

            try {
                $reportData = [
                    ...$reportData,
                    ...$this->storageService->uploadReportAttachment($request->file('attachment'), $student, $storageOwner),
                ];
            } catch (\Throwable $e) {
                throw ValidationException::withMessages([
                    'attachment' => $e->getMessage(),
                ]);
            }
        }

        $student->progressReports()->create($reportData);

        return redirect()->route('reports.index', $student)->with('success', 'Report saved.');
    }

    public function show(Student $student, ProgressReport $report)
    {
        $this->authorize('view', $report);
        $report->load('revisions.comments.user', 'attachmentStorageOwner');
        return view('reports.show', compact('student', 'report'));
    }

    public function destroy(Student $student, ProgressReport $report)
    {
        $this->authorize('delete', $report);

        if ($report->attachment_path) {
            $this->storageService->deleteReportAttachment($report);
        }

        $report->delete();

        return redirect()->route('reports.index', $student)->with('success', 'Report deleted.');
    }

    public function edit(Student $student, ProgressReport $report)
    {
        $this->authorize('update', $report);

        $storageOwner = $this->resolveStorageOwner($student);
        $storageProfile = $storageOwner ? $this->storageService->profileFor($storageOwner) : null;
        $reportTypeOptions = ProgressReport::typeOptions();

        return view('reports.edit', compact('student', 'report', 'storageOwner', 'storageProfile', 'reportTypeOptions'));
    }

    public function update(Request $request, Student $student, ProgressReport $report)
    {
        $this->authorize('update', $report);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'achievements' => 'nullable|string',
            'challenges' => 'nullable|string',
            'next_steps' => 'nullable|string',
            'type' => ['required', Rule::in(array_keys(ProgressReport::typeOptions()))],
            'custom_type' => 'nullable|string|max:255|required_if:type,other',
            'attachment' => 'nullable|file|max:51200|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,zip,jpg,jpeg,png',
        ]);

        $reportData = [
            ...$validated,
            'status' => $request->has('submit') ? 'submitted' : 'draft',
            'submitted_at' => $request->has('submit') ? now() : $report->submitted_at,
        ];
        unset($reportData['attachment']);
        $reportData['custom_type'] = $validated['type'] === 'other'
            ? ($validated['custom_type'] ?? null)
            : null;

        if ($request->hasFile('attachment')) {
            if ($report->attachment_path) {
                $this->storageService->deleteReportAttachment($report);
            }

            $storageOwner = $this->resolveStorageOwner($student);
            if (!$storageOwner) {
                throw ValidationException::withMessages([
                    'attachment' => 'No supervisor storage owner is assigned for this student.',
                ]);
            }

            try {
                $reportData = [
                    ...$reportData,
                    ...$this->storageService->uploadReportAttachment($request->file('attachment'), $student, $storageOwner),
                ];
            } catch (\Throwable $e) {
                throw ValidationException::withMessages([
                    'attachment' => $e->getMessage(),
                ]);
            }
        }

        $report->update($reportData);

        return redirect()->route('reports.show', [$student, $report])->with('success', 'Report updated.');
    }

    public function removeAttachment(Student $student, ProgressReport $report)
    {
        $this->authorize('manageAttachment', $report);
        abort_unless($report->attachment_path, 404, 'No attachment to remove.');

        $this->storageService->deleteReportAttachment($report);
        $report->update([
            'attachment_original_name' => null,
            'attachment_mime_type' => null,
            'attachment_size' => null,
            'attachment_disk' => null,
            'attachment_path' => null,
            'attachment_storage_owner_id' => null,
        ]);

        return back()->with('success', 'Attachment removed.');
    }

    public function replaceAttachment(Request $request, Student $student, ProgressReport $report)
    {
        $this->authorize('manageAttachment', $report);

        $request->validate([
            'attachment' => 'required|file|max:51200|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,zip,jpg,jpeg,png',
        ]);

        $storageOwner = $this->resolveStorageOwner($student);
        if (!$storageOwner) {
            throw ValidationException::withMessages([
                'attachment' => 'No supervisor storage owner is assigned for this student.',
            ]);
        }

        if ($report->attachment_path) {
            $this->storageService->deleteReportAttachment($report);
        }

        try {
            $report->update(
                $this->storageService->uploadReportAttachment($request->file('attachment'), $student, $storageOwner)
            );
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'attachment' => $e->getMessage(),
            ]);
        }

        return back()->with('success', 'Attachment replaced.');
    }

    public function downloadAttachment(Student $student, ProgressReport $report)
    {
        $this->authorize('view', $report);
        abort_unless($report->attachment_path, 404, 'No attachment found for this report.');

        return $this->storageService->downloadReportAttachment($report);
    }

    public function review(Request $request, Student $student, ProgressReport $report)
    {
        $this->authorize('review', $report);

        $validated = $request->validate([
            'supervisor_feedback' => 'required|string',
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

        return back()->with('success', 'Report reviewed.');
    }

    protected function resolveStorageOwner(Student $student)
    {
        return $student->supervisor ?: $student->cosupervisor;
    }
}
