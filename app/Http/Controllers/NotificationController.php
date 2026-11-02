<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Display a listing of notifications for the current user.
     */
    public function index(Request $request): View|JsonResponse
    {
        $user = auth()->user();
        $notifications = AppNotification::forUser($user)
            ->latest('id')
            ->paginate(20);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'unread_count' => NotificationService::getUnreadCount($user),
                'data' => $notifications,
            ]);
        }

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Get live unread count and recent notifications (JSON for navbar).
     */
    public function feed(Request $request): JsonResponse
    {
        $user = auth()->user();
        $unreadCount = NotificationService::getUnreadCount($user);
        $recent = NotificationService::getRecentForUser($user, 8)->map(function ($n) {
            return [
                'id' => $n->id,
                'title' => $n->title,
                'message' => $n->message,
                'icon' => $n->icon ?: 'bell',
                'color' => $n->color ?: 'brand',
                'is_read' => (bool)$n->is_read,
                'action_url' => $n->action_url,
                'time_ago' => $n->created_at ? $n->created_at->diffForHumans() : '',
            ];
        });

        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount,
            'notifications' => $recent,
        ]);
    }

    /**
     * Mark a single notification as read and optionally navigate to action_url.
     */
    public function markAsRead(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $user = auth()->user();
        $notification = AppNotification::forUser($user)->find($id);

        if (!$notification) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Notifikasi tidak ditemukan'], 404);
            }
            return redirect()->back();
        }

        $notification->markAsRead();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'unread_count' => NotificationService::getUnreadCount($user),
            ]);
        }

        if ($request->has('redirect') && $notification->action_url) {
            return redirect()->to($notification->action_url);
        }

        return redirect()->back()->with('toast', [
            'type' => 'success',
            'message' => 'Notifikasi ditandai telah dibaca.',
        ]);
    }

    /**
     * Mark all notifications for the user as read.
     */
    public function markAllAsRead(Request $request): JsonResponse|RedirectResponse
    {
        $user = auth()->user();
        $affected = NotificationService::markAllAsRead($user);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'affected' => $affected,
                'unread_count' => 0,
            ]);
        }

        return redirect()->back()->with('toast', [
            'type' => 'success',
            'message' => 'Semua notifikasi ditandai telah dibaca.',
        ]);
    }
}
