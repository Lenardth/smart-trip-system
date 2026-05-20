<?php

namespace App\Http\Controllers;

use App\Mail\ContactMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    public function show()
    {
        return view('contact.index');
    }

    public function send(Request $request)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:120',
            'email'   => 'required|email|max:200',
            'subject' => 'required|string|max:100',
            'message' => 'required|string|min:10|max:3000',
        ]);

        try {
            Mail::to(config('mail.from.address'))
                ->send(new ContactMail(
                    senderName:  $data['name'],
                    senderEmail: $data['email'],
                    subject:     $data['subject'],
                    body:        $data['message'],
                ));
        } catch (\Throwable $e) {
            Log::warning('Contact mail failed: ' . $e->getMessage());
        }

        return back()->with('success', 'Your message has been sent. We\'ll be in touch soon.');
    }
}
