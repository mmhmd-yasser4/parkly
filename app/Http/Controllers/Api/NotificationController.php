<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // List all notifications for the authenticated user
    public function index(Request $request)
    {
        $notifications = $request->user()
            ->notifications()
            ->orderByDesc('created_at')
            ->get();

        return response()->json($notifications);
    }

    // Mark a single notification as read
    public function markRead(Request $request, $id)
    {
        $notification = Notification::findOrFail($id);
    
        if ($notification->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
    
        $notification->read_at = now();
        $notification->save();
    
        return response()->json(['message' => 'Notification marked as read.']);
    }

    // Mark all notifications as read
    public function markAllRead(Request $request)
    {
        $request->user()
            ->notifications()
            ->whereNull('read_at')
            ->each(function ($notification) {
                $notification->read_at = now();
                $notification->save();
            });
    
        return response()->json(['message' => 'All notifications marked as read.']);
    }
}