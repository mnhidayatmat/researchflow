<?php

namespace Tests\Feature;

use App\Models\AiConversation;
use App\Models\AiProject;
use App\Models\User;
use App\Services\Ai\Cowork\AiCoworkService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class AiCoworkBrowserFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_browser_cowork_plan_and_complete_flow(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $project = AiProject::create([
            'user_id' => $user->id,
            'name' => 'Browser Cowork',
        ]);
        $conversation = AiConversation::create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'title' => 'Cowork Browser Session',
            'scope' => 'cowork',
        ]);

        $mock = Mockery::mock(AiCoworkService::class);
        $mock->shouldReceive('plan')
            ->once()
            ->andReturn([
                'operation' => 'update',
                'target_type' => 'file',
                'relative_path' => 'src/App.php',
                'content' => '<?php echo "updated";',
                'summary' => 'Update the selected file.',
                'clarification' => null,
            ]);
        $this->app->instance(AiCoworkService::class, $mock);

        $planResponse = $this->actingAs($user)->postJson("/api/ai/conversations/{$conversation->id}/cowork-plan", [
            'message' => 'Update App.php',
            'workspace_label' => 'Desktop/demo-app',
            'workspace_context' => [
                'workspace_label' => 'Desktop/demo-app',
                'root_name' => 'demo-app',
                'entries' => [['path' => 'src/App.php', 'type' => 'file']],
                'text_files' => [],
            ],
        ]);

        $planResponse->assertOk()
            ->assertJsonPath('plan.operation', 'update')
            ->assertJsonPath('conversation_meta.metadata.workspace_label', 'Desktop/demo-app');

        $completeResponse = $this->actingAs($user)->postJson("/api/ai/conversations/{$conversation->id}/cowork-complete", [
            'message' => 'Update App.php',
            'workspace_label' => 'Desktop/demo-app',
            'plan' => [
                'operation' => 'update',
                'target_type' => 'file',
                'relative_path' => 'src/App.php',
                'summary' => 'Update the selected file.',
            ],
            'execution_result' => [
                'operation' => 'update',
                'relative_path' => 'src/App.php',
                'summary' => 'Updated local file.',
                'preview' => '<?php echo "updated";',
            ],
        ]);

        $completeResponse->assertOk()
            ->assertJsonPath('conversation_meta.metadata.workspace_label', 'Desktop/demo-app')
            ->assertJsonPath('conversation.messages.1.metadata.operation', 'update');

        $conversation->refresh();
        $this->assertSame('cowork', $conversation->scope);
        $this->assertSame('Desktop/demo-app', $conversation->metadata['workspace_label']);
        $this->assertCount(2, $conversation->messages);
    }
}
