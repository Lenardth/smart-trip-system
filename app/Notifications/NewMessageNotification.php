<?php

namespace App\Notifications;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewMessageNotification extends Notification
{
    use Queueable;

    public function __construct(public Message $message) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $sender   = $this->message->sender;
        $initials = collect(explode(' ', $sender->name))
            ->map(fn($w) => strtoupper($w[0]))
            ->take(2)
            ->implode('');

        return [
            'type'    => 'chat',
            'title'   => 'New message from ' . $sender->name,
            'message' => strlen($this->message->body) > 80
                ? substr($this->message->body, 0, 80) . '…'
                : $this->message->body,
            'url'     => '/chat/' . $sender->id,
            'user'    => [
                'id'       => $sender->id,
                'name'     => $sender->name,
                'avatar'   => $sender->avatar,
                'initials' => $initials,
            ],
        ];
    }
}