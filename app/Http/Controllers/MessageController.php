<?php

namespace App\Http\Controllers;

use App\Events\NewMessage;
use App\Models\Message;
use App\Models\User;
use App\Notifications\NewMessageNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function conversations(): JsonResponse
    {
        $userId  = Auth::id();
        $threads = Message::conversationsFor($userId);

        $result = $threads->map(function (Message $msg) use ($userId) {
            $other  = $msg->sender_id === $userId ? $msg->receiver : $msg->sender;
            $unread = Message::where('sender_id', $other->id)
                ->where('receiver_id', $userId)
                ->whereNull('read_at')
                ->count();

            return [
                'user' => [
                    'id'     => $other->id,
                    'name'   => $other->name,
                    'avatar' => $other->avatar,
                ],
                'last_message' => [
                    'id'         => $msg->id,
                    'body'       => $msg->body,
                    'sender_id'  => $msg->sender_id,
                    'created_at' => $msg->created_at->toIso8601String(),
                ],
                'unread_count' => $unread,
            ];
        });

        return response()->json($result);
    }

    public function thread(int $userId): JsonResponse
    {
        $me = Auth::id();

        Message::where('sender_id', $userId)
            ->where('receiver_id', $me)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $messages = Message::conversation($me, $userId)->get();

        return response()->json($messages->map(fn($m) => [
            'id'          => $m->id,
            'sender_id'   => $m->sender_id,
            'receiver_id' => $m->receiver_id,
            'body'        => $m->body,
            'read_at'     => $m->read_at?->toIso8601String(),
            'created_at'  => $m->created_at->toIso8601String(),
            'sender' => [
                'id'     => $m->sender->id,
                'name'   => $m->sender->name,
                'avatar' => $m->sender->avatar,
            ],
        ]));
    }

    public function send(Request $request): JsonResponse
    {
        $data = $request->validate([
            'receiver_id' => ['required', 'integer', 'exists:users,id'],
            'body'        => ['required', 'string', 'max:2000'],
        ]);

        $me = Auth::id();

        if ((int) $data['receiver_id'] === $me) {
            return response()->json(['error' => 'Cannot message yourself.'], 422);
        }

        $message = Message::create([
            'sender_id'   => $me,
            'receiver_id' => $data['receiver_id'],
            'body'        => $data['body'],
        ]);

        $message->load('sender:id,name,avatar', 'receiver:id,name,avatar');

        broadcast(new NewMessage($message))->toOthers();

        $message->receiver->notify(new NewMessageNotification($message));

        return response()->json([
            'id'          => $message->id,
            'sender_id'   => $message->sender_id,
            'receiver_id' => $message->receiver_id,
            'body'        => $message->body,
            'read_at'     => null,
            'created_at'  => $message->created_at->toIso8601String(),
            'sender' => [
                'id'     => $message->sender->id,
                'name'   => $message->sender->name,
                'avatar' => $message->sender->avatar,
            ],
        ], 201);
    }

    public function unreadCount(): JsonResponse
    {
        $count = Message::where('receiver_id', Auth::id())
            ->whereNull('read_at')
            ->count();

        return response()->json(['count' => $count]);
    }

    public function searchUsers(Request $request): JsonResponse
    {
        $q = $request->get('q', '');

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $users = User::where('id', '!=', Auth::id())
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%");
            })
            ->select('id', 'name', 'avatar', 'email')
            ->limit(10)
            ->get();

        return response()->json($users);
    }
}
