<?php

namespace App\Services\Ai;

class GeminiProvider extends BaseAiProvider
{
    protected string $apiKey;

    protected function headers(): array
    {
        return [
            'Content-Type' => 'application/json',
        ];
    }

    protected function formatMessages(array $messages): array
    {
        // Gemini uses 'contents' array with 'role' as 'user' or 'model'
        $contents = [];
        $systemInstruction = null;

        foreach ($messages as $msg) {
            if ($msg['role'] === 'system') {
                $systemInstruction = [
                    'parts' => [['text' => $msg['content']]]
                ];
                continue;
            }

            $contents[] = [
                'role' => $msg['role'] === 'assistant' ? 'model' : 'user',
                'parts' => $msg['parts'] ?? [['text' => $msg['content']]],
            ];
        }

        return [$contents, $systemInstruction];
    }

    protected function buildChatPayload(array $messages, array $options): array
    {
        [$contents, $systemInstruction] = $this->formatMessages($messages);

        $payload = [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => $options['temperature'] ?? 0.7,
                'maxOutputTokens' => $options['max_tokens'] ?? 4096,
            ],
        ];

        if ($systemInstruction) {
            $payload['systemInstruction'] = $systemInstruction;
        }

        return $payload;
    }

    protected function sendRequest(array $payload): array
    {
        $response = $this->httpClient()->post(
            $this->getChatEndpoint() . '?key=' . $this->apiKey,
            $payload
        );

        if (!$response->successful()) {
            throw new \RuntimeException("AI request failed: {$response->body()}");
        }

        return $response->json();
    }

    protected function extractContent(array $response): string
    {
        return $response['candidates'][0]['content']['parts'][0]['text'] ?? '';
    }

    protected function extractStreamChunk(string $line): ?string
    {
        // Gemini uses a different streaming format
        $data = json_decode($line, true);
        if (!$data || !isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            return null;
        }

        return $data['candidates'][0]['content']['parts'][0]['text'];
    }

    protected function getChatEndpoint(): string
    {
        $baseUrl = $this->baseUrl ?: 'https://generativelanguage.googleapis.com/v1beta';
        $model = $this->model ?? 'gemini-2.5-flash';
        return rtrim($baseUrl, '/') . "/models/{$model}:generateContent";
    }

    protected function getEmbeddingEndpoint(): string
    {
        $baseUrl = $this->baseUrl ?: 'https://generativelanguage.googleapis.com/v1beta';
        $model = $this->capabilities['embedding_model'] ?? 'text-embedding-004';
        return rtrim($baseUrl, '/') . "/models/{$model}:batchEmbedContents";
    }

    public function embed(string|array $texts): array
    {
        $isBatch = is_array($texts);
        $texts = $isBatch ? $texts : [$texts];

        $requests = array_map(fn($text) => [
            'model' => $this->capabilities['embedding_model'] ?? 'text-embedding-004',
            'content' => ['parts' => [['text' => $text]]],
        ], $texts);

        $response = $this->httpClient()->post(
            $this->getEmbeddingEndpoint() . '?key=' . $this->apiKey,
            ['requests' => $requests]
        );

        if (!$response->successful()) {
            throw new \RuntimeException("Embedding request failed: {$response->body()}");
        }

        $embeddings = array_map(
            fn($item) => $item['embedding']['values'],
            $response->json('embeddings', [])
        );

        return $isBatch ? $embeddings : ($embeddings[0] ?? []);
    }

    public function embeddingDimension(): int
    {
        return 768; // text-embedding-004 dimension
    }

    public function getName(): string
    {
        return 'gemini';
    }

    public static function fromConfig(array $config): self
    {
        return new self(
            apiKey: $config['api_key'],
            model: $config['model'] ?? 'gemini-2.5-flash',
            baseUrl: $config['base_url'] ?? null,
            capabilities: [
                'name' => 'gemini',
                'features' => $config['features'] ?? ['chat', 'embeddings'],
                'embedding_model' => $config['embedding_model'] ?? 'text-embedding-004',
            ]
        );
    }
}
