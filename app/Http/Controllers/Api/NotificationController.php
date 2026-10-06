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
        $user = $request->user();

        // Mengambil notifikasi pengguna terpanggil
        $notifications = $user->notifications()->paginate(15);

       return response()->json([
    'data' => NotificationResource::collection($notifications)->resolve($request),
    'meta' => [
        'current_page' => $courses->currentPage(),
        'last_page'    => $courses->lastPage(),
        'per_page'     => $courses->perPage(), // <-- Tambahkan baris ini
        'total'        => $courses->total(),
    ],
]);
    }

    // POST /api/v1/notifications/{id}/read
    public function markRead(Request $request, $id)
    {
        $user = $request->user();

        $notification = $user->notifications()->where('id', $id)->first();

        if (! $notification) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $notification->markAsRead();

        return new NotificationResource($notification);
    }
}