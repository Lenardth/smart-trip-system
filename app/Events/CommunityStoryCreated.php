<?php

namespace App\Events;

use App\Models\CommunityStory;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommunityStoryCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public CommunityStory $story) {}

    public function broadcastOn(): Channel
    {
        return new Channel('community');
    }

    public function broadcastAs(): string
    {
        return 'story.created';
    }

    public function broadcastWith(): array
    {
        return ['story' => $this->story->toArray()];
    }
}
