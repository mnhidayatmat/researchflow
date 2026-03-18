<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProgressReport;
use App\Models\Student;
use App\Models\File;
use App\Services\Ai\Features\ReportSummarizer;
use App\Services\Ai\Features\DeadlineRiskDetector;
use App\Services\Ai\Features\TaskSuggester;
use App\Services\Ai\Features\DocumentComparator;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AiFeatureController extends Controller
{
    /**
     * Summarize a progress report.
     */
    public function summarizeReport(ProgressReport $report): JsonResponse
    {
        $this->authorize('view', $report->student);

        try {
            $summarizer = new ReportSummarizer();
            $summary = $summarizer->execute($report);

            return response()->json([
                'summary' => $summary,
                'report_id' => $report->id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate summary: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Summarize multiple reports for a student.
     */
    public function summarizeReports(Student $student, Request $request): JsonResponse
    {
        $this->authorize('view', $student);

        $request->validate([
            'report_ids' => 'required|array',
            'report_ids.*' => 'exists:progress_reports,id',
        ]);

        $reports = ProgressReport::whereIn('id', $request->report_ids)
            ->where('student_id', $student->id)
            ->get();

        try {
            $summarizer = new ReportSummarizer();
            $summary = $summarizer->summarizeMultiple($reports->toArray());

            return response()->json([
                'summary' => $summary,
                'reports_count' => $reports->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate summary: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Analyze deadline risks for a student.
     */
    public function analyzeDeadlineRisks(Student $student): JsonResponse
    {
        $this->authorize('view', $student);

        try {
            $detector = new DeadlineRiskDetector();
            $analysis = $detector->execute($student);

            return response()->json($analysis);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to analyze risks: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Suggest next tasks for a student.
     */
    public function suggestTasks(Student $student, Request $request): JsonResponse
    {
        $this->authorize('view', $student);

        $count = $request->get('count', 5);

        try {
            $suggester = new TaskSuggester();
            $suggestions = $suggester->execute($student, (int) $count);

            return response()->json($suggestions);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate suggestions: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Suggest subtasks for a task.
     */
    public function suggestSubtasks(Student $student, int $taskId): JsonResponse
    {
        $this->authorize('view', $student);

        $task = $student->tasks()->findOrFail($taskId);

        try {
            $suggester = new TaskSuggester();
            $subtasks = $suggester->suggestSubtasks($task);

            return response()->json([
                'task_id' => $task->id,
                'task_title' => $task->title,
                'subtasks' => $subtasks,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate subtasks: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Compare two documents.
     */
    public function compareDocuments(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'file1_id' => 'required|exists:files,id',
            'file2_id' => 'required|exists:files,id',
        ]);

        $file1 = File::findOrFail($validated['file1_id']);
        $file2 = File::findOrFail($validated['file2_id']);

        $this->authorize('view', $file1->student);
        $this->authorize('view', $file2->student);

        try {
            $comparator = new DocumentComparator();
            $comparison = $comparator->execute($file1, $file2);

            return response()->json($comparison);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to compare documents: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Compare file with its versions.
     */
    public function compareVersions(Student $student, File $file): JsonResponse
    {
        $this->authorize('view', $student);

        if ($file->student_id !== $student->id) {
            return response()->json(['error' => 'File does not belong to this student.'], 403);
        }

        try {
            $comparator = new DocumentComparator();
            $comparison = $comparator->compareWithVersions($file);

            return response()->json($comparison);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to compare versions: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Summarize changes between document versions.
     */
    public function summarizeFileChanges(Student $student, File $file): JsonResponse
    {
        $this->authorize('view', $student);

        if ($file->student_id !== $student->id) {
            return response()->json(['error' => 'File does not belong to this student.'], 403);
        }

        try {
            $comparator = new DocumentComparator();
            $summary = $comparator->summarizeChanges($file);

            return response()->json([
                'file_id' => $file->id,
                'file_name' => $file->original_name,
                'version' => $file->version,
                'changes_summary' => $summary,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to summarize changes: ' . $e->getMessage(),
            ], 500);
        }
    }
}
