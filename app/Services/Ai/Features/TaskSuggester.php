<?php

namespace App\Services\Ai\Features;

use App\Models\Student;
use App\Models\Task;
use App\Models\Milestone;
use Illuminate\Support\Collection;

class TaskSuggester extends AiFeature
{
    /**
     * Suggest next tasks for a student.
     */
    public function execute(Student $student, ?int $count = 5): array
    {
        $context = $this->buildStudentContext($student);
        $recentTasks = $this->getRecentTasks($student);
        $currentMilestone = $this->getCurrentMilestone($student);
        $upcomingMilestone = $this->getUpcomingMilestone($student);

        $messages = [
            ['role' => 'system', 'content' => $this->buildSystemPrompt()],
            ['role' => 'user', 'content' => $this->buildUserMessage($student, $context, $recentTasks, $currentMilestone, $upcomingMilestone, $count)],
        ];

        $suggestions = $this->call($messages, ['temperature' => 0.6, 'max_tokens' => 1500]);

        // Parse the response into structured suggestions
        $tasks = $this->parseSuggestions($suggestions);

        return [
            'suggestions' => $tasks,
            'context' => [
                'current_milestone' => $currentMilestone?->title,
                'upcoming_milestone' => $upcomingMilestone?->title,
                'active_tasks' => $student->tasks()->whereIn('status', ['in_progress', 'planned'])->count(),
                'completed_tasks' => $student->tasks()->where('status', 'completed')->count(),
            ],
            'raw_response' => $suggestions,
        ];
    }

    /**
     * Suggest subtasks for a given task.
     */
    public function suggestSubtasks(Task $task): array
    {
        $student = $task->student;

        $messages = [
            ['role' => 'system', 'content' => "You are an academic research assistant. Break down research tasks into actionable subtasks. Each subtask should be specific, measurable, and time-bound."],
            ['role' => 'user', 'content' => $this->buildSubtaskPrompt($task, $student)],
        ];

        $response = $this->call($messages, ['temperature' => 0.5, 'max_tokens' => 1000]);

        return $this->parseSuggestions($response);
    }

    protected function buildStudentContext(Student $student): string
    {
        $context = [];

        if ($student->research_title) {
            $context[] = "Research Title: {$student->research_title}";
        }

        if ($student->programme) {
            $context[] = "Programme: {$student->programme->name}";
        }

        return implode("\n", $context);
    }

    protected function getRecentTasks(Student $student): Collection
    {
        return $student->tasks()
            ->latest()
            ->take(10)
            ->get(['id', 'title', 'status', 'completed_at']);
    }

    protected function getCurrentMilestone(Student $student): ?Milestone
    {
        return $student->researchJourney?->milestones()
            ->where('due_date', '>=', now())
            ->orderBy('due_date')
            ->first();
    }

    protected function getUpcomingMilestone(Student $student): ?Milestone
    {
        return $student->researchJourney?->milestones()
            ->where('due_date', '>', now())
            ->orderBy('due_date')
            ->first();
    }

    protected function buildSystemPrompt(): string
    {
        return "You are an academic research advisor specializing in breaking down research work into actionable tasks.\n\n" .
            "Your suggestions should:\n" .
            "1. Be specific and actionable (not vague)\n" .
            "2. Follow a logical progression\n" .
            "3. Consider the research stage and upcoming milestones\n" .
            "4. Include a mix of short (1-2 days) and medium (1-2 weeks) tasks\n" .
            "5. Be relevant to the student's research area\n\n" .
            "Format each suggestion as:\n" .
            "- **[Title]**: Brief description (Estimated: X days, Priority: high/medium/low)";
    }

    protected function buildUserMessage(Student $student, string $context, Collection $recentTasks, ?Milestone $current, ?Milestone $upcoming, int $count): string
    {
        $message = "Please suggest {$count} specific next tasks for this student:\n\n";
        $message .= "### Student Context\n{$context}\n\n";

        if ($current) {
            $message .= "### Current Milestone\n{$current->title} (due {$current->due_date->format('Y-m-d')})\n";
            if ($current->description) {
                $message .= "{$current->description}\n";
            }
            $message .= "\n";
        }

        if ($upcoming && $upcoming->id !== $current?->id) {
            $message .= "### Upcoming Milestone\n{$upcoming->title} (due {$upcoming->due_date->format('Y-m-d')})\n\n";
        }

        $message .= "### Recent Tasks\n";
        if ($recentTasks->isNotEmpty()) {
            foreach ($recentTasks as $task) {
                $statusIcon = match ($task->status) {
                    'completed' => '✓',
                    'in_progress' => '→',
                    default => '○',
                };
                $message .= "{$statusIcon} {$task->title} ({$task->status})\n";
            }
        } else {
            $message .= "No recent tasks.\n";
        }

        return $message;
    }

    protected function buildSubtaskPrompt(Task $task, Student $student): string
    {
        $prompt = "Break down the following task into 3-7 actionable subtasks:\n\n";
        $prompt .= "### Task\n{$task->title}\n";

        if ($task->description) {
            $prompt .= "\n{$task->description}\n";
        }

        if ($task->due_date) {
            $prompt .= "\nDue date: {$task->due_date->format('Y-m-d')}";
        }

        $prompt .= "\n\n" .
            "Student context: {$student->research_title ?? 'Not specified'}\n\n" .
            "Format each subtask as:\n" .
            "- **[Title]**: Description (Estimated: X days)";

        return $prompt;
    }

    protected function parseSuggestions(string $response): array
    {
        // Parse markdown-style suggestions into structured array
        preg_match_all('/\*\*(.+?)\*\*:\s*(.+?)\s*\((Estimated:\s*(\d+)\s*days?,?\s*Priority:\s*(\w+))\)/s', $response, $matches, PREG_SET_ORDER);

        $tasks = [];
        foreach ($matches as $match) {
            $tasks[] = [
                'title' => trim($match[1]),
                'description' => trim($match[2]),
                'estimated_days' => (int) ($match[4] ?? 3),
                'priority' => $match[5] ?? 'medium',
            ];
        }

        // Fallback: if no structured matches found, return the raw response
        if (empty($tasks)) {
            return [
                [
                    'title' => 'Suggested Tasks',
                    'description' => $response,
                    'estimated_days' => 7,
                    'priority' => 'medium',
                ]
            ];
        }

        return $tasks;
    }
}
