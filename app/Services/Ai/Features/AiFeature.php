<?php

namespace App\Services\Ai\Features;

use App\Services\Ai\AiProviderInterface;
use App\Services\Ai\AiServiceFactory;

abstract class AiFeature
{
    protected ?AiProviderInterface $provider;

    public function __construct(?AiProviderInterface $provider = null)
    {
        $this->provider = $provider ?? AiServiceFactory::getProvider();
    }

    /**
     * Execute the feature and return the result.
     */
    abstract public function execute(...$args): string|array;

    /**
     * Build the system prompt for this feature.
     */
    abstract protected function buildSystemPrompt(): string;

    /**
     * Build the user message for this feature.
     */
    abstract protected function buildUserMessage(...$args): string;

    /**
     * Call the AI provider with the built messages.
     */
    protected function call(array $messages, array $options = []): string
    {
        if (!$this->provider) {
            throw new \RuntimeException('AI provider not configured.');
        }

        return $this->provider->chat($messages, $options);
    }
}
