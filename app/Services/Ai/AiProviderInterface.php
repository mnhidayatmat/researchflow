<?php

namespace App\Services\Ai;

use Illuminate\Contracts\Support\Arrayable;

interface AiProviderInterface extends Arrayable
{
    /**
     * Send a chat completion request.
     *
     * @param array $messages
     * @param array $options
     * @return string
     */
    public function chat(array $messages, array $options = []): string;

    /**
     * Stream a chat completion request.
     *
     * @param array $messages
     * @param callable $callback
     * @param array $options
     * @return void
     */
    public function streamChat(array $messages, callable $callback, array $options = []): void;

    /**
     * Generate embeddings for text.
     *
     * @param string|array $texts
     * @return array
     */
    public function embed(string|array $texts): array;

    /**
     * Get the dimension count of embeddings.
     *
     * @return int
     */
    public function embeddingDimension(): int;

    /**
     * Get the provider name/slug.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get the model name.
     *
     * @return string
     */
    public function getModel(): string;

    /**
     * Check if the provider supports a specific feature.
     *
     * @param string $feature
     * @return bool
     */
    public function supports(string $feature): bool;
}
