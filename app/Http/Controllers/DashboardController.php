<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Booking;
use App\Models\Trip;
use App\Models\Message;
use App\Models\Notification;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        return view('dashboard.index', compact('user'));
    }

    public function statistics()
    {
        $userId = Auth::id();

        $totalTrips    = Trip::where('user_id', $userId)->count();
        $upcomingTrips = Trip::where('user_id', $userId)->whereDate('start_date', '>=', now())->count();
        $totalBookings = Booking::where('user_id', $userId)->count();
        $totalSpent    = Booking::where('user_id', $userId)->where('status', 'confirmed')->sum('total_price');

        return response()->json([
            'total_trips'    => $totalTrips,
            'upcoming_trips' => $upcomingTrips,
            'total_bookings' => $totalBookings,
            'total_spent'    => $totalSpent,
        ]);
    }

    public function notifications()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return response()->json($notifications);
    }

    public function markAllNotificationsRead()
    {
        Notification::where('user_id', Auth::id())
            ->where('read', false)
            ->update(['read' => true]);

        return response()->json(['success' => true]);
    }

    public function markNotificationsRead(Request $request)
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

    public function searchUsers(Request $request)
    {
        $query = $request->input('q', '');

        $users = User::where('id', '!=', Auth::id())
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', '%' . $query . '%')
                  ->orWhere('email', 'like', '%' . $query . '%');
            })
            ->select('id', 'name', 'email')
            ->limit(10)
            ->get();

        return response()->json($users);
    }

    public function sendChat(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'content'     => 'required|string|max:1000',
        ]);

        $message = Message::create([
            'sender_id'   => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'content'     => $request->content,
        ]);

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }
}
