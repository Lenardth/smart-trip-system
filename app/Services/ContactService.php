<?php

namespace App\Services;

use App\Mail\ContactMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactService
{
    public function send(array $data): void
    {
        try {
            Mail::to(config('mail.from.address'))
                ->send(new ContactMail(
                    senderName: $data['name'],
                    senderEmail: $data['email'],
                    subject: $data['subject'],
                    body: $data['message'],
                ));
        } catch (\Throwable $e) {
            Log::warning('Contact mail failed: ' . $e->getMessage());
        }
    }
}
