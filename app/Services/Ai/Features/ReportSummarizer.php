<?php

namespace App\Services\Ai\Features;

use App\Models\ProgressReport;

class ReportSummarizer extends AiFeature
{
    /**
     * Summarize a progress report.
     */
    public function execute(ProgressReport $report): string
    {
        $messages = [
            ['role' => 'system', 'content' => $this->buildSystemPrompt()],
            ['role' => 'user', 'content' => $this->buildUserMessage($report)],
        ];

        return $this->call($messages, ['temperature' => 0.5, 'max_tokens' => 1000]);
    }

    /**
     * Summarize multiple reports.
     */
    public function summarizeMultiple(array $reports): string
    {
        $content = "Please summarize the following progress reports:\n\n";

        foreach ($reports as $index => $report) {
            $content .= "--- Report {$index + 1} ({$report->submitted_at?->format('Y-m-d')}) ---\n";
            $content .= "Title: {$report->title}\n";
            $content .= "Achievements: {$report->achievements}\n";
            $content .= "Challenges: {$report->challenges}\n";
            $content .= "Next Steps: {$report->next_steps}\n\n";
        }

        $messages = [
            ['role' => 'system', 'content' => $this->buildSystemPrompt()],
            ['role' => 'user', 'content' => $content],
        ];

        return $this->call($messages, ['temperature' => 0.5, 'max_tokens' => 2000]);
    }

    protected function buildSystemPrompt(): string
    {
        return "You are an academic research assistant. Your task is to summarize student progress reports concisely and highlight key achievements, challenges, and recommendations.\n\n" .
            "Your summary should:\n" .
            "1. Be 2-3 paragraphs maximum\n" .
            "2. Highlight key achievements and milestones reached\n" .
            "3. Identify significant challenges or blockers\n" .
            "4. Suggest actionable next steps or considerations\n" .
            "5. Use academic and supportive tone\n\n" .
            "Format your response with clear sections using markdown.";
    }

    protected function buildUserMessage(ProgressReport $report): string
    {
        return "Please summarize the following progress report:\n\n" .
            "--- Progress Report ---\n" .
            "Title: {$report->title}\n" .
            "Period: {$report->period_start?->format('Y-m-d')} to {$report->period_end?->format('Y-m-d')}\n" .
            "Status: {$report->status}\n\n" .
            "Achievements:\n{$report->achievements}\n\n" .
            "Challenges:\n{$report->challenges}\n\n" .
            "Next Steps:\n{$report->next_steps}\n\n" .
            "--- End of Report ---";
    }
}
