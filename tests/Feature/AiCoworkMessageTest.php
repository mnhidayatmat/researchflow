<?php

namespace Tests\Feature;

use App\Models\AiConversation;
use App\Models\AiProject;
use App\Models\User;
use App\Services\Ai\Cowork\AiCoworkService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class AiCoworkMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_cowork_message_endpoint_persists_workspace_metadata(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $project = AiProject::create([
            'user_id' => $user->id,
            'name' => 'Workspace Project',
        ]);
        $conversation = AiConversation::create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'title' => 'Cowork Session',
            'scope' => 'general',
        ]);

        $mock = Mockery::mock(AiCoworkService::class);
        $mock->shouldReceive('execute')
            ->once()
            ->andReturn([
                'message' => '**Cowork completed the request.**',
                'metadata' => [
                    'mode' => 'cowork',
                    'workspace_path' => base_path('resources/views/test.blade.php'),
                    'operation' => 'update',
                    'summary' => 'Updated file.',
                    'details' => [],
                ],
            ]);
        $this->app->instance(AiCoworkService::class, $mock);

        $response = $this->actingAs($user)->postJson("/api/ai/conversations/{$conversation->id}/cowork", [
            'message' => 'Update the Blade title.',
            'workspace_path' => 'resources/views/test.blade.php',
        ]);

        $response->assertOk()
            ->assertJsonPath('conversation_meta.scope', 'cowork')
            ->assertJsonPath('conversation_meta.metadata.mode', 'cowork')
            ->assertJsonPath('conversation_meta.metadata.workspace_path', base_path('resources/views/test.blade.php'));

        $conversation->refresh();

        $this->assertSame('cowork', $conversation->scope);
        $this->assertSame('cowork', $conversation->metadata['mode']);
        $this->assertSame(base_path('resources/views/test.blade.php'), $conversation->metadata['workspace_path']);
        $this->assertCount(2, $conversation->messages);
    }
}
