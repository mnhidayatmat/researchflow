<?php

namespace App\Services\Ai;

use Illuminate\Http\Client\PendingRequest;

/**
 * Z.AI Provider - supports both Anthropic-compatible (Coding Plan) and OpenAI-compatible endpoints.
 *
 * GLM Coding Plan uses: https://api.z.ai/api/anthropic (Anthropic Messages API format)
 * Standard API Plan uses: https://api.z.ai/api/paas/v4 (OpenAI-compatible format)
 *
 * Default is the Anthropic endpoint (Coding Plan) since that's the most common subscription.
 */
class ZAiProvider extends BaseAiProvider
{
    /**
     * Determine if we're using the Anthropic-compatible endpoint.
     */
    protected function isAnthropicMode(): bool
    {
        $baseUrl = $this->baseUrl ?? 'https://api.z.ai/api/anthropic';

        return str_contains($baseUrl, '/anthropic');
    }

    protected function headers(): array
    {
        if ($this->isAnthropicMode()) {
            return [
                'Content-Type' => 'application/json',
                'x-api-key' => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'Accept-Language' => 'en-US,en',
            ];
        }

        return [
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer {$this->apiKey}",
            'Accept-Language' => 'en-US,en',
        ];
    }

    protected function httpClient(): PendingRequest
    {
        return parent::httpClient()->withoutVerifying()->timeout(90);
    }

    public function getChatEndpoint(): string
    {
        $baseUrl = rtrim($this->baseUrl ?? 'https://api.z.ai/api/anthropic', '/');

        if ($this->isAnthropicMode()) {
            // Anthropic endpoint: /v1/messages
            if (str_ends_with($baseUrl, '/v1')) {
                return $baseUrl . '/messages';
            }
            return $baseUrl . '/v1/messages';
        }

        // OpenAI endpoint: /paas/v4/chat/completions
        if (str_ends_with($baseUrl, '/paas/v4')) {
            return $baseUrl . '/chat/completions';
        }

        return $baseUrl . '/paas/v4/chat/completions';
    }

    public function getEmbeddingEndpoint(): string
    {
        $baseUrl = rtrim($this->baseUrl ?? 'https://api.z.ai/api', '/');

        if (str_ends_with($baseUrl, '/paas/v4')) {
            return $baseUrl . '/embeddings';
        }

        return $baseUrl . '/paas/v4/embeddings';
    }

    protected function formatMessages(array $messages): array
    {
        if ($this->isAnthropicMode()) {
            // Anthropic format: separate system prompt from messages
            $formatted = [];
            foreach ($messages as $msg) {
                if ($msg['role'] === 'system') {
                    continue; // handled in buildChatPayload
                }
                $formatted[] = [
                    'role' => $msg['role'],
                    'content' => $msg['content'],
                ];
            }
            return $formatted;
        }

        // OpenAI format: pass through as-is
        return $messages;
    }

    protected function buildChatPayload(array $messages, array $options): array
    {
        if ($this->isAnthropicMode()) {
            // Extract system prompt
            $systemPrompt = null;
            foreach ($messages as $msg) {
                if ($msg['role'] === 'system') {
                    $systemPrompt = $msg['content'];
                    break;
                }
            }

            $payload = [
                'model' => $this->model,
                'messages' => $this->formatMessages($messages),
                'max_tokens' => $options['max_tokens'] ?? 4096,
            ];

            if (isset($options['temperature'])) {
                $payload['temperature'] = $options['temperature'];
            }

            if ($systemPrompt) {
                $payload['system'] = $systemPrompt;
            }

            if (isset($options['stream']) && $options['stream']) {
                $payload['stream'] = true;
            }

            return $payload;
        }

        // OpenAI format
        return parent::buildChatPayload($messages, $options);
    }

    protected function extractContent(array $response): string
    {
        if ($this->isAnthropicMode()) {
            // Anthropic format: { content: [{ type: "text", text: "..." }] }
            if (isset($response['error'])) {
                throw new \RuntimeException($response['error']['message'] ?? 'Z.AI API error');
            }

            return $response['content'][0]['text'] ?? '';
        }

        // OpenAI format: { choices: [{ message: { content: "..." } }] }
        $content = $response['choices'][0]['message']['content'] ?? null;

        if ($content === null) {
            $toolCalls = $response['choices'][0]['message']['tool_calls'] ?? [];
            if (!empty($toolCalls)) {
                return '';
            }
            throw new \RuntimeException('Invalid response from Z.Ai API: ' . json_encode($response));
        }

        return $content;
    }

    protected function extractStreamChunk(string $chunk): ?string
    {
        $line = trim($chunk);

        if (empty($line) || $line === 'data: [DONE]' || str_starts_with($line, ':')) {
            return null;
        }

        if (str_starts_with($line, 'data: ')) {
            $line = substr($line, 6);
        }

        $data = json_decode($line, true);
        if (!$data) {
            return null;
        }

        if ($this->isAnthropicMode()) {
            // Anthropic streaming: content_block_delta events
            if (($data['type'] ?? '') === 'content_block_delta') {
                return $data['delta']['text'] ?? null;
            }
            return null;
        }

        // OpenAI streaming: choices[0].delta.content
        return $data['choices'][0]['delta']['content'] ?? null;
    }

    public function embed(string|array $texts): array
    {
        $endpoint = $this->getEmbeddingEndpoint();
        $embeddingModel = $this->capabilities['embedding_model'] ?? 'embedding-2';

        $textsArray = is_string($texts) ? [$texts] : $texts;
        $embeddings = [];

        foreach ($textsArray as $text) {
            $response = $this->httpClient()->post($endpoint, [
                'model' => $embeddingModel,
                'input' => $text,
            ]);

            if (!$response->successful()) {
                throw new \RuntimeException(
                    "Z.Ai embedding request failed: {$response->status()} {$response->body()}"
                );
            }

            $data = $response->json();

            if (!isset($data['data'][0]['embedding'])) {
                throw new \RuntimeException('Invalid embedding response from Z.Ai API: ' . $response->body());
            }

            $embeddings[] = $data['data'][0]['embedding'];
        }

        return $embeddings;
    }

    public function streamChat(array $messages, callable $callback, array $options = []): void
    {
        $payload = $this->buildChatPayload($messages, ['stream' => true, ...$options]);

        $ch = curl_init($this->getChatEndpoint());
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => $this->curlHeaders(),
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_TIMEOUT => 90,
            CURLOPT_WRITEFUNCTION => function ($ch, $data) use ($callback) {
                $lines = explode("\n", $data);
                foreach ($lines as $line) {
                    $content = $this->extractStreamChunk($line);
                    if ($content !== null) {
                        $callback($content);
                    }
                }
                return strlen($data);
            },
        ]);

        curl_exec($ch);
        curl_close($ch);
    }

    public function embeddingDimension(): int
    {
        return 1024;
    }

    public static function fromConfig(array $config): self
    {
        return new self(
            apiKey: $config['api_key'],
            model: $config['model'] ?? 'glm-4.7',
            baseUrl: $config['base_url'] ?? 'https://api.z.ai/api/anthropic',
            capabilities: [
                'name' => 'zai',
                'features' => $config['features'] ?? ['chat', 'embeddings'],
                'embedding_model' => $config['embedding_model'] ?? 'embedding-2',
            ]
        );
    }
}
