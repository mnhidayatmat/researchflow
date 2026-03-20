<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProgressReport;
use App\Models\Task;
use App\Services\Ai\AiServiceFactory;
use App\Services\Ai\Features\DeadlineRiskDetector;
use App\Services\Ai\Features\ReportSummarizer;
use App\Services\Ai\Features\TaskSuggester;
use Illuminate\Http\Request;

class AiFeatureController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function summarizeReport(Request $request, ProgressReport $report)
    {
        $this->authorize('view', $report);

        $provider = AiServiceFactory::getProvider();

        if (!$provider) {
            return response()->json([
                'error' => 'No AI provider configured.',
            ], 400);
        }

        $summarizer = new ReportSummarizer($provider);
        $summary = $summarizer->execute($report);

        return response()->json([
            'summary' => $summary,
            'report_id' => $report->id,
        ]);
    }

    public function summarizeReports(Request $request, $studentId)
    {
        $student = \App\Models\Student::findOrFail($studentId);
        $this->authorize('view', $student);

        $provider = AiServiceFactory::getProvider();

        if (!$provider) {
            return response()->json([
                'error' => 'No AI provider configured.',
            ], 400);
        }

        $reports = ProgressReport::where('student_id', $studentId)
            ->where('status', '!=', 'draft')
            ->latest()
            ->take(5)
            ->get();

        if ($reports->isEmpty()) {
            return response()->json([
                'error' => 'No reports found to summarize.',
            ], 400);
        }

        $summaries = $reports->map(function ($report) use ($provider) {
            $summarizer = new ReportSummarizer($provider);
            return [
                'report_id' => $report->id,
                'title' => $report->title,
                'summary' => $summarizer->execute($report),
            ];
        });

        return response()->json([
            'summaries' => $summaries,
        ]);
    }

    public function analyzeDeadlineRisks(Request $request, $studentId)
    {
        $student = \App\Models\Student::findOrFail($studentId);
        $this->authorize('view', $student);

        $provider = AiServiceFactory::getProvider();

        if (!$provider) {
            return response()->json([
                'error' => 'No AI provider configured.',
            ], 400);
        }

        $detector = new DeadlineRiskDetector($provider);
        $analysis = $detector->execute($student);

        return response()->json($analysis);
    }

    public function suggestTasks(Request $request, $studentId)
    {
        $student = \App\Models\Student::findOrFail($studentId);
        $this->authorize('view', $student);

        $provider = AiServiceFactory::getProvider();

        if (!$provider) {
            return response()->json([
                'error' => 'No AI provider configured.',
            ], 400);
        }

        $suggester = new TaskSuggester($provider);
        $suggestions = $suggester->execute($student);

        return response()->json([
            'suggestions' => $suggestions,
        ]);
    }

    public function suggestSubtasks(Request $request, $studentId, Task $task)
    {
        $student = \App\Models\Student::findOrFail($studentId);
        $this->authorize('view', $student);

        if ($task->student_id !== $student->id) {
            return response()->json([
                'error' => 'Task does not belong to this student.',
            ], 403);
        }

        $provider = AiServiceFactory::getProvider();

        if (!$provider) {
            return response()->json([
                'error' => 'No AI provider configured.',
            ], 400);
        }

        $suggester = new TaskSuggester($provider);
        $subtasks = $suggester->breakDownTask($task, $student);

        return response()->json([
            'subtasks' => $subtasks,
        ]);
    }

    public function compareDocuments(Request $request)
    {
        $validated = $request->validate([
            'file1_id' => 'required|exists:files,id',
            'file2_id' => 'required|exists:files,id',
        ]);

        $provider = AiServiceFactory::getProvider();

        if (!$provider) {
            return response()->json([
                'error' => 'No AI provider configured.',
            ], 400);
        }

        $file1 = \App\Models\File::findOrFail($validated['file1_id']);
        $file2 = \App\Models\File::findOrFail($validated['file2_id']);

        $comparator = new \App\Services\Ai\Features\DocumentComparator($provider);
        $comparison = $comparator->execute($file1, $file2);

        return response()->json($comparison);
    }

    public function compareVersions(Request $request, $studentId, $fileId)
    {
        $file = \App\Models\File::findOrFail($fileId);

        $provider = AiServiceFactory::getProvider();

        if (!$provider) {
            return response()->json([
                'error' => 'No AI provider configured.',
            ], 400);
        }

        $comparator = new \App\Services\Ai\Features\DocumentComparator($provider);
        $comparison = $comparator->compareAllVersions($file);

        return response()->json($comparison);
    }

    public function summarizeFileChanges(Request $request, $studentId, $fileId)
    {
        $file = \App\Models\File::findOrFail($fileId);

        $provider = AiServiceFactory::getProvider();

        if (!$provider) {
            return response()->json([
                'error' => 'No AI provider configured.',
            ], 400);
        }

        $comparator = new \App\Services\Ai\Features\DocumentComparator($provider);
        $summary = $comparator->summarizeChanges($file);

        return response()->json([
            'summary' => $summary,
        ]);
    }
}
