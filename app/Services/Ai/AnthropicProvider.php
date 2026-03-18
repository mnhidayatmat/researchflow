<?php

namespace App\Services\Ai;

class AnthropicProvider extends BaseAiProvider
{
    protected string $apiVersion = '2023-06-01';

    protected function headers(): array
    {
        return [
            'Content-Type' => 'application/json',
            'x-api-key' => $this->apiKey,
            'anthropic-version' => $this->apiVersion,
        ];
    }

    protected function formatMessages(array $messages): array
    {
        // Anthropic combines system prompt separately
        $formatted = [];
        $systemPrompt = null;

        foreach ($messages as $msg) {
            if ($msg['role'] === 'system') {
                $systemPrompt = $msg['content'];
                continue;
            }

            $formatted[] = [
                'role' => $msg['role'],
                'content' => $msg['content'],
            ];
        }

        return [$formatted, $systemPrompt];
    }

    protected function buildChatPayload(array $messages, array $options): array
    {
        [$messages, $systemPrompt] = $this->formatMessages($messages);

        $payload = [
            'model' => $this->model,
            'messages' => $messages,
            'max_tokens' => $options['max_tokens'] ?? 4096,
            'temperature' => $options['temperature'] ?? 0.7,
        ];

        if ($systemPrompt) {
            $payload['system'] = $systemPrompt;
        }

        return $payload;
    }

    protected function extractContent(array $response): string
    {
        if (isset($response['error'])) {
            throw new \RuntimeException($response['error']['message'] ?? 'Unknown error');
        }

        return $response['content'][0]['text'] ?? '';
    }

    protected function extractStreamChunk(string $line): ?string
    {
        if (!str_starts_with($line, 'data: ')) {
            return null;
        }

        $data = json_decode(substr($line, 6), true);
        if (!$data) {
            return null;
        }

        // Handle different event types
        if ($data['type'] === 'content_block_delta') {
            return $data['delta']['text'] ?? null;
        }

        return null;
    }

    protected function getChatEndpoint(): string
    {
        return rtrim($this->baseUrl ?: 'https://api.anthropic.com/v1', '/') . '/messages';
    }

    protected function getEmbeddingEndpoint(): string
    {
        return rtrim($this->baseUrl ?: 'https://api.anthropic.com/v1', '/') . '/embeddings';
    }

    public function embed(string|array $texts): array|array
    {
        $isBatch = is_array($texts);
        $texts = $isBatch ? $texts : [$texts];

        $results = [];
        foreach ($texts as $text) {
            $response = $this->httpClient()->post($this->getEmbeddingEndpoint(), [
                'model' => $this->capabilities['embedding_model'] ?? 'claude-3-5-sonnet-20241022',
                'input' => $text,
                'embedding_type' => 'float',
            ]);

            if (!$response->successful()) {
                throw new \RuntimeException("Embedding request failed: {$response->body()}");
            }

            $results[] = $response->json('embedding', []);
        }

        return $isBatch ? $results : ($results[0] ?? []);
    }

    public function embeddingDimension(): int
    {
        return 1024; // Claude embeddings dimension
    }

    public function getName(): string
    {
        return 'anthropic';
    }

    public static function fromConfig(array $config): self
    {
        return new self(
            apiKey: $config['api_key'],
            model: $config['model'] ?? 'claude-3-5-sonnet-20241022',
            baseUrl: $config['base_url'] ?? null,
            capabilities: [
                'name' => 'anthropic',
                'features' => $config['features'] ?? ['chat', 'embeddings'],
                'embedding_model' => $config['embedding_model'] ?? 'claude-3-5-sonnet-20241022',
            ]
        );
    }
}
