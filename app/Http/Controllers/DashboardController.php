<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Booking;
use App\Models\Trip;
use App\Models\Message;
use App\Models\SavedDestination;
use App\Models\Media;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        return view('dashboard.index', compact('user'));
    }

    public function statistics(): JsonResponse
    {
        $user   = Auth::user();
        $userId = $user->id;

        $trips         = Trip::where('user_id', $userId)->count();
        $bookings      = Booking::where('user_id', $userId)->count();
        $saved         = SavedDestination::where('user_id', $userId)->count();
        $photos        = Media::where('user_id', $userId)->count();
        $notifications = $user->unreadNotifications()->count();

        return response()->json([
            'photos'        => $photos,
            'trips'         => $trips,
            'bookings'      => $bookings,
            'saved'         => $saved,
            'notifications' => $notifications,
        ]);
    }

    public function wishlistCount(): JsonResponse
    {
        $count = SavedDestination::where('user_id', Auth::id())->count();
        return response()->json(['count' => $count]);
    }

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
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'string',
        ]);

        Auth::user()
            ->notifications()
            ->whereIn('id', $request->ids)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function searchUsers(Request $request): JsonResponse
    {
        $query = $request->input('q', '');

        $users = User::where('id', '!=', Auth::id())
            ->where(function ($q) use ($query) {
                $q->where('name',  'like', '%' . $query . '%')
                  ->orWhere('email', 'like', '%' . $query . '%');
            })
            ->select('id', 'name', 'email', 'profile_picture')
            ->limit(10)
            ->get();

        return response()->json(['users' => $users]);
    }

    public function sendChat(Request $request): JsonResponse
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'content'     => 'required|string|max:2000',
        ]);

        $message = Message::create([
            'sender_id'   => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'body'        => $request->input('content'),
        ]);

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }
}
