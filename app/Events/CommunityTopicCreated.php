<?php

namespace App\Events;

use App\Models\CommunityTopic;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommunityTopicCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public CommunityTopic $topic) {}

    public function broadcastOn(): Channel
    {
        return new Channel('community');
    }

    public function broadcastAs(): string
    {
        return 'topic.created';
    }

    public function broadcastWith(): array
    {
        return [
            'topic' => [
                'id'         => $this->topic->id,
                'author'     => $this->topic->author,
                'title'      => $this->topic->title,
                'tags'       => json_decode($this->topic->tags, true) ?? [],
                'replies'    => $this->topic->replies,
                'created_at' => $this->topic->created_at,
            ]
        ];
    }
}
