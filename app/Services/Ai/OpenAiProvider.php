<?php

namespace App\Services\Ai;

class OpenAiProvider extends BaseAiProvider
{
    protected function headers(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer {$this->apiKey}",
        ];
    }

    protected function formatMessages(array $messages): array
    {
        return $messages;
    }

    protected function extractContent(array $response): string
    {
        return $response['choices'][0]['message']['content'] ?? '';
    }

    protected function extractStreamChunk(string $line): ?string
    {
        if (!str_starts_with($line, 'data: ')) {
            return null;
        }

        $data = substr($line, 6);
        if ($data === '[DONE]') {
            return null;
        }

        $json = json_decode($data, true);
        if (!$json || !isset($json['choices'][0]['delta']['content'])) {
            return null;
        }

        return $json['choices'][0]['delta']['content'];
    }

    protected function getChatEndpoint(): string
    {
        return rtrim($this->baseUrl ?: 'https://api.openai.com/v1', '/') . '/chat/completions';
    }

    protected function getEmbeddingEndpoint(): string
    {
        return rtrim($this->baseUrl ?: 'https://api.openai.com/v1', '/') . '/embeddings';
    }

    public function embed(string|array $texts): array
    {
        $isBatch = is_array($texts);
        $texts = $isBatch ? $texts : [$texts];

        $response = $this->httpClient()->post($this->getEmbeddingEndpoint(), [
            'model' => $this->capabilities['embedding_model'] ?? 'text-embedding-3-small',
            'input' => $texts,
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException("Embedding request failed: {$response->body()}");
        }

        $data = $response->json();
        $embeddings = array_map(fn($item) => $item['embedding'], $data['data']);

        return $isBatch ? $embeddings : $embeddings[0];
    }

    public function embeddingDimension(): int
    {
        return match ($this->capabilities['embedding_model'] ?? null) {
            'text-embedding-3-large', 'text-embedding-ada-002' => 1536,
            'text-embedding-3-small' => 1536,
            default => 1536,
        };
    }

    public static function fromConfig(array $config): self
    {
        return new self(
            apiKey: $config['api_key'],
            model: $config['model'] ?? 'gpt-4o-mini',
            baseUrl: $config['base_url'] ?? null,
            capabilities: [
                'name' => 'openai',
                'features' => $config['features'] ?? ['chat', 'embeddings'],
                'embedding_model' => $config['embedding_model'] ?? 'text-embedding-3-small',
            ]
        );
    }
}
