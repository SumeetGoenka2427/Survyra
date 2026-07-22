<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function markRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()->notifications()->where('id', $id)->firstOrFail();
        $notification->markAsRead();

        return response()->json([
            'html' => view('notifications._items', [
                'notifications' => $request->user()->notifications()->latest()->limit(10)->get(),
            ])->render(),
            'unreadCount' => $request->user()->unreadNotifications()->count(),
        ]);
    }
}
