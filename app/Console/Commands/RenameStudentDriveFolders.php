<?php

namespace App\Console\Commands;

use App\Models\Student;
use App\Models\User;
use App\Services\UserStorageService;
use Google\Service\Drive\DriveFile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RenameStudentDriveFolders extends Command
{
    protected $signature = 'gdrive:rename-student-folders
                            {--dry-run : Show planned renames without applying}
                            {--supervisor= : Only process this supervisor user ID}';

    protected $description = 'Rename existing student-{id} folders on supervisor Google Drives to the student name.';

    public function handle(UserStorageService $userStorage): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $supervisorFilter = $this->option('supervisor');

        $query = DB::table('files')
            ->where('disk', 'google_drive')
            ->whereNotNull('storage_owner_id')
            ->select('storage_owner_id', 'student_id')
            ->distinct();

        if ($supervisorFilter) {
            $query->where('storage_owner_id', $supervisorFilter);
        }

        $pairs = $query->get();

        if ($pairs->isEmpty()) {
            $this->info('No Google Drive files found. Nothing to rename.');
            return self::SUCCESS;
        }

        $this->info(($dryRun ? '[DRY RUN] ' : '') . 'Processing ' . $pairs->count() . ' supervisor/student pair(s)...');

        $renamed = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($pairs as $pair) {
            $supervisor = User::find($pair->storage_owner_id);
            $student = Student::with('user')->find($pair->student_id);

            if (!$supervisor || !$student) {
                $this->warn("  skip: missing supervisor {$pair->storage_owner_id} or student {$pair->student_id}");
                $skipped++;
                continue;
            }

            $newName = trim((string) ($student->user?->name ?? ''));
            $newName = str_replace(['/', '\\'], '-', $newName);

            if ($newName === '') {
                $this->warn("  skip student {$student->id}: no user name");
                $skipped++;
                continue;
            }

            $oldName = 'student-' . $student->id;

            if ($newName === $oldName) {
                $skipped++;
                continue;
            }

            $service = $userStorage->googleDriveServiceFor($supervisor);
            if (!$service) {
                $this->warn("  skip supervisor {$supervisor->id} ({$supervisor->email}): Drive not connected");
                $skipped++;
                continue;
            }

            $profile = $userStorage->profileFor($supervisor);
            $rootId = $profile->google_drive_folder_id ?: 'root';

            try {
                $rfId = $this->findFolder($service, 'ResearchFlow', $rootId);
                if (!$rfId) {
                    $this->line("  skip: no ResearchFlow folder for supervisor {$supervisor->id}");
                    $skipped++;
                    continue;
                }

                $supFolderId = $this->findFolder($service, 'supervisor-' . $supervisor->id, $rfId);
                if (!$supFolderId) {
                    $this->line("  skip: no supervisor-{$supervisor->id} folder");
                    $skipped++;
                    continue;
                }

                $studentFolderId = $this->findFolder($service, $oldName, $supFolderId);
                if (!$studentFolderId) {
                    if ($this->findFolder($service, $newName, $supFolderId)) {
                        $this->line("  ok already: {$oldName} -> {$newName} (sup {$supervisor->id})");
                    } else {
                        $this->line("  skip: {$oldName} not found under supervisor {$supervisor->id}");
                    }
                    $skipped++;
                    continue;
                }

                if ($dryRun) {
                    $this->info("  plan: {$oldName} -> {$newName} (sup {$supervisor->id})");
                    $renamed++;
                    continue;
                }

                $service->files->update($studentFolderId, new DriveFile(['name' => $newName]));
                $this->info("  renamed: {$oldName} -> {$newName} (sup {$supervisor->id})");
                $renamed++;
            } catch (\Throwable $e) {
                $this->error("  error for student {$student->id} / supervisor {$supervisor->id}: " . $e->getMessage());
                $errors++;
            }
        }

        $this->newLine();
        $this->info(sprintf(
            '%s Done. %d %s, %d skipped, %d errors.',
            $dryRun ? '[DRY RUN]' : '',
            $renamed,
            $dryRun ? 'planned' : 'renamed',
            $skipped,
            $errors
        ));

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function findFolder($service, string $name, string $parentId): ?string
    {
        $escaped = addslashes($name);
        $query = "name = '{$escaped}' and mimeType = 'application/vnd.google-apps.folder' and trashed = false and '{$parentId}' in parents";

        $results = $service->files->listFiles([
            'q' => $query,
            'fields' => 'files(id, name)',
            'pageSize' => 1,
        ]);

        return $results->count() > 0 ? $results[0]->id : null;
    }
}
