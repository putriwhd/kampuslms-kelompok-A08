<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use Illuminate\Http\Request;

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

    // POST /api/v1/notifications/{id}/read
    public function markRead(Request $request, $id)
    {
        $user = $request->user();

        $notification = $user->notifications()->where('id', $id)->first();

        if (! $notification) {
            return response()->json([
                'message' => 'Anda tidak memiliki akses ke sumber daya ini.',
            ], 403);
        }

        $notification->markAsRead();

        return response()->json([
            'data' => (new NotificationResource($notification))->resolve($request),
        ]);
    }
}