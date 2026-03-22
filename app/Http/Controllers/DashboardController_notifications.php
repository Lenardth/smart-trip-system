<?php

// Add the Notifiable trait to your User model if not already present:
// use Illuminate\Notifications\Notifiable;
// class User extends Authenticatable { use Notifiable; ... }

// ─────────────────────────────────────────────────────────────
// Replace / add these methods inside DashboardController
// ─────────────────────────────────────────────────────────────

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    // ... your existing methods (index, statistics, etc.) ...

    public function notifications(): JsonResponse
    {
        $user = Auth::user();

        $rows = $user->notifications()
            ->latest()
            ->limit(50)
            ->get();

        $mapped = $rows->map(function ($n) {
            $data = $n->data;

            return [
                'id'      => $n->id,
                'type'    => $data['type']    ?? 'system',
                'title'   => $data['title']   ?? 'Notification',
                'message' => $data['message'] ?? '',
                'url'     => $data['url']     ?? null,
                'time'    => $n->created_at->diffForHumans(),
                'read'    => $n->read_at !== null,
                'user'    => $data['user']    ?? null,
            ];
        });

        return response()->json(['notifications' => $mapped]);
    }

    public function markAllNotificationsRead(): JsonResponse
    {
        Auth::user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function markNotificationsRead(Request $request): JsonResponse
    {
        $ids = $request->input('ids', []);

        Auth::user()
            ->notifications()
            ->whereIn('id', $ids)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }
}
