<?php

namespace App\Services;

use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Models\AiProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AiService
{
    private ?AiProvider $provider = null;

    private array $providerConfig = [
        'openai' => [
            'default_base_url' => 'https://api.openai.com/v1',
            'default_model' => 'gpt-4o-mini',
            'endpoint' => '/chat/completions',
            'supports_streaming' => true,
        ],
        'gemini' => [
            'default_base_url' => 'https://generativelanguage.googleapis.com/v1beta/models',
            'default_model' => 'gemini-2.0-flash-exp',
            'endpoint' => ':generateContent',
            'supports_streaming' => true,
        ],
        'anthropic' => [
            'default_base_url' => 'https://api.anthropic.com/v1',
            'default_model' => 'claude-3-5-sonnet-20241022',
            'endpoint' => '/messages',
            'supports_streaming' => true,
            'version_header' => '2023-06-01',
        ],
        'ollama' => [
            'default_base_url' => 'http://localhost:11434/v1',
            'default_model' => 'llama2',
            'endpoint' => '/chat/completions',
            'supports_streaming' => false,
        ],
    ];

    public function getDefaultProvider(): ?AiProvider
    {
        if ($this->provider) {
            return $this->provider;
        }

        return Cache::remember('ai.default_provider', 3600, function () {
            return AiProvider::where('is_default', true)
                ->where('is_active', true)
                ->first();
        });
    }

    public function setProvider(AiProvider $provider): void
    {
        $this->provider = $provider;
    }

    public function getProvider(?string $slug = null): ?AiProvider
    {
        if ($slug) {
            return AiProvider::where('slug', $slug)
                ->where('is_active', true)
                ->first();
        }

        return $this->getDefaultProvider();
    }

    public function chat(
        string|array $message,
        ?AiConversation $conversation = null,
        ?string $systemPrompt = null,
        ?AiProvider $provider = null,
        array $options = []
    ): string {
        $provider = $provider ?? $this->getDefaultProvider();

        if (!$provider) {
            return $this->errorResponse('AI provider not configured. Please contact your administrator.');
        }

        $messages = $this->buildMessages($message, $conversation);

        try {
            return $this->callProvider($provider, $messages, $systemPrompt, $options);
        } catch (\Exception $e) {
            return $this->errorResponse('AI service error: ' . $e->getMessage());
        }
    }

    public function chatWithContext(
        string $message,
        array $context,
        ?string $systemPrompt = null,
        ?AiProvider $provider = null
    ): string {
        $enhancedPrompt = $this->buildContextualPrompt($message, $context);
        return $this->chat($enhancedPrompt, null, $systemPrompt, $provider);
    }

    public function streamChat(
        string $message,
        ?AiConversation $conversation = null,
        ?string $systemPrompt = null,
        ?AiProvider $provider = null,
        callable $onChunk = null
    ): \Generator {
        $provider = $provider ?? $this->getDefaultProvider();

        if (!$provider) {
            yield $this->errorResponse('AI provider not configured.');
            return;
        }

        $messages = $this->buildMessages($message, $conversation);

        try {
            yield from $this->streamProviderResponse($provider, $messages, $systemPrompt, $onChunk);
        } catch (\Exception $e) {
            yield $this->errorResponse('AI service error: ' . $e->getMessage());
        }
    }

    public function generateResearchSuggestions(
        string $researchTopic,
        string $stage = 'planning'
    ): array {
        $systemPrompt = $this->getSystemPromptForStage($stage);

        $prompt = $this->buildResearchPrompt($researchTopic, $stage);

        $response = $this->chat($prompt, null, $systemPrompt);

        return $this->parseStructuredResponse($response);
    }

    public function analyzeProgress(
        string $currentProgress,
        string $researchGoals
    ): array {
        $systemPrompt = 'You are an academic research advisor. Analyze student progress and provide constructive feedback. Return response as structured JSON with keys: assessment, strengths, concerns, recommendations.';

        $prompt = "Research Goals:\n{$researchGoals}\n\nCurrent Progress:\n{$currentProgress}\n\nProvide analysis.";

        $response = $this->chat($prompt, null, $systemPrompt);

        return $this->parseStructuredResponse($response);
    }

    public function suggestMilestones(
        string $researchTopic,
        string $programmeDuration,
        array $existingMilestones = []
    ): array {
        $systemPrompt = 'You are an academic planning expert. Suggest research milestones with timelines. Return as JSON array with objects containing: name, description, suggested_week, dependencies (array of milestone indices).';

        $existing = $existingMilestones
            ? "\n\nExisting milestones to consider:\n" . json_encode($existingMilestones)
            : '';

        $prompt = "Research Topic: {$researchTopic}\nProgramme Duration: {$programmeDuration} weeks{$existing}\n\nSuggest a comprehensive set of milestones.";

        $response = $this->chat($prompt, null, $systemPrompt);

        return $this->parseStructuredResponse($response);
    }

    public function createConversation(
        int $userId,
        string $title,
        ?int $studentId = null,
        ?array $contextFiles = null,
        string $scope = 'general'
    ): AiConversation {
        return AiConversation::create([
            'user_id' => $userId,
            'student_id' => $studentId,
            'title' => $title,
            'context_files' => $contextFiles,
            'scope' => $scope,
        ]);
    }

    public function addMessage(
        AiConversation $conversation,
        string $role,
        string $content,
        ?array $metadata = null
    ): AiMessage {
        return $conversation->messages()->create([
            'role' => $role,
            'content' => $content,
            'metadata' => $metadata,
        ]);
    }

    public function getConversationHistory(AiConversation $conversation, int $limit = 20): array
    {
        return $conversation->messages()
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get()
            ->map(fn($m) => [
                'role' => $m->role,
                'content' => $m->content,
            ])
            ->toArray();
    }

    public function estimateTokens(string $text): int
    {
        return (int) ceil(str_word_count($text) * 1.3);
    }

    public function estimateCost(int $inputTokens, int $outputTokens, ?AiProvider $provider = null): float
    {
        $provider = $provider ?? $this->getDefaultProvider();
        if (!$provider) return 0.0;

        $config = $this->providerConfig[$provider->slug] ?? null;

        $inputCost = $config['input_cost'] ?? 0.000001;
        $outputCost = $config['output_cost'] ?? 0.000002;

        return ($inputTokens * $inputCost) + ($outputTokens * $outputCost);
    }

    private function buildMessages(string|array $message, ?AiConversation $conversation): array
    {
        $messages = [];

        if ($conversation) {
            $messages = $this->getConversationHistory($conversation);
        }

        if (is_array($message)) {
            $messages = array_merge($messages, $message);
        } else {
            $messages[] = ['role' => 'user', 'content' => $message];
        }

        return $messages;
    }

    private function callProvider(
        AiProvider $provider,
        array $messages,
        ?string $systemPrompt = null,
        array $options = []
    ): string {
        $config = $this->providerConfig[$provider->slug] ?? [];

        return match ($provider->slug) {
            'openai', 'ollama' => $this->callOpenAiCompatible($provider, $messages, $systemPrompt, $config, $options),
            'gemini' => $this->callGemini($provider, $messages, $systemPrompt, $config, $options),
            'anthropic' => $this->callAnthropic($provider, $messages, $systemPrompt, $config, $options),
            default => $this->callOpenAiCompatible($provider, $messages, $systemPrompt, $config, $options),
        };
    }

    private function callOpenAiCompatible(
        AiProvider $provider,
        array $messages,
        ?string $systemPrompt,
        array $config,
        array $options
    ): string {
        $baseUrl = $provider->base_url ?? $config['default_base_url'] ?? 'https://api.openai.com/v1';
        $endpoint = $config['endpoint'] ?? '/chat/completions';
        $model = $provider->model ?? $config['default_model'] ?? 'gpt-4';

        $payload = array_merge([
            'model' => $model,
            'messages' => $systemPrompt
                ? [['role' => 'system', 'content' => $systemPrompt], ...$messages]
                : $messages,
            'max_tokens' => $options['max_tokens'] ?? 2000,
            'temperature' => $options['temperature'] ?? 0.7,
        ], $options['additional_params'] ?? []);

        $response = Http::withToken($provider->api_key)
            ->timeout($options['timeout'] ?? 60)
            ->post($baseUrl . $endpoint, $payload);

        if (!$response->successful()) {
            throw new \Exception("API Error: {$response->status()} - {$response->body()}");
        }

        return $response->json('choices.0.message.content', 'No response received.');
    }

    private function callGemini(
        AiProvider $provider,
        array $messages,
        ?string $systemPrompt,
        array $config,
        array $options
    ): string {
        $baseUrl = $provider->base_url ?? $config['default_base_url'];
        $model = $provider->model ?? $config['default_model'] ?? 'gemini-pro';

        $contents = collect($messages)->map(fn($m) => [
            'role' => $m['role'] === 'assistant' ? 'model' : 'user',
            'parts' => [['text' => $m['content']]],
        ])->toArray();

        $payload = [
            'contents' => $contents,
            'generationConfig' => [
                'maxOutputTokens' => $options['max_tokens'] ?? 2000,
                'temperature' => $options['temperature'] ?? 0.7,
            ],
        ];

        if ($systemPrompt) {
            $payload['systemInstruction'] = ['parts' => [['text' => $systemPrompt]]];
        }

        $url = "{$baseUrl}/{$model}:generateContent?key={$provider->api_key}";

        $response = Http::timeout($options['timeout'] ?? 60)
            ->post($url, $payload);

        if (!$response->successful()) {
            throw new \Exception("API Error: {$response->status()} - {$response->body()}");
        }

        return $response->json('candidates.0.content.parts.0.text', 'No response received.');
    }

    private function callAnthropic(
        AiProvider $provider,
        array $messages,
        ?string $systemPrompt,
        array $config,
        array $options
    ): string {
        $baseUrl = $provider->base_url ?? $config['default_base_url'];
        $endpoint = $config['endpoint'] ?? '/messages';
        $model = $provider->model ?? $config['default_model'] ?? 'claude-3-5-sonnet-20241022';
        $version = $config['version_header'] ?? '2023-06-01';

        // Convert messages format for Anthropic
        $messages = collect($messages)
            ->filter(fn($m) => $m['role'] !== 'system')
            ->map(fn($m) => ['role' => $m['role'], 'content' => $m['content']])
            ->values()
            ->toArray();

        $payload = [
            'model' => $model,
            'max_tokens' => $options['max_tokens'] ?? 2000,
            'messages' => $messages,
        ];

        if ($systemPrompt) {
            $payload['system'] = $systemPrompt;
        }

        $response = Http::withHeaders([
            'x-api-key' => $provider->api_key,
            'anthropic-version' => $version,
            'content-type' => 'application/json',
        ])->timeout($options['timeout'] ?? 60)
            ->post($baseUrl . $endpoint, $payload);

        if (!$response->successful()) {
            throw new \Exception("API Error: {$response->status()} - {$response->body()}");
        }

        return $response->json('content.0.text', 'No response received.');
    }

    private function streamProviderResponse(
        AiProvider $provider,
        array $messages,
        ?string $systemPrompt,
        ?callable $onChunk
    ): \Generator {
        // For streaming, we'd implement SSE handling here
        // For now, fall back to non-streaming
        yield $this->callProvider($provider, $messages, $systemPrompt);
    }

    private function buildContextualPrompt(string $message, array $context): string
    {
        $contextText = collect($context)->map(function ($item, $key) {
            if (is_array($item)) {
                $item = json_encode($item);
            }
            return "### {$key}\n{$item}";
        })->implode("\n\n");

        return "Context:\n{$contextText}\n\nUser Query:\n{$message}";
    }

    private function buildResearchPrompt(string $topic, string $stage): string
    {
        $prompts = [
            'planning' => "Provide research planning suggestions for: {$topic}. Include potential methodology, theoretical framework, and key literature areas.",
            'proposal' => "Suggest a thesis proposal structure for: {$topic}. Include sections, key points for each, and potential research questions.",
            'analysis' => "Suggest data analysis approaches for research on: {$topic}. Include statistical methods, visualization techniques, and interpretation guidelines.",
            'writing' => "Provide writing guidance for a thesis on: {$topic}. Focus on structure, academic tone, and common pitfalls to avoid.",
        ];

        return $prompts[$stage] ?? $prompts['planning'];
    }

    private function getSystemPromptForStage(string $stage): string
    {
        return match ($stage) {
            'planning' => 'You are an expert research advisor helping students plan their postgraduate research. Provide structured, actionable advice.',
            'proposal' => 'You are an academic mentor helping craft research proposals. Ensure suggestions follow academic standards and include necessary components.',
            'analysis' => 'You are a statistical consultant advising on research methodology and data analysis. Provide clear, practical guidance.',
            'writing' => 'You are an academic writing coach. Help students communicate their research effectively with proper academic conventions.',
            default => 'You are ResearchFlow AI, an academic research assistant. Help with research planning, writing, methodology, and analysis.',
        };
    }

    private function parseStructuredResponse(string $response): array
    {
        $json = json_decode($response, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            return $json;
        }

        // Try to extract JSON from markdown code blocks
        if (preg_match('/```(?:json)?\s*(.*?)\s*```/s', $response, $matches)) {
            $json = json_decode($matches[1], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $json;
            }
        }

        // Return as plain text array if parsing fails
        return ['response' => $response];
    }

    private function errorResponse(string $message): string
    {
        return "Error: {$message}";
    }
}
