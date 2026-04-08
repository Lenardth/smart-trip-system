<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
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

    public function recentActivity(): JsonResponse
    {
        $userId = Auth::id();
        $activities = [];

        // Recent trips
        Trip::where('user_id', $userId)->latest()->limit(5)->get()
            ->each(function ($t) use (&$activities) {
                $activities[] = [
                    'type'  => 'trip',
                    'icon'  => 'fa-route',
                    'color' => '#9c27b0',
                    'title' => 'Trip planned to ' . $t->destination,
                    'sub'   => ($t->budget_label ?? '') . ($t->duration ? ' · ' . ($t->duration_label ?? $t->duration) : ''),
                    'time'  => $t->created_at->diffForHumans(),
                    'ts'    => $t->created_at->timestamp,
                    'url'   => '/plan-trip',
                ];
            });

        // Recent bookings
        Booking::where('user_id', $userId)->latest()->limit(5)->get()
            ->each(function ($b) use (&$activities) {
                $activities[] = [
                    'type'  => 'booking',
                    'icon'  => 'fa-ticket-alt',
                    'color' => '#4caf50',
                    'title' => 'Booking: ' . ($b->title ?? 'Ref #' . $b->booking_reference),
                    'sub'   => '$' . number_format($b->total_price) . ' · ' . ucfirst($b->status),
                    'time'  => $b->created_at->diffForHumans(),
                    'ts'    => $b->created_at->timestamp,
                    'url'   => '/bookings',
                ];
            });

        // Recent wishlist saves
        SavedDestination::with('destination:id,name,country')
            ->where('user_id', $userId)->latest()->limit(5)->get()
            ->each(function ($s) use (&$activities) {
                $name    = $s->destination?->name    ?? 'Destination';
                $country = $s->destination?->country ?? '';
                $activities[] = [
                    'type'  => 'wishlist',
                    'icon'  => 'fa-heart',
                    'color' => '#f44336',
                    'title' => 'Saved ' . $name . ($country ? ', ' . $country : ''),
                    'sub'   => 'Added to wishlist',
                    'time'  => $s->created_at->diffForHumans(),
                    'ts'    => $s->created_at->timestamp,
                    'url'   => '/wishlist',
                ];
            });

        // Recent photo uploads
        Media::where('user_id', $userId)->latest()->limit(5)->get()
            ->each(function ($m) use (&$activities) {
                $activities[] = [
                    'type'  => 'photo',
                    'icon'  => 'fa-images',
                    'color' => '#2196f3',
                    'title' => 'Uploaded: ' . ($m->title ?? $m->file_name),
                    'sub'   => ucfirst($m->type) . ' · ' . number_format($m->file_size / 1024, 0) . ' KB',
                    'time'  => $m->created_at->diffForHumans(),
                    'ts'    => $m->created_at->timestamp,
                    'url'   => '/dashboard',
                ];
            });

        // Recent messages sent
        \App\Models\Message::where('sender_id', $userId)->latest()->limit(3)->get()
            ->each(function ($msg) use (&$activities) {
                $activities[] = [
                    'type'  => 'message',
                    'icon'  => 'fa-comment-dots',
                    'color' => '#c9a96e',
                    'title' => 'Message sent',
                    'sub'   => mb_strlen($msg->body) > 50 ? mb_substr($msg->body, 0, 50) . '…' : $msg->body,
                    'time'  => $msg->created_at->diffForHumans(),
                    'ts'    => $msg->created_at->timestamp,
                    'url'   => '/chat',
                ];
            });

        // Sort by most recent, take top 10
        usort($activities, fn($a, $b) => $b['ts'] - $a['ts']);
        $sorted = array_slice($activities, 0, 10);

        return response()->json(['activities' => $sorted]);
    }

    public function statistics(): JsonResponse
    {
        $user   = Auth::user();
        $userId = $user->id;

        $trips    = Trip::where('user_id', $userId)->count();
        $bookings = Booking::where('user_id', $userId)->count();
        $saved    = SavedDestination::where('user_id', $userId)->count();
        $photos   = Media::where('user_id', $userId)->count();

        try {
            $notifications = $user->unreadNotifications()->count();
        } catch (\Exception $e) {
            $notifications = 0;
        }

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
