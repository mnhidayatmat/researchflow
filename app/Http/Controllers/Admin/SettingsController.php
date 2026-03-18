<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiProvider;
use App\Models\SystemSetting;
use App\Services\Storage\StorageManager;
use App\Services\Storage\StorageTestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function __construct(
        protected StorageManager $storageManager,
        protected StorageTestService $storageTestService
    ) {}

    public function storage()
    {
        $currentDisk = SystemSetting::get('storage_disk', 'local');
        $settings = SystemSetting::where('group', 'storage')->pluck('value', 'key');
        $storageStats = [];

        try {
            if ($currentDisk === 'do_spaces' || $currentDisk === 'google_drive') {
                $storageStats = $this->storageTestService->getStorageStats($currentDisk);
            }
        } catch (\Exception $e) {
            $storageStats = ['error' => $e->getMessage()];
        }

        return view('admin.settings.storage', compact('currentDisk', 'settings', 'storageStats'));
    }

    public function updateStorage(Request $request)
    {
        $validated = $request->validate([
            'storage_disk' => 'required|in:local,do_spaces,google_drive',
            'do_spaces_key' => 'nullable|string',
            'do_spaces_secret' => 'nullable|string',
            'do_spaces_region' => 'nullable|string',
            'do_spaces_bucket' => 'nullable|string',
            'do_spaces_endpoint' => 'nullable|string',
            'google_drive_client_id' => 'nullable|string',
            'google_drive_client_secret' => 'nullable|string',
            'google_drive_refresh_token' => 'nullable|string',
            'google_drive_folder_id' => 'nullable|string',
        ]);

        // Don't update placeholder password values
        foreach ($validated as $key => $value) {
            if ($value === '••••••••') {
                unset($validated[$key]);
                continue;
            }

            if ($value !== null && $key !== 'storage_disk') {
                SystemSetting::set($key, $value, 'storage');
            }
        }

        // Always update the disk selection
        SystemSetting::set('storage_disk', $request->input('storage_disk'), 'storage');

        return back()->with('success', 'Storage settings updated.');
    }

    public function testStorage(Request $request)
    {
        $disk = $request->input('disk', 'local');

        $result = $this->storageTestService->testConnection($disk);

        $statusCode = $result['success'] ? 200 : 422;

        return response()->json($result, $statusCode);
    }

    public function getStorageStats(Request $request)
    {
        $disk = $request->input('disk', 'local');

        try {
            $stats = $this->storageTestService->getStorageStats($disk);
            return response()->json(['success' => true, 'stats' => $stats]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function ai()
    {
        $providers = AiProvider::all();
        return view('admin.settings.ai', compact('providers'));
    }

    public function updateAi(Request $request)
    {
        $validated = $request->validate([
            'providers' => 'required|array',
            'providers.*.name' => 'required|string',
            'providers.*.slug' => 'required|string',
            'providers.*.api_key' => 'nullable|string',
            'providers.*.model' => 'nullable|string',
            'providers.*.base_url' => 'nullable|string',
            'providers.*.is_active' => 'boolean',
            'providers.*.is_default' => 'boolean',
        ]);

        foreach ($validated['providers'] as $data) {
            AiProvider::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );
        }

        return back()->with('success', 'AI providers updated.');
    }

    public function users()
    {
        $users = \App\Models\User::with('supervisedStudents')
            ->orderBy('role')->orderBy('name')->paginate(20);
        return view('admin.settings.users', compact('users'));
    }

    public function updateRole(Request $request, \App\Models\User $user)
    {
        $validated = $request->validate([
            'role' => 'required|in:admin,supervisor,cosupervisor,student',
        ]);

        $oldRole = $user->role;
        $user->update(['role' => $validated['role']]);

        // If switching from student to supervisor/cosupervisor, remove student profile
        if ($oldRole === 'student' && in_array($validated['role'], ['supervisor', 'cosupervisor', 'admin'])) {
            $user->student()?->delete();
        }

        // If switching from supervisor to student, ensure student profile exists
        if (in_array($oldRole, ['supervisor', 'cosupervisor', 'admin']) && $validated['role'] === 'student') {
            if (!$user->student) {
                $user->student()->create([
                    'status' => 'pending',
                    'overall_progress' => 0,
                ]);
            }
        }

        $message = "User role changed from {$oldRole} to {$validated['role']}.";

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('success', $message);
    }

    public function updateStatus(Request $request, \App\Models\User $user)
    {
        $validated = $request->validate([
            'status' => 'required|in:active,inactive,pending',
        ]);

        $user->update(['status' => $validated['status']]);

        $message = 'User status updated.';

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('success', $message);
    }
}
