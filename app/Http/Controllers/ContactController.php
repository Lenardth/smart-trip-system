<?php

namespace App\Http\Controllers;

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
            Mail::raw(
                "Name: {$data['name']}\nEmail: {$data['email']}\nSubject: {$data['subject']}\n\n{$data['message']}",
                function ($m) use ($data) {
                    $m->to(config('mail.from.address'))
                      ->replyTo($data['email'], $data['name'])
                      ->subject('[Smart Booking] ' . $data['subject']);
                }
            );
        } catch (\Throwable $e) {
            Log::warning('Contact mail failed: ' . $e->getMessage());
        }

        return back()->with('success', 'Your message has been sent. We\'ll be in touch soon.');
    }
}
