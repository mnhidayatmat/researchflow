<?php

namespace Tests\Feature;

use App\Services\Ai\Cowork\LocalWorkspaceService;
use Illuminate\Filesystem\Filesystem;
use Tests\TestCase;

class LocalWorkspaceServiceTest extends TestCase
{
    public function test_it_can_create_read_update_list_and_delete_text_files_in_storage_app(): void
    {
        $service = new LocalWorkspaceService(new Filesystem());
        $directory = storage_path('app/testing-cowork');
        $file = $directory . DIRECTORY_SEPARATOR . 'notes.md';

        try {
            $service->execute([
                'operation' => 'create',
                'target_type' => 'file',
                'content' => "# Draft\n\nInitial copy.",
            ], $file);

            $read = $service->execute([
                'operation' => 'read',
                'target_type' => 'file',
                'content' => null,
            ], $file);

            $list = $service->execute([
                'operation' => 'list',
                'target_type' => 'directory',
                'content' => null,
            ], $directory);

            $update = $service->execute([
                'operation' => 'update',
                'target_type' => 'file',
                'content' => "# Draft\n\nUpdated copy.",
            ], $file);

            $this->assertSame('read', $read['operation']);
            $this->assertStringContainsString('Initial copy.', $read['preview']);
            $this->assertSame('list', $list['operation']);
            $this->assertStringContainsString('notes.md', $list['preview']);
            $this->assertSame('update', $update['operation']);
            $this->assertStringContainsString('Updated copy.', file_get_contents($file));
        } finally {
            if (is_file($file)) {
                unlink($file);
            }
            if (is_dir($directory)) {
                rmdir($directory);
            }
        }
    }

    public function test_it_blocks_protected_project_paths(): void
    {
        $service = new LocalWorkspaceService(new Filesystem());

        $this->expectExceptionMessage('protected project paths');

        $service->resolvePath('.env');
    }
}
