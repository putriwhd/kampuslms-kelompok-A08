<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    // GET /api/v1/notifications
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()->paginate(15);

        return response()->json([
            'data' => NotificationResource::collection($notifications)->resolve($request),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'total' => $notifications->total(),
            ],
        ]);
    }

    // GET /api/v1/notifications/unread-count
    public function unreadCount(Request $request)
    {
        return response()->json([
            'count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    // POST /api/v1/notifications/{id}/read
    public function markAsRead(Request $request, string $id)
    {
        $notification = DatabaseNotification::findOrFail($id);

        if (
            $notification->notifiable_id !== auth()->id()
            || $notification->notifiable_type !== $request->user()->getMorphClass()
        ) {
            abort(403, 'Anda tidak memiliki akses ke sumber daya ini.');
        }

        $notification->markAsRead();

        return response()->json([
            'data' => (new NotificationResource($notification))->resolve($request),
        ]);
    }

    // POST /api/v1/notifications/read-all
    public function markAllAsRead(Request $request)
    {
        $updated = $request->user()
            ->unreadNotifications()
            ->update(['read_at' => now()]);

        return response()->json([
            'updated' => $updated,
        ]);
    }
}
