<?php

namespace App\Http\Controllers;

use App\Services\ContactService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function __construct(private readonly ContactService $contact) {}

    public function show(): View
    {
        return view('contact.index');
    }

    public function send(Request $request): RedirectResponse
    {
        $this->contact->send($request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:200',
            'subject' => 'required|string|max:100',
            'message' => 'required|string|min:10|max:3000',
        ]));

        return back()->with('success', 'Your message has been sent. We\'ll be in touch soon.');
    }
}
