<?php

namespace App\Services\Ai\Features;

use App\Models\Student;
use App\Models\Task;
use Illuminate\Support\Collection;

class DeadlineRiskDetector extends AiFeature
{
    /**
     * Analyze deadline risks for a student.
     */
    public function execute(Student $student): array
    {
        $atRiskTasks = $this->getAtRiskTasks($student);
        $overdueTasks = $this->getOverdueTasks($student);

        $messages = [
            ['role' => 'system', 'content' => $this->buildSystemPrompt()],
            ['role' => 'user', 'content' => $this->buildUserMessage($student, $atRiskTasks, $overdueTasks)],
        ];

        $analysis = $this->call($messages, ['temperature' => 0.3, 'max_tokens' => 1500]);

        return [
            'analysis' => $analysis,
            'at_risk_tasks' => $atRiskTasks->map(fn($t) => [
                'id' => $t->id,
                'title' => $t->title,
                'due_date' => $t->due_date?->format('Y-m-d'),
                'days_until_due' => $t->due_date?->diffInDays(now()),
                'status' => $t->status,
                'priority' => $t->priority,
            ])->values(),
            'overdue_tasks' => $overdueTasks->map(fn($t) => [
                'id' => $t->id,
                'title' => $t->title,
                'due_date' => $t->due_date?->format('Y-m-d'),
                'days_overdue' => $t->due_date ? now()->diffInDays($t->due_date) : 0,
                'status' => $t->status,
                'priority' => $t->priority,
            ])->values(),
            'summary' => [
                'total_at_risk' => $atRiskTasks->count(),
                'total_overdue' => $overdueTasks->count(),
                'high_priority_at_risk' => $atRiskTasks->where('priority', 'high')->count(),
            ],
        ];
    }

    /**
     * Get tasks at risk of missing deadlines.
     */
    protected function getAtRiskTasks(Student $student): Collection
    {
        return $student->tasks()
            ->whereIn('status', ['backlog', 'planned', 'in_progress'])
            ->whereNotNull('due_date')
            ->where('due_date', '>', now())
            ->where('due_date', '<=', now()->addDays(14))
            ->orderBy('due_date')
            ->get();
    }

    /**
     * Get overdue tasks.
     */
    protected function getOverdueTasks(Student $student): Collection
    {
        return $student->tasks()
            ->whereIn('status', ['backlog', 'planned', 'in_progress'])
            ->whereNotNull('due_date')
            ->where('due_date', '<', now()->startOfDay())
            ->orderBy('due_date')
            ->get();
    }

    protected function buildSystemPrompt(): string
    {
        return "You are an academic project manager analyzing research task deadlines. Your role is to:\n\n" .
            "1. Identify which tasks are at highest risk based on due dates, priorities, and dependencies\n" .
            "2. Recommend which tasks should be prioritized\n" .
            "3. Suggest realistic timeline adjustments if needed\n" .
            "4. Identify any cascading risks from dependencies\n\n" .
            "Be practical and constructive. Focus on actionable advice.\n\n" .
            "Format your response with:\n" .
            "- **Risk Level**: (Low/Medium/High)\n" .
            "- **Priority Tasks**: List of tasks to focus on\n" .
            "- **Recommendations**: Specific actions to take";
    }

    protected function buildUserMessage(Student $student, Collection $atRiskTasks, Collection $overdueTasks): string
    {
        $context = "Student: {$student->user->name}\n";
        $context .= "Programme: {$student->programme->name ?? 'N/A'}\n";
        $context .= "Research Title: {$student->research_title ?? 'TBD'}\n\n";

        if ($overdueTasks->isNotEmpty()) {
            $context .= "### OVERDUE TASKS ({$overdueTasks->count()})\n";
            foreach ($overdueTasks as $task) {
                $daysOverdue = now()->diffInDays($task->due_date);
                $context .= "- {$task->title} (due {$task->due_date->format('Y-m-d')}, {$daysOverdue} days overdue, priority: {$task->priority})\n";
            }
            $context .= "\n";
        }

        if ($atRiskTasks->isNotEmpty()) {
            $context .= "### AT-RISK TASKS (Due within 14 days: {$atRiskTasks->count()})\n";
            foreach ($atRiskTasks as $task) {
                $daysUntilDue = $task->due_date->diffInDays(now());
                $context .= "- {$task->title} (due {$task->due_date->format('Y-m-d')}, {$daysUntilDue} days remaining, priority: {$task->priority}, status: {$task->status})\n";
            }
            $context .= "\n";
        }

        if ($overdueTasks->isEmpty() && $atRiskTasks->isEmpty()) {
            $context .= "Good news! The student has no overdue or at-risk tasks.\n";
        }

        $context .= "\nPlease analyze the deadline situation and provide recommendations.";

        return $context;
    }
}
