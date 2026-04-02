<?php

namespace App\Console\Commands;

use App\Models\AiProvider;
use Illuminate\Console\Command;

class SetupAiProvider extends Command
{
    protected $signature = 'ai:setup
                            {--slug=zai : Provider slug (openai, gemini, anthropic, zai, custom)}
                            {--key= : API key}
                            {--model=glm-4.7 : Model name}
                            {--base-url=https://api.z.ai/api/anthropic : API base URL}
                            {--activate : Set as active and default}';

    protected $description = 'Configure an AI provider with API key and settings';

    public function handle(): int
    {
        $slug    = $this->option('slug');
        $apiKey  = $this->option('key');
        $model   = $this->option('model');
        $baseUrl = $this->option('base-url');

        if (empty($apiKey)) {
            $apiKey = $this->secret('Enter the API key');
        }

        if (empty($apiKey)) {
            $this->error('API key is required.');
            return 1;
        }

        // Find or create the provider
        $provider = AiProvider::where('slug', $slug)->first();

        if ($provider) {
            $this->info("Updating existing {$slug} provider (ID: {$provider->id})...");
        } else {
            $this->info("Creating new {$slug} provider...");
            $provider = new AiProvider();
        }

        $names = [
            'openai' => 'OpenAI', 'gemini' => 'Google Gemini',
            'anthropic' => 'Anthropic Claude', 'zai' => 'Z.Ai',
            'custom' => 'Custom Provider',
        ];

        $provider->fill([
            'name'        => $provider->name ?: ($names[$slug] ?? $slug),
            'slug'        => $slug,
            'api_key'     => $apiKey,
            'model'       => $model,
            'base_url'    => $baseUrl,
            'is_active'   => true,
            'is_default'  => true,
            'temperature' => $provider->temperature ?? 0.7,
            'max_tokens'  => $provider->max_tokens ?? 4096,
            'settings'    => $provider->settings ?? ['features' => []],
        ]);

        // If setting as default, unset other defaults
        if ($this->option('activate') || true) {
            AiProvider::where('id', '!=', $provider->id ?? 0)->update(['is_default' => false]);
        }

        $provider->save();

        // Verify the key was saved correctly
        $fresh = AiProvider::find($provider->id);
        try {
            $key = $fresh->api_key;
            $preview = substr($key, 0, 8) . '...' . substr($key, -4);
            $this->info("API key saved and verified: {$preview}");
        } catch (\Exception $e) {
            $this->error("API key encryption error: {$e->getMessage()}");
            return 1;
        }

        $this->table(['Field', 'Value'], [
            ['ID', $fresh->id],
            ['Slug', $fresh->slug],
            ['Model', $fresh->model],
            ['Base URL', $fresh->base_url],
            ['Active', $fresh->is_active ? 'Yes' : 'No'],
            ['Default', $fresh->is_default ? 'Yes' : 'No'],
        ]);

        // Test the connection
        $this->info('Testing connection...');
        try {
            $providerInstance = \App\Services\Ai\ZAiProvider::fromConfig([
                'api_key'  => $key,
                'model'    => $fresh->model,
                'base_url' => $fresh->base_url,
            ]);

            $response = $providerInstance->chat([
                ['role' => 'user', 'content' => 'Reply with exactly: CONNECTION_OK'],
            ], ['max_tokens' => 20]);

            $this->info("Connection OK! Response: {$response}");
        } catch (\Exception $e) {
            $this->error("Connection failed: {$e->getMessage()}");
            return 1;
        }

        \App\Services\Ai\AiServiceFactory::clearCache();
        $this->info('AI provider cache cleared. Done!');

        return 0;
    }
}
