<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Booking;
use App\Models\Trip;
use App\Models\Message;
use App\Models\Notification;
use App\Models\SavedDestination;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        return view('dashboard.index', compact('user'));
    }

    public function statistics(): JsonResponse
    {
        $userId = Auth::id();

        $trips         = Trip::where('user_id', $userId)->count();
        $bookings      = Booking::where('user_id', $userId)->count();
        $saved         = SavedDestination::where('user_id', $userId)->count();
        $notifications = Notification::where('user_id', $userId)->where('read', false)->count();

        return response()->json([
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
        $notifications = Notification::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return response()->json(['notifications' => $notifications]);
    }

    public function markAllNotificationsRead(): JsonResponse
    {
        Notification::where('user_id', Auth::id())
            ->where('read', false)
            ->update(['read' => true]);

        return response()->json(['success' => true]);
    }

    public function markNotificationsRead(Request $request): JsonResponse
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'integer',
        ]);

        Notification::where('user_id', Auth::id())
            ->whereIn('id', $request->ids)
            ->update(['read' => true]);

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
            ->select('id', 'name', 'email')
            ->limit(10)
            ->get();

        return response()->json(['users' => $users]);
    }

    public function sendChat(Request $request): JsonResponse
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'content'     => 'required|string|max:1000',
        ]);

        $message = Message::create([
            'sender_id'   => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'content'     => $request->input('content'),
        ]);

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }
}
