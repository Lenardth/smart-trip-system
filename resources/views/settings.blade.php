@extends('layouts.authenticated')

@section('title', 'Settings — Smart Booking')
@section('page-title', 'Settings')
@section('page-description', 'Manage your account preferences')

@push('styles')
    <style>
        .settings-container { max-width: 720px; margin: 0 auto; }
        .settings-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,.06);
            padding: 32px;
            text-align: center;
            margin-bottom: 24px;
        }
        .settings-card h2 { margin: 0 0 8px; }
        .settings-card p  { color: #666; margin: 0 0 20px; }
        .settings-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: var(--gold, #c9a96e);
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            transition: background 0.2s;
        }
        .settings-link:hover { background: #b58d4a; }
    </style>
@endpush

@section('content')
<div class="settings-container">
    <div class="settings-card">
        <h2><i class="fas fa-user-circle" style="color:var(--gold,#c9a96e);margin-right:8px;"></i> Account Settings</h2>
        <p>Update your profile information, change your password, or delete your account.</p>
        <a href="{{ route('profile.edit') }}" class="settings-link">
            <i class="fas fa-user"></i> Go to Profile Settings
        </a>
    </div>
</div>
@endsection
