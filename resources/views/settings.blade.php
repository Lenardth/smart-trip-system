@extends('layouts.authenticated')

@section('title', 'Settings — Smart Booking')
@section('page-title', 'Settings')
@section('page-description', 'Manage your account and preferences')

@section('content')

@if(session('status'))
<div class="profile-alert profile-alert--success" style="margin-bottom:20px;">
    <i class="fas fa-check-circle"></i>
    @switch(session('status'))
        @case('profile-updated') Profile updated. @break
        @case('password-updated') Password changed. @break
        @default Settings saved.
    @endswitch
</div>
@endif

<div class="settings-wrap">

    <div class="settings-nav">
        <a href="#account" class="settings-nav-item active" onclick="showTab('account',this)">
            <i class="fas fa-user"></i> Account
        </a>
        <a href="#security" class="settings-nav-item" onclick="showTab('security',this)">
            <i class="fas fa-lock"></i> Security
        </a>
        <a href="#preferences" class="settings-nav-item" onclick="showTab('preferences',this)">
            <i class="fas fa-sliders-h"></i> Preferences
        </a>
        <a href="#danger" class="settings-nav-item settings-nav-item--danger" onclick="showTab('danger',this)">
            <i class="fas fa-exclamation-triangle"></i> Danger Zone
        </a>
    </div>

    <div class="settings-content">


        <div class="settings-tab active" id="tab-account">
            <div class="settings-card">
                <div class="settings-card-header">
                    <div class="settings-avatar-wrap">
                        @if(Auth::user()->profile_picture)
                            <img src="{{ asset('storage/' . Auth::user()->profile_picture) }}"
                                 alt="{{ Auth::user()->name }}"
                                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                            <div class="settings-avatar-initials" style="display:none;">{{ strtoupper(substr(Auth::user()->name,0,1)) }}</div>
                        @else
                            <div class="settings-avatar-initials">{{ strtoupper(substr(Auth::user()->name,0,1)) }}</div>
                        @endif
                        <label class="settings-avatar-btn" for="settingsPicInput" title="Change photo">
                            <i class="fas fa-camera"></i>
                        </label>
                    </div>
                    <div>
                        <h3 style="margin:0 0 4px;color:var(--deep);">{{ Auth::user()->name }}</h3>
                        <p style="margin:0;font-size:13px;color:var(--text-muted);">{{ Auth::user()->email }}</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('profile.picture.upload') }}" enctype="multipart/form-data" id="settingsPicForm">
                    @csrf
                    <input type="file" id="settingsPicInput" name="profile_picture" accept="image/*" style="display:none"
                           onchange="document.getElementById('settingsPicForm').submit()">
                    @error('profile_picture')
                        <div class="pf-error" style="margin-top:8px; display:block; text-align:center;">{{ $message }}</div>
                    @enderror
                </form>

                <form method="POST" action="{{ route('profile.update') }}" style="margin-top:24px;">
                    @csrf @method('PATCH')

                    <div class="pf-group">
                        <label for="s_name">Full Name</label>
                        <input type="text" id="s_name" name="name" value="{{ old('name', Auth::user()->name) }}" required>
                        @error('name') <span class="pf-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="pf-group">
                        <label for="s_email">Email Address</label>
                        <input type="email" id="s_email" name="email" value="{{ old('email', Auth::user()->email) }}" required>
                        @error('email') <span class="pf-error">{{ $message }}</span> @enderror
                    </div>

                    @if(Auth::user()->user_type === 'agency')
                    <div class="pf-group">
                        <label for="s_agency">Agency Name</label>
                        <input type="text" id="s_agency" name="agency_name" value="{{ old('agency_name', Auth::user()->agency_name) }}">
                    </div>
                    @endif

                    <div style="display:flex;gap:10px;flex-wrap:wrap;">
                        <button type="submit" class="primary-button">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                        @if(Auth::user()->profile_picture)
                        <form method="POST" action="{{ route('profile.picture.delete') }}" style="margin:0;">
                            @csrf @method('DELETE')
                            <button type="submit" class="secondary-button" style="font-size:13px;">
                                <i class="fas fa-trash-alt"></i> Remove Photo
                            </button>
                        </form>
                        @endif
                    </div>
                </form>
            </div>
        </div>


        <div class="settings-tab" id="tab-security" style="display:none;">
            <div class="settings-card">
                <h2 class="settings-section-title"><i class="fas fa-lock"></i> Change Password</h2>

                <form method="POST" action="{{ route('password.update') }}">
                    @csrf @method('PUT')

                    <div class="pf-group">
                        <label for="s_current">Current Password</label>
                        <input type="password" id="s_current" name="current_password" autocomplete="current-password" placeholder="Enter current password">
                        @error('current_password', 'updatePassword') <span class="pf-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="pf-group">
                        <label for="s_new">New Password</label>
                        <input type="password" id="s_new" name="password" autocomplete="new-password" placeholder="Min. 8 characters">
                        @error('password', 'updatePassword') <span class="pf-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="pf-group">
                        <label for="s_confirm">Confirm New Password</label>
                        <input type="password" id="s_confirm" name="password_confirmation" autocomplete="new-password" placeholder="Repeat new password">
                    </div>

                    <button type="submit" class="primary-button">
                        <i class="fas fa-key"></i> Update Password
                    </button>
                </form>
            </div>

            <div class="settings-card" style="margin-top:20px;">
                <h2 class="settings-section-title"><i class="fas fa-shield-alt"></i> Account Security</h2>
                <div class="settings-info-row">
                    <span><i class="fas fa-envelope"></i> Email verified</span>
                    @if(Auth::user()->hasVerifiedEmail())
                        <span style="color:var(--success);font-weight:600;"><i class="fas fa-check-circle"></i> Yes</span>
                    @else
                        <form method="POST" action="{{ route('verification.send') }}" style="margin:0;">
                            @csrf
                            <button type="submit" class="secondary-button" style="font-size:12px;padding:5px 12px;">
                                <i class="fas fa-paper-plane"></i> Send verification
                            </button>
                        </form>
                    @endif
                </div>
                <div class="settings-info-row">
                    <span><i class="fas fa-user-tag"></i> Account type</span>
                    <span style="font-weight:600;color:var(--deep);text-transform:capitalize;">{{ Auth::user()->user_type ?? 'Traveler' }}</span>
                </div>
                <div class="settings-info-row">
                    <span><i class="fas fa-calendar-alt"></i> Member since</span>
                    <span style="color:var(--text-muted);">{{ Auth::user()->created_at->format('M Y') }}</span>
                </div>
            </div>
        </div>


        <div class="settings-tab" id="tab-preferences" style="display:none;">
            <div class="settings-card">
                <h2 class="settings-section-title"><i class="fas fa-sliders-h"></i> App Preferences</h2>
                <div class="settings-pref-row">
                    <div>
                        <strong>Email Notifications</strong>
                        <p>Receive booking confirmations and trip reminders by email.</p>
                    </div>
                    <label class="settings-toggle">
                        <input type="checkbox" checked>
                        <span class="settings-toggle-slider"></span>
                    </label>
                </div>
                <div class="settings-pref-row">
                    <div>
                        <strong>Community Updates</strong>
                        <p>Get notified when someone replies to your topics or stories.</p>
                    </div>
                    <label class="settings-toggle">
                        <input type="checkbox" checked>
                        <span class="settings-toggle-slider"></span>
                    </label>
                </div>
                <div class="settings-pref-row">
                    <div>
                        <strong>Price Alerts</strong>
                        <p>Notify me when flight prices drop for saved destinations.</p>
                    </div>
                    <label class="settings-toggle">
                        <input type="checkbox">
                        <span class="settings-toggle-slider"></span>
                    </label>
                </div>
                <div style="margin-top:20px;">
                    <a href="{{ route('profile.edit') }}" class="primary-button" style="text-decoration:none;">
                        <i class="fas fa-user"></i> Full Profile Settings
                    </a>
                </div>
            </div>
        </div>


        <div class="settings-tab" id="tab-danger" style="display:none;">
            <div class="settings-card settings-card--danger">
                <h2 class="settings-section-title" style="color:var(--danger);border-bottom-color:rgba(244,67,54,.2);">
                    <i class="fas fa-exclamation-triangle"></i> Danger Zone
                </h2>
                <p style="color:#6b5b4f;font-size:14px;margin-bottom:20px;line-height:1.6;">
                    Deleting your account is permanent and cannot be undone. All your trips, bookings, messages, and media will be removed.
                </p>
                <button class="danger-btn" onclick="confirmDelete()">
                    <i class="fas fa-trash-alt"></i> Delete My Account
                </button>
                <form method="POST" action="{{ route('profile.destroy') }}" id="deleteAccountForm" style="display:none;">
                    @csrf @method('DELETE')
                    <input type="hidden" name="password" id="deletePassword">
                </form>
            </div>
        </div>

    </div>
</div>

@endsection
