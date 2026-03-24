<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = ['sender_id', 'receiver_id', 'body', 'read_at'];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public static function conversation(int $userA, int $userB)
    {
        return static::where(function ($q) use ($userA, $userB) {
            $q->where('sender_id', $userA)->where('receiver_id', $userB);
        })->orWhere(function ($q) use ($userA, $userB) {
            $q->where('sender_id', $userB)->where('receiver_id', $userA);
        })->with(['sender:id,name,profile_picture', 'receiver:id,name,profile_picture'])
          ->orderBy('created_at');
    }

    public static function conversationsFor(int $userId)
    {
        $latestIds = static::selectRaw('MAX(id) as id')
            ->where(function ($q) use ($userId) {
                $q->where('sender_id', $userId)->orWhere('receiver_id', $userId);
            })
            ->groupByRaw('LEAST(sender_id, receiver_id), GREATEST(sender_id, receiver_id)')
            ->pluck('id');

        return static::whereIn('id', $latestIds)
            ->with(['sender:id,name,profile_picture', 'receiver:id,name,profile_picture'])
            ->orderByDesc('created_at')
            ->get();
    }
}
