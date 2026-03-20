<?php

namespace App\Services\Ai;

use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Models\Student;
use App\Models\File;
use App\Models\Task;
use App\Models\ProgressReport;
use Illuminate\Support\Collection;

class AiChatService
{
    protected ?AiProviderInterface $provider;

    public function __construct(?AiProviderInterface $provider = null)
    {
        $this->provider = $provider ?? AiServiceFactory::getProvider();
    }

    /**
     * Send a message and get AI response.
     */
    public function chat(AiConversation $conversation, string $userMessage): array
    {
        if (!$this->provider) {
            throw new \RuntimeException('AI provider not configured.');
        }

        // Save user message
        $userMsg = $conversation->messages()->create([
            'role' => 'user',
            'content' => $userMessage,
        ]);

        // Build message history with context
        $messages = $this->buildMessageContext($conversation, $userMessage);

        // Get AI response
        $response = $this->provider->chat($messages, [
            'temperature' => 0.7,
            'max_tokens' => 2000,
        ]);

        // Save assistant message
        $assistantMsg = $conversation->messages()->create([
            'role' => 'assistant',
            'content' => $response,
            'metadata' => [
                'provider' => $this->provider->getName(),
                'model' => $this->provider->getModel(),
            ],
        ]);

        return [
            'user_message' => $userMsg,
            'assistant_message' => $assistantMsg,
            'conversation' => $conversation->load('messages'),
        ];
    }

    /**
     * Build message context with system prompt and conversation history.
     */
    protected function buildMessageContext(AiConversation $conversation, string $currentMessage): array
    {
        $systemPrompt = $this->buildSystemPrompt($conversation);
        $history = $conversation->messages()
            ->orderBy('created_at')
            ->take(20)
            ->get()
            ->map(fn($m) => ['role' => $m->role, 'content' => $m->content])
            ->toArray();

        return [
            ['role' => 'system', 'content' => $systemPrompt],
            ...$history,
            ['role' => 'user', 'content' => $currentMessage],
        ];
    }

    /**
     * Build system prompt based on conversation context.
     */
    protected function buildSystemPrompt(AiConversation $conversation): string
    {
        $prompt = "You are ResearchFlow AI, an academic research assistant for postgraduate students and their supervisors.\n\n";
        $prompt .= "Your role is to help with:\n";
        $prompt .= "- Research planning and methodology\n";
        $prompt .= "- Academic writing and feedback\n";
        $prompt .= "- Task and deadline management\n";
        $prompt .= "- Document analysis and comparison\n\n";

        $prompt .= "Be concise, academic, and constructive in your responses.\n";

        // Add student context if available
        if ($conversation->student) {
            $student = $conversation->student;
            $prompt .= "\nStudent Context:\n";
            $prompt .= "- Name: {$student->user->name}\n";
            $prompt .= "- Programme: {$student->programme->name ?? 'N/A'}\n";
            $prompt .= "- Research Title: {$student->research_title ?? 'TBD'}\n";

            // Add task context
            $pendingTasks = $student->tasks()
                ->whereIn('status', ['in_progress', 'planned'])
                ->with('milestone')
                ->take(5)
                ->get();

            if ($pendingTasks->isNotEmpty()) {
                $prompt .= "\nCurrent Tasks:\n";
                foreach ($pendingTasks as $task) {
                    $prompt .= "- {$task->title} ({$task->status})";
                    $prompt .= $task->due_date ? " - Due: {$task->due_date->format('Y-m-d')}" : "";
                    $prompt .= "\n";
                }
            }

            // Add file context if specified
            if ($conversation->context_files) {
                $contextFiles = File::whereIn('id', $conversation->context_files)
                    ->where('is_latest', true)
                    ->get();

                if ($contextFiles->isNotEmpty()) {
                    $prompt .= "\nAvailable Documents:\n";
                    foreach ($contextFiles as $file) {
                        $prompt .= "- {$file->original_name} (ID: {$file->id})\n";
                    }
                }
            }
        }

        return $prompt;
    }

    /**
     * Simple chat method that accepts messages array and returns response text.
     */
    public function chatWithMessages(array $messages, string $systemPrompt = '', bool $useRag = false, ?AiConversation $conversation = null): string
    {
        if (!$this->provider) {
            throw new \RuntimeException('AI provider not configured.');
        }

        // Prepare messages for the provider
        $providerMessages = $messages;

        // Add system prompt at the beginning if provided
        if ($systemPrompt) {
            array_unshift($providerMessages, ['role' => 'system', 'content' => $systemPrompt]);
        }

        // If RAG is enabled and conversation has context files, retrieve relevant content
        if ($useRag && $conversation && !empty($conversation->context_files)) {
            $ragService = new \App\Services\Ai\AiRagService($this->provider);
            $context = $ragService->retrieveContext($conversation->context_files, end($messages)['content'] ?? '');

            if ($context) {
                // Inject context into the last user message
                $lastIndex = count($providerMessages) - 1;
                if ($providerMessages[$lastIndex]['role'] === 'user') {
                    $providerMessages[$lastIndex]['content'] = "Context:\n{$context}\n\nQuestion: {$providerMessages[$lastIndex]['content']}";
                }
            }
        }

        // Get AI response
        return $this->provider->chat($providerMessages, [
            'temperature' => 0.7,
            'max_tokens' => 2000,
        ]);
    }

    /**
     * Create a new conversation.
     */
    public function createConversation(array $data): AiConversation
    {
        return auth()->user()->aiConversations()->create([
            'title' => $data['title'] ?? 'New Conversation',
            'student_id' => $data['student_id'] ?? null,
            'context_files' => $data['context_files'] ?? null,
            'scope' => $data['scope'] ?? 'general',
        ]);
    }

    /**
     * Update conversation context files.
     */
    public function updateContextFiles(AiConversation $conversation, array $fileIds): void
    {
        $conversation->update(['context_files' => $fileIds]);
    }

    /**
     * Get conversation with messages.
     */
    public function getConversation(AiConversation $conversation): AiConversation
    {
        return $conversation->load('messages');
    }

    /**
     * Get user's conversations.
     */
    public function getUserConversations(int $userId, int $limit = 20): Collection
    {
        return AiConversation::where('user_id', $userId)
            ->latest()
            ->take($limit)
            ->get();
    }
}
