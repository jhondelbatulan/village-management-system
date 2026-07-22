<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(
        private NotificationService $notificationService,
    ) {}

    public function index(): View
    {
        $notifications = $this->notificationService->getRecentForUser(auth()->id(), 50);
        $unreadCount = $this->notificationService->getUnreadCount(auth()->id());

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    public function fetchUnread(): JsonResponse
    {
        $userId = auth()->id();
        $notifications = $this->notificationService->getUnreadForUser($userId, 10);
        $count = $this->notificationService->getUnreadCount($userId);

        return response()->json([
            'count' => $count,
            'notifications' => $notifications->map(fn($n) => [
                'id' => $n->id,
                'type' => $n->type,
                'title' => $n->title,
                'message' => $n->message,
                'icon' => $n->icon,
                'color' => $n->color,
                'action_url' => $n->action_url,
                'created_at' => $n->created_at->diffForHumans(),
                'created_at_raw' => $n->created_at->toISOString(),
            ]),
        ]);
    }

    public function markAsRead(int $id): JsonResponse
    {
        $this->notificationService->markAsRead($id, auth()->id());
        return response()->json(['success' => true]);
    }

    public function markAllAsRead(): JsonResponse
    {
        $count = $this->notificationService->markAllAsRead(auth()->id());
        return response()->json(['success' => true, 'count' => $count]);
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->notificationService->deleteForUser($id, auth()->id());
        return redirect()->route('notifications.index')->with('success', 'Notification deleted.');
    }
}
