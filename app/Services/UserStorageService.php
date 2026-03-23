<?php

namespace App\Services;

use App\Models\ProgressReport;
use App\Models\Student;
use App\Models\User;
use App\Models\UserStorageSetting;
use Google\Client as GoogleClient;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserStorageService
{
    public function profileFor(User $user): UserStorageSetting
    {
        return $user->storageProfile()->firstOrCreate([], [
            'storage_disk' => 'local',
        ]);
    }

    public function canUseGoogleDrive(User $user): bool
    {
        $profile = $this->profileFor($user);

        return $this->hasGoogleOAuthConfig()
            && !empty($profile->google_drive_refresh_token);
    }

    public function hasGoogleOAuthConfig(): bool
    {
        return !empty(Config::get('services.google_drive.client_id'))
            && !empty(Config::get('services.google_drive.client_secret'));
    }

    public function authorizationUrlFor(User $user, string $redirectRoute): string
    {
        if (!$this->hasGoogleOAuthConfig()) {
            throw new \RuntimeException('Google Drive OAuth is not configured for this application.');
        }

        $client = $this->buildGoogleClient();
        $state = Str::random(40);

        session([
            'google_drive_oauth_state' => $state,
            'google_drive_oauth_user_id' => $user->id,
            'google_drive_oauth_redirect_route' => $redirectRoute,
        ]);

        $client->setState($state);
        $client->setPrompt('consent select_account');

        return $client->createAuthUrl();
    }

    public function handleOAuthCallback(User $user, string $code, string $state): ?string
    {
        abort_unless(
            session('google_drive_oauth_state') === $state
                && (int) session('google_drive_oauth_user_id') === $user->id,
            403,
            'Invalid Google Drive authorization state.'
        );

        $redirectRoute = session('google_drive_oauth_redirect_route');

        $client = $this->buildGoogleClient();
        $token = $client->fetchAccessTokenWithAuthCode($code);

        if (!empty($token['error'])) {
            throw new \RuntimeException($token['error_description'] ?? $token['error']);
        }

        $profile = $this->profileFor($user);
        $refreshToken = $token['refresh_token'] ?? $profile->google_drive_refresh_token;

        if (!$refreshToken) {
            throw new \RuntimeException('Google did not return a refresh token. Please connect again and grant consent.');
        }

        $profile->update([
            'storage_disk' => 'google_drive',
            'google_drive_refresh_token' => $refreshToken,
        ]);

        session()->forget([
            'google_drive_oauth_state',
            'google_drive_oauth_user_id',
            'google_drive_oauth_redirect_route',
        ]);

        return $redirectRoute;
    }

    public function disconnectGoogleDrive(User $user): void
    {
        $profile = $this->profileFor($user);
        $profile->update([
            'google_drive_refresh_token' => null,
            'google_drive_folder_id' => null,
            'storage_disk' => 'local',
        ]);
    }

    public function uploadReportAttachment(UploadedFile $file, Student $student, User $storageOwner): array
    {
        $profile = $this->profileFor($storageOwner);
        $disk = $profile->storage_disk ?: 'local';

        return $disk === 'google_drive'
            ? $this->uploadToGoogleDrive($file, $student, $storageOwner, $profile)
            : $this->uploadToLocal($file, $student, $storageOwner);
    }

    public function deleteReportAttachment(ProgressReport $report): void
    {
        if (!$report->attachment_path || !$report->attachment_disk) {
            return;
        }

        if ($report->attachment_disk === 'local') {
            Storage::disk('local')->delete($report->attachment_path);
            return;
        }

        $owner = $report->attachmentStorageOwner;
        if (!$owner) {
            return;
        }

        $service = $this->createGoogleDriveService($owner);
        if ($service) {
            $service->files->delete($report->attachment_path);
        }
    }

    public function downloadReportAttachment(ProgressReport $report): StreamedResponse
    {
        if ($report->attachment_disk === 'local') {
            return Storage::disk('local')->download($report->attachment_path, $report->attachment_original_name);
        }

        $owner = $report->attachmentStorageOwner;
        abort_unless($owner, 404, 'Attachment storage owner not found.');

        $service = $this->createGoogleDriveService($owner);
        abort_unless($service, 422, 'Google Drive is not configured for this supervisor.');

        $response = $service->files->get($report->attachment_path, ['alt' => 'media']);
        $stream = $response->getBody();

        return response()->streamDownload(function () use ($stream) {
            while (!$stream->eof()) {
                echo $stream->read(1024 * 8);
            }
        }, $report->attachment_original_name, [
            'Content-Type' => $report->attachment_mime_type ?: 'application/octet-stream',
        ]);
    }

    public function testConnection(User $user): array
    {
        $profile = $this->profileFor($user);

        if ($profile->storage_disk !== 'google_drive') {
            return [
                'success' => true,
                'message' => 'Local storage selected. No external authentication required.',
            ];
        }

        if (!$this->canUseGoogleDrive($user)) {
            return [
                'success' => false,
                'message' => $this->hasGoogleOAuthConfig()
                    ? 'Google Drive is not connected yet. Please sign in with Google first.'
                    : 'Google Drive OAuth is not configured for this application.',
            ];
        }

        try {
            $service = $this->createGoogleDriveService($user);
            if (!$service) {
                return ['success' => false, 'message' => 'Unable to create Google Drive client.'];
            }

            $about = $service->about->get(['fields' => 'user,storageQuota']);

            return [
                'success' => true,
                'message' => 'Connected to Google Drive as ' . ($about->getUser()?->getEmailAddress() ?? 'unknown user') . '.',
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Google Drive connection failed: ' . $e->getMessage(),
            ];
        }
    }

    protected function uploadToLocal(UploadedFile $file, Student $student, User $storageOwner): array
    {
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = "reports/owner-{$storageOwner->id}/student-{$student->id}/{$filename}";
        $storedPath = $file->storeAs(dirname($path), basename($path), 'local');

        return [
            'attachment_original_name' => $file->getClientOriginalName(),
            'attachment_mime_type' => $file->getMimeType(),
            'attachment_size' => $file->getSize(),
            'attachment_disk' => 'local',
            'attachment_path' => $storedPath,
            'attachment_storage_owner_id' => $storageOwner->id,
        ];
    }

    protected function uploadToGoogleDrive(UploadedFile $file, Student $student, User $storageOwner, UserStorageSetting $profile): array
    {
        if (!$this->canUseGoogleDrive($storageOwner)) {
            throw new \RuntimeException('Assigned supervisor has not configured Google Drive yet.');
        }

        $service = $this->createGoogleDriveService($storageOwner);
        if (!$service) {
            throw new \RuntimeException('Unable to initialize Google Drive service.');
        }

        $folderId = $this->ensureGoogleDriveFolder(
            $service,
            [
                'ResearchFlow',
                'supervisor-' . $storageOwner->id,
                'student-' . $student->id,
                'reports',
            ],
            $profile->google_drive_folder_id ?: 'root'
        );

        $driveFile = new DriveFile([
            'name' => $file->getClientOriginalName(),
            'parents' => [$folderId],
        ]);

        $createdFile = $service->files->create($driveFile, [
            'data' => file_get_contents($file->getRealPath()),
            'mimeType' => $file->getMimeType(),
            'uploadType' => 'multipart',
            'fields' => 'id',
        ]);

        return [
            'attachment_original_name' => $file->getClientOriginalName(),
            'attachment_mime_type' => $file->getMimeType(),
            'attachment_size' => $file->getSize(),
            'attachment_disk' => 'google_drive',
            'attachment_path' => $createdFile->id,
            'attachment_storage_owner_id' => $storageOwner->id,
        ];
    }

    protected function createGoogleDriveService(User $user): ?Drive
    {
        $profile = $this->profileFor($user);
        if (!$this->canUseGoogleDrive($user)) {
            return null;
        }

        $client = $this->buildGoogleClient();
        $client->fetchAccessTokenWithRefreshToken($profile->google_drive_refresh_token);

        return new Drive($client);
    }

    public function googleDriveServiceFor(User $user): ?Drive
    {
        return $this->createGoogleDriveService($user);
    }

    protected function ensureGoogleDriveFolder(Drive $service, array $segments, string $rootFolderId): string
    {
        $parentId = $rootFolderId;

        foreach ($segments as $segment) {
            $parentId = $this->findOrCreateGoogleDriveFolder($service, $segment, $parentId);
        }

        return $parentId;
    }

    public function ensureDriveFolder(Drive $service, array $segments, string $rootFolderId = 'root'): string
    {
        return $this->ensureGoogleDriveFolder($service, $segments, $rootFolderId);
    }

    protected function findOrCreateGoogleDriveFolder(Drive $service, string $name, string $parentId): string
    {
        $escapedName = addslashes($name);
        $query = "name = '{$escapedName}' and mimeType = 'application/vnd.google-apps.folder' and trashed = false and '{$parentId}' in parents";

        $results = $service->files->listFiles([
            'q' => $query,
            'fields' => 'files(id, name)',
            'pageSize' => 1,
        ]);

        if ($results->count() > 0) {
            return $results[0]->id;
        }

        $folder = new DriveFile([
            'name' => $name,
            'mimeType' => 'application/vnd.google-apps.folder',
            'parents' => [$parentId],
        ]);

        return $service->files->create($folder, ['fields' => 'id'])->id;
    }

    protected function buildGoogleClient(): GoogleClient
    {
        $client = new GoogleClient();
        $client->setClientId(Config::get('services.google_drive.client_id'));
        $client->setClientSecret(Config::get('services.google_drive.client_secret'));
        $client->setRedirectUri(route('storage.google.callback'));
        $client->setAccessType('offline');
        $client->setScopes([Drive::DRIVE]);
        $client->setHttpClient(new GuzzleClient($this->googleHttpOptions()));

        return $client;
    }

    protected function googleHttpOptions(): array
    {
        $caBundle = env('AI_CA_BUNDLE');
        $verifySsl = filter_var(env('AI_SSL_VERIFY', true), FILTER_VALIDATE_BOOL);

        if (is_string($caBundle) && $caBundle !== '') {
            return ['verify' => $caBundle];
        }

        if (!$verifySsl || app()->environment('local')) {
            return ['verify' => false];
        }

        return [];
    }
}
