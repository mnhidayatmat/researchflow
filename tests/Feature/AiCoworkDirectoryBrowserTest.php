<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiCoworkDirectoryBrowserTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_browse_allowed_cowork_directories(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $directory = storage_path('app/testing-cowork-browser');
        $child = $directory . DIRECTORY_SEPARATOR . 'child-folder';

        if (!is_dir($child)) {
            mkdir($child, 0777, true);
        }

        try {
            $normalizedDirectory = str_replace('/', DIRECTORY_SEPARATOR, $directory);
            $response = $this->actingAs($user)->getJson('/api/ai/cowork/directories?path=' . urlencode($directory));

            $response->assertOk()
                ->assertJsonPath('path', $normalizedDirectory)
                ->assertJsonPath('entries.0.name', 'child-folder')
                ->assertJsonPath('entries.0.type', 'directory');
        } finally {
            if (is_dir($child)) {
                rmdir($child);
            }
            if (is_dir($directory)) {
                rmdir($directory);
            }
        }
    }
}
