<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display a listing of the user's notifications.
     */
    public function index(): JsonResponse
    {
        $notifications = Auth::user()->userNotifications()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'notifications' => $notifications
        ]);
    }

    /**
     * Display a listing of the user's unread notifications.
     */
    public function unread(): JsonResponse
    {
        $notifications = Auth::user()->userNotifications()
            ->where('read', false)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'notifications' => $notifications
        ]);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(Notification $notification): JsonResponse
    {
        // Check if the notification belongs to the authenticated user
        if ($notification->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Bạn không có quyền truy cập thông báo này'
            ], 403);
        }

        $notification->update([
            'read' => true
        ]);

        return response()->json([
            'message' => 'Đã đánh dấu thông báo là đã đọc',
            'notification' => $notification
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(): JsonResponse
    {
        Auth::user()->userNotifications()
            ->where('read', false)
            ->update(['read' => true]);

        return response()->json([
            'message' => 'Đã đánh dấu tất cả thông báo là đã đọc'
        ]);
    }

    /**
     * Delete a notification.
     */
    public function destroy(Notification $notification): JsonResponse
    {
        // Check if the notification belongs to the authenticated user
        if ($notification->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Bạn không có quyền xóa thông báo này'
            ], 403);
        }

        $notification->delete();

        return response()->json([
            'message' => 'Đã xóa thông báo'
        ]);
    }
}
