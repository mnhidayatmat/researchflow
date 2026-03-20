<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationApiController extends Controller
{
    public function index()
    {
        try {
            $notifications = Auth::user()->notifications()->take(20)->get();
            $unreadCount = Auth::user()->unreadNotifications()->count();

            return response()->json([
                'notifications' => $notifications,
                'unread_count' => $unreadCount,
            ]);
        } catch (\Exception $e) {
            // Return empty result if notifications table doesn't exist or has issues
            return response()->json([
                'notifications' => [],
                'unread_count' => 0,
            ]);
        }
    }

    public function markAsRead(string $id)
    {
        try {
            Auth::user()->notifications()->where('id', $id)->first()?->markAsRead();
        } catch (\Exception $e) {
            // Ignore errors
        }
        return response()->json(['success' => true]);
    }

    public function markAllRead()
    {
        try {
            Auth::user()->unreadNotifications->markAsRead();
        } catch (\Exception $e) {
            // Ignore errors
        }
        return response()->json(['success' => true]);
    }
}
