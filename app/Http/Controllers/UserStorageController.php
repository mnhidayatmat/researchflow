<?php

namespace App\Http\Controllers;

use App\Services\UserStorageService;
use Illuminate\Http\Request;

class UserStorageController extends Controller
{
    public function __construct(protected UserStorageService $storageService)
    {
    }

    public function edit(Request $request)
    {
        $profile = $this->storageService->profileFor($request->user());
        $role = $request->routeIs('admin.*') ? 'admin' : 'supervisor';
        $hasGoogleOAuthConfig = $this->storageService->hasGoogleOAuthConfig();
        $isGoogleConnected = $this->storageService->canUseGoogleDrive($request->user());

        return view('admin.settings.storage-profile', compact('profile', 'role', 'hasGoogleOAuthConfig', 'isGoogleConnected'));
    }

    public function update(Request $request)
    {
        $profile = $this->storageService->profileFor($request->user());

        $validated = $request->validate([
            'storage_disk' => 'required|in:local,google_drive',
            'google_drive_folder_id' => 'nullable|string',
        ]);

        if (($validated['storage_disk'] ?? 'local') === 'google_drive' && !$this->storageService->canUseGoogleDrive($request->user())) {
            return back()
                ->withInput()
                ->withErrors([
                    'storage_disk' => 'Connect your Google account before selecting Google Drive storage.',
                ]);
        }

        $profile->update($validated);

        return back()->with('success', 'Storage preferences updated.');
    }

    public function test(Request $request)
    {
        $result = $this->storageService->testConnection($request->user());

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    public function redirectToGoogle(Request $request)
    {
        $redirectRoute = $request->user()->isAdmin()
            ? 'admin.settings.storage'
            : 'supervisor.storage.edit';

        $url = $this->storageService->authorizationUrlFor($request->user(), $redirectRoute);

        return redirect()->away($url);
    }

    public function handleGoogleCallback(Request $request)
    {
        if ($request->filled('error')) {
            return redirect()
                ->route($request->user()->isAdmin() ? 'admin.settings.storage' : 'supervisor.storage.edit')
                ->withErrors([
                    'google_drive' => 'Google authorization failed: ' . $request->string('error'),
                ]);
        }

        $validated = $request->validate([
            'code' => 'required|string',
            'state' => 'required|string',
        ]);

        try {
            $redirectRoute = $this->storageService->handleOAuthCallback($request->user(), $validated['code'], $validated['state'])
                ?: ($request->user()->isAdmin() ? 'admin.settings.storage' : 'supervisor.storage.edit');
        } catch (\Throwable $e) {
            return redirect()
                ->route($request->user()->isAdmin() ? 'admin.settings.storage' : 'supervisor.storage.edit')
                ->withErrors([
                    'google_drive' => 'Google Drive connection failed: ' . $e->getMessage(),
                ]);
        }

        return redirect()
            ->route($redirectRoute)
            ->with('success', 'Google Drive connected successfully.');
    }

    public function disconnectGoogle(Request $request)
    {
        $this->storageService->disconnectGoogleDrive($request->user());

        return back()->with('success', 'Google Drive disconnected.');
    }
}
