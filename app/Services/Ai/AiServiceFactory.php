<?php

namespace App\Services\Ai;

use App\Models\AiProvider as AiProviderModel;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Cache;

class AiServiceFactory
{
    protected static array $providers = [
        'openai' => OpenAiProvider::class,
        'gemini' => GeminiProvider::class,
        'anthropic' => AnthropicProvider::class,
        'zai' => ZAiProvider::class,
        'custom' => OpenAiProvider::class, // Custom uses OpenAI-compatible API
    ];

    /**
     * Get the default active provider.
     */
    public static function getProvider(): ?AiProviderInterface
    {
        // Don't cache the provider model to avoid serialization issues
        $provider = AiProviderModel::where('is_active', true)
            ->where('is_default', true)
            ->first();

        if (!$provider) {
            return null;
        }

        return self::createProvider($provider);
    }

    /**
     * Get a provider by slug.
     */
    public static function getBySlug(string $slug): ?AiProviderInterface
    {
        $provider = AiProviderModel::where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (!$provider) {
            return null;
        }

        return self::createProvider($provider);
    }

    /**
     * Get all active providers.
     */
    public static function getAllActive(): array
    {
        $providers = AiProviderModel::where('is_active', true)->get();

        return $providers->map(fn($p) => self::createProvider($p))->filter()->toArray();
    }

    /**
     * Create a provider instance from a model.
     */
    protected static function createProvider(AiProviderModel $model): ?AiProviderInterface
    {
        $providerClass = self::$providers[$model->slug] ?? null;

        if (!$providerClass || !class_exists($providerClass)) {
            return null;
        }

        return $providerClass::fromConfig([
            'api_key' => self::resolveApiKey($model),
            'model' => $model->model,
            'base_url' => $model->base_url,
            'features' => array_keys($model->settings['features'] ?? []),
            'embedding_model' => $model->settings['embedding_model'] ?? null,
        ]);
    }

    /**
     * Register a custom provider type.
     */
    public static function registerProvider(string $slug, string $class): void
    {
        self::$providers[$slug] = $class;
    }

    /**
     * Clear the provider cache.
     */
    public static function clearCache(): void
    {
        Cache::forget('ai.default_provider');
    }

    protected static function resolveApiKey(AiProviderModel $model): ?string
    {
        try {
            return $model->api_key;
        } catch (DecryptException) {
            return $model->getRawOriginal('api_key');
        }
    }
}
