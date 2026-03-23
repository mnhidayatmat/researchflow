<?php

namespace App\Services\Ai;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

abstract class BaseAiProvider implements AiProviderInterface
{
    protected string $apiKey;
    protected string $model;
    protected ?string $baseUrl = null;
    protected array $capabilities = [];
    protected int $timeout = 120;

    protected function __construct(
        string $apiKey,
        string $model,
        ?string $baseUrl = null,
        array $capabilities = []
    ) {
        $this->apiKey = $apiKey;
        $this->model = $model;
        $this->baseUrl = $baseUrl;
        $this->capabilities = $capabilities;
    }

    /**
     * Create a configured HTTP client.
     */
    protected function httpClient(): PendingRequest
    {
        return Http::timeout($this->timeout)
            ->withOptions($this->httpOptions())
            ->withHeaders($this->headers());
    }

    protected function httpOptions(): array
    {
        $caBundle = env('AI_CA_BUNDLE');
        $verifySsl = filter_var(env('AI_SSL_VERIFY', true), FILTER_VALIDATE_BOOL);

        if (is_string($caBundle) && $caBundle !== '') {
            return ['verify' => $caBundle];
        }

        if (!$verifySsl) {
            return ['verify' => false];
        }

        if (app()->environment('local')) {
            return ['verify' => false];
        }

        return [];
    }

    /**
     * Get default headers for requests.
     */
    protected function headers(): array
    {
        return [
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Format messages for the provider's API.
     */
    abstract protected function formatMessages(array $messages): array;

    /**
     * Extract the response content from the provider's API response.
     */
    abstract protected function extractContent(array $response): string;

    /**
     * Extract streaming chunks from the provider's SSE response.
     */
    abstract protected function extractStreamChunk(string $chunk): ?string;

    public function chat(array $messages, array $options = []): string
    {
        if (empty($messages)) {
            throw new RuntimeException('Messages array cannot be empty.');
        }

        $response = $this->sendRequest($this->buildChatPayload($messages, $options));

        return $this->extractContent($response);
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
            CURLOPT_WRITEFUNCTION => function ($ch, $data) use ($callback) {
                $lines = explode("\n", $data);
                foreach ($lines as $line) {
                    if (empty($line) || $line === ':OPENAI_CARDINALITY') {
                        continue;
                    }
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

    protected function sendRequest(array $payload): array
    {
        $response = $this->httpClient()->post(
            $this->getChatEndpoint(),
            $payload
        );

        if (!$response->successful()) {
            throw new RuntimeException(
                "AI request failed: {$response->status()} {$response->body()}",
                $response->status()
            );
        }

        return $response->json();
    }

    abstract protected function getChatEndpoint(): string;
    abstract protected function getEmbeddingEndpoint(): string;

    protected function buildChatPayload(array $messages, array $options): array
    {
        return [
            'model' => $this->model,
            'messages' => $this->formatMessages($messages),
            ...$options,
        ];
    }

    protected function curlHeaders(): array
    {
        $headers = [];
        foreach ($this->headers() as $key => $value) {
            $headers[] = "$key: $value";
        }
        return $headers;
    }

    public function getName(): string
    {
        return $this->capabilities['name'] ?? 'unknown';
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function supports(string $feature): bool
    {
        return in_array($feature, $this->capabilities['features'] ?? []);
    }

    public function toArray(): array
    {
        return [
            'name' => $this->getName(),
            'model' => $this->getModel(),
            'supports' => $this->capabilities['features'] ?? [],
        ];
    }

    /**
     * Create provider instance from config array.
     */
    abstract public static function fromConfig(array $config): self;
}
