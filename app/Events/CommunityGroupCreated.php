<?php

namespace App\Events;

use App\Models\CommunityGroup;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommunityGroupCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public CommunityGroup $group) {}

    public function broadcastOn(): Channel
    {
        return new Channel('community');
    }

    public function broadcastAs(): string
    {
        return 'group.created';
    }

    public function broadcastWith(): array
    {
        return ['group' => $this->group->toArray()];
    }
}
