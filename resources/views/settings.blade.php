@extends('layouts.public')

@section('title', 'Settings — Smart Booking')

@push('styles')
    <style>
        .settings-container {
            max-width: 900px;
            margin: 32px auto;
            padding: 0 16px;
        }
        .settings-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 32px;
            text-align: center;
        }
        .settings-link {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 24px;
            background: var(--gold, #c9a96e);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            transition: background 0.3s;
        }
        .settings-link:hover {
            background: #b58d4a;
        }
    </style>
@endpush

@section('content')
<div class="settings-container">
    <div class="settings-card">
        <h1><i class="fas fa-cog"></i> Settings</h1>
        <p style="margin: 16px 0; color: #666;">Settings page is available. Use profile page for account updates.</p>
        <a href="/profile" class="settings-link">
            <i class="fas fa-user"></i> Go to Profile Settings
        </a>
    </div>
</div>
@endsection
