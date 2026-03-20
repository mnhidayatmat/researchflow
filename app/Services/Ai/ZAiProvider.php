<?php

namespace App\Services\Ai;

use Illuminate\Http\Client\PendingRequest;

class ZAiProvider extends BaseAiProvider
{
    protected function headers(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer {$this->apiKey}",
            'Accept-Language' => 'en-US,en',
        ];
    }

    /**
     * Create a configured HTTP client with SSL verification disabled for development.
     */
    protected function httpClient(): PendingRequest
    {
        return parent::httpClient()->withoutVerifying();
    }

    public function getChatEndpoint(): string
    {
        // Z.Ai uses OpenAI-compatible API at /api/paas/v4/chat/completions
        $baseUrl = $this->baseUrl ?? 'https://api.z.ai/api/paas/v4';
        return rtrim($baseUrl, '/') . '/chat/completions';
    }

    public function getEmbeddingEndpoint(): string
    {
        // Z.Ai embeddings endpoint
        $baseUrl = $this->baseUrl ?? 'https://api.z.ai/api/paas/v4';
        return rtrim($baseUrl, '/') . '/embeddings';
    }

    protected function formatMessages(array $messages): array
    {
        // Z.Ai uses OpenAI-compatible format
        return $messages;
    }

    protected function extractContent(array $response): string
    {
        if (!isset($response['choices'][0]['message']['content'])) {
            throw new \RuntimeException('Invalid response from Z.Ai API: ' . json_encode($response));
        }

        return $response['choices'][0]['message']['content'];
    }

    protected function extractStreamChunk(string $chunk): ?string
    {
        $data = json_decode($chunk, true);

        if ($data && isset($data['choices'][0]['delta']['content'])) {
            return $data['choices'][0]['delta']['content'];
        }

        return null;
    }

    public function embed(string|array $texts): array
    {
        $endpoint = $this->getEmbeddingEndpoint();
        $embeddingModel = $this->capabilities['embedding_model'] ?? 'embedding-2';

        // Normalize to array
        $textsArray = is_string($texts) ? [$texts] : $texts;
        $embeddings = [];

        foreach ($textsArray as $text) {
            $response = $this->httpClient()->post($endpoint, [
                'headers' => [
                    'Authorization' => "Bearer {$this->apiKey}",
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => $embeddingModel,
                    'input' => $text,
                ],
            ]);

            $data = json_decode($response->body(), true);

            if (!isset($data['data'][0]['embedding'])) {
                throw new \RuntimeException('Invalid embedding response from Z.Ai API');
            }

            $embeddings[] = $data['data'][0]['embedding'];
        }

        return $embeddings;
    }

    public function embeddingDimension(): int
    {
        // Z.Ai embedding-2 model has 1024 dimensions
        return 1024;
    }

    public static function fromConfig(array $config): self
    {
        return new self(
            apiKey: $config['api_key'],
            model: $config['model'] ?? 'glm-4.6',
            baseUrl: $config['base_url'] ?? 'https://api.z.ai/api/paas/v4',
            capabilities: [
                'name' => 'zai',
                'features' => $config['features'] ?? ['chat', 'embeddings'],
                'embedding_model' => $config['embedding_model'] ?? 'embedding-2',
            ]
        );
    }
}
