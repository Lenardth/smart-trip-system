@extends('layouts.public')

@section('title', 'Contact — ' . config('app.name'))

@section('content')
<div class="static-page">
    <div class="static-hero">
        <h1>Contact Us</h1>
        <p>We'd love to hear from you.</p>
    </div>

    <div class="static-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('contact.send') }}" class="contact-form">
            @csrf
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required maxlength="120">
                @error('name')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required maxlength="200">
                @error('email')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label for="subject">Subject</label>
                <input type="text" id="subject" name="subject" value="{{ old('subject') }}" required maxlength="100">
                @error('subject')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label for="message">Message</label>
                <textarea id="message" name="message" rows="6" required maxlength="3000">{{ old('message') }}</textarea>
                @error('message')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <button type="submit" class="primary-button">Send Message</button>
        </form>
    </div>
</div>
@endsection
