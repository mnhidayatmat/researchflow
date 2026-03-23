<?php

namespace App\Services\Ai\Cowork;

use App\Models\User;
use App\Services\Ai\AiProviderInterface;
use App\Services\Ai\AiServiceFactory;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AiCoworkService
{
    public function __construct(protected ?LocalWorkspaceService $workspace = null)
    {
    }

    public function plan(User $user, string $instruction, array $workspaceContext): array
    {
        if (!$this->hasPremiumAccess($user)) {
            throw new RuntimeException('Cowork is a premium feature. Activate a subscription to use it.');
        }

        $provider = AiServiceFactory::getBySlug('zai');
        if (!$provider) {
            throw new RuntimeException('Z.Ai is not configured or active. Enable it in Admin > AI settings first.');
        }

        return $this->buildPlan($provider, $instruction, $workspaceContext);
    }

    public function execute(User $user, string $instruction, string $workspacePath): array
    {
        if (!$this->workspace) {
            throw new RuntimeException('Server-side workspace access is not available.');
        }

        $context = $this->workspace->inspect($workspacePath);
        $plan = $this->plan($user, $instruction, [
            'workspace_label' => $workspacePath,
            'root_name' => basename($workspacePath),
            'entries' => $context['directory_entries'] ?? [],
            'text_files' => $context['content']
                ? [[
                    'path' => basename($workspacePath),
                    'size' => strlen($context['content']),
                    'content' => $context['content'],
                ]]
                : [],
            'type' => $context['type'] ?? null,
        ]);
        $result = $this->workspace->execute([
            'operation' => $plan['operation'] ?? 'error',
            'target_type' => $plan['target_type'] ?? 'file',
            'content' => $plan['content'] ?? null,
            'clarification' => $plan['clarification'] ?? null,
        ], $workspacePath);

        return [
            'message' => $this->formatAssistantMessage($plan, $result),
            'metadata' => [
                'mode' => 'cowork',
                'workspace_path' => $result['path'],
                'operation' => $result['operation'],
                'summary' => $result['summary'],
                'details' => $result['details'],
            ],
        ];
    }

    protected function buildPlan(AiProviderInterface $provider, string $instruction, array $workspaceContext): array
    {
        $contextPayload = [
            'instruction' => $instruction,
            'workspace' => $workspaceContext,
        ];

        $response = $provider->chat([
            [
                'role' => 'system',
                'content' => implode("\n", [
                    'You are Cowork, a local workspace operator.',
                    'Return JSON only.',
                    'Pick exactly one operation: read, list, create, update, delete, or error.',
                    'Pick exactly one target_type: file or directory.',
                    'Use a relative path inside the selected local workspace.',
                    'Only create or update when text content is appropriate.',
                    'For create/update, include the full file content in the `content` field.',
                    'For read/list/delete, set `content` to null.',
                    'If the request is ambiguous or unsafe, return operation `error` and explain in `clarification`.',
                    'Schema: {"operation":"","target_type":"","relative_path":"","content":null,"summary":"","clarification":null}',
                ]),
            ],
            [
                'role' => 'user',
                'content' => json_encode($contextPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            ],
        ], [
            'temperature' => 0.2,
            'max_tokens' => 3000,
        ]);

        $decoded = $this->decodeJson($response);
        if (($decoded['operation'] ?? 'error') === 'error') {
            throw new RuntimeException($decoded['clarification'] ?? 'Cowork could not determine a safe operation.');
        }

        return $decoded;
    }

    protected function decodeJson(string $response): array
    {
        $payload = trim($response);
        if (preg_match('/```(?:json)?\s*(.*?)\s*```/is', $payload, $matches)) {
            $payload = $matches[1];
        }

        $decoded = json_decode($payload, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('Z.Ai returned an invalid Cowork plan.');
        }

        return $decoded;
    }

    protected function formatAssistantMessage(array $plan, array $result): string
    {
        $lines = [
            '**Cowork completed the request.**',
            '',
            '- Operation: ' . ucfirst($result['operation']),
            '- Path: `' . $result['path'] . '`',
            '- Summary: ' . ($plan['summary'] ?? $result['summary']),
        ];

        if (!empty($result['preview'])) {
            $lines[] = '';
            $lines[] = 'Preview:';
            $lines[] = '```';
            $lines[] = $result['preview'];
            $lines[] = '```';
        }

        return implode("\n", $lines);
    }

    protected function hasPremiumAccess(User $user): bool
    {
        if (!config('services.cowork.require_premium', app()->environment('production'))) {
            return true;
        }

        if ($user->role === 'admin') {
            return true;
        }

        return DB::table('subscriptions')
            ->where('user_id', $user->id)
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>', now());
            })
            ->where(function ($query) {
                $query->whereIn('stripe_status', ['active', 'trialing'])
                    ->orWhere(function ($trialQuery) {
                        $trialQuery->whereNull('stripe_status')
                            ->where('trial_ends_at', '>', now());
                    });
            })
            ->exists();
    }
}
