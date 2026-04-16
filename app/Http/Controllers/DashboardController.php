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
        $userId     = Auth::id();
        $activities = [];

        // Trips
        Trip::where('user_id', $userId)->latest()->limit(5)->get()
            ->each(function ($t) use (&$activities) {
                $dur = [
                    'weekend'   => 'Long Weekend',
                    'week'      => 'One Week',
                    'two_weeks' => 'Two Weeks',
                    'month'     => 'One Month+',
                    'flexible'  => 'Flexible',
                ][$t->duration] ?? $t->duration ?? '';

                $bud = [
                    'backpacker' => 'Backpacker',
                    'budget'     => 'Budget',
                    'mid'        => 'Mid-Range',
                    'premium'    => 'Premium',
                    'luxury'     => 'Luxury',
                ][$t->budget] ?? $t->budget ?? '';

                $activities[] = [
                    'type'  => 'trip',
                    'icon'  => 'fa-route',
                    'color' => '#9c27b0',
                    'title' => 'Trip planned to ' . $t->destination,
                    'sub'   => trim($bud . ($dur ? ' · ' . $dur : '')),
                    'time'  => $t->created_at->diffForHumans(),
                    'ts'    => $t->created_at->timestamp,
                    'url'   => '/plan-trip',
                ];
            });

        // Bookings
        Booking::where('user_id', $userId)->latest()->limit(5)->get()
            ->each(function ($b) use (&$activities) {
                $activities[] = [
                    'type'  => 'booking',
                    'icon'  => 'fa-ticket-alt',
                    'color' => '#4caf50',
                    'title' => 'Booking: ' . ($b->title ?? 'Ref #' . $b->booking_reference),
                    'sub'   => '$' . number_format((float) $b->total_price, 2) . ' · ' . ucfirst($b->status),
                    'time'  => $b->created_at->diffForHumans(),
                    'ts'    => $b->created_at->timestamp,
                    'url'   => '/bookings',
                ];
            });

        // Wishlist saves
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

        // Photo uploads
        Media::where('user_id', $userId)->latest()->limit(5)->get()
            ->each(function ($m) use (&$activities) {
                $kb = $m->file_size ? number_format($m->file_size / 1024, 0) . ' KB' : '';
                $activities[] = [
                    'type'  => 'photo',
                    'icon'  => 'fa-images',
                    'color' => '#2196f3',
                    'title' => 'Uploaded: ' . ($m->title ?? $m->file_name ?? 'Photo'),
                    'sub'   => ucfirst($m->type ?? 'image') . ($kb ? ' · ' . $kb : ''),
                    'time'  => $m->created_at->diffForHumans(),
                    'ts'    => $m->created_at->timestamp,
                    'url'   => '/dashboard',
                ];
            });

        // Messages sent
        Message::where('sender_id', $userId)->latest()->limit(3)->get()
            ->each(function ($msg) use (&$activities) {
                $preview = mb_strlen($msg->body) > 50
                    ? mb_substr($msg->body, 0, 50) . '…'
                    : $msg->body;
                $activities[] = [
                    'type'  => 'message',
                    'icon'  => 'fa-comment-dots',
                    'color' => '#c9a96e',
                    'title' => 'Message sent',
                    'sub'   => $preview,
                    'time'  => $msg->created_at->diffForHumans(),
                    'ts'    => $msg->created_at->timestamp,
                    'url'   => '/chat',
                ];
            });

        usort($activities, fn($a, $b) => $b['ts'] - $a['ts']);

        return response()->json(['activities' => array_slice($activities, 0, 10)]);
    }

    public function statistics(): JsonResponse
    {
        $user   = Auth::user();
        $userId = $user->id;

        // Only count active/planned items so numbers match what user sees
        $trips    = Trip::where('user_id', $userId)->where('status', 'planned')->count();
        $bookings = Booking::where('user_id', $userId)->whereIn('status', ['confirmed', 'pending'])->count();
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

        $rows = $user->notifications()->latest()->limit(50)->get();

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

        return response()->json(['success' => true, 'message' => $message]);
    }
}
