@extends('layouts.authenticated')

@section('title', 'Profile — Smart Booking')
@section('page-title', 'My Profile')
@section('page-description', 'Manage your account settings and preferences')

@section('content')

@if(session('status'))
<div class="profile-alert profile-alert--{{ session('status') === 'profile-updated' || session('status') === 'profile-picture-updated' || session('status') === 'password-updated' ? 'success' : 'info' }}">
    <i class="fas fa-check-circle"></i>
    @switch(session('status'))
        @case('profile-updated') Profile updated successfully. @break
        @case('profile-picture-updated') Profile picture updated. @break
        @case('profile-picture-deleted') Profile picture removed. @break
        @case('password-updated') Password changed successfully. @break
        @default Changes saved.
    @endswitch
</div>
@endif

<div class="profile-wrap">

    
    <div class="profile-sidebar">
        <div class="profile-avatar-card">
            <div class="profile-avatar-wrap" id="avatarWrap">
                @if($user->profile_picture)
                    <img src="{{ asset('storage/' . $user->profile_picture) }}" alt="{{ $user->name }}" id="avatarImg">
                @else
                    <div class="profile-avatar-initials" id="avatarInitials">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                @endif
                <label class="avatar-upload-overlay" for="pictureInput" title="Change photo">
                    <i class="fas fa-camera"></i>
                </label>
            </div>

            <h3 class="profile-name">{{ $user->name }}</h3>
            <span class="profile-type-badge profile-type-badge--{{ $user->user_type ?? 'traveler' }}">
                <i class="fas fa-{{ $user->user_type === 'agency' ? 'building' : 'suitcase' }}"></i>
                {{ ucfirst($user->user_type ?? 'Traveler') }}
            </span>
            @if($user->hasVerifiedEmail())
                <span class="profile-verified"><i class="fas fa-check-circle"></i> Verified</span>
            @endif

            
            <form method="POST" action="{{ route('profile.picture.upload') }}" enctype="multipart/form-data" id="pictureForm">
                @csrf
                <input type="file" id="pictureInput" name="profile_picture" accept="image/*" style="display:none"
                       onchange="document.getElementById('pictureForm').submit()">
            </form>

            @if($user->profile_picture)
            <form method="POST" action="{{ route('profile.picture.delete') }}" style="margin-top:8px;">
                @csrf @method('DELETE')
                <button type="submit" class="profile-remove-pic-btn">
                    <i class="fas fa-trash-alt"></i> Remove photo
                </button>
            </form>
            @endif
        </div>

        
        <div class="profile-stats-card">
            <div class="profile-stat">
                <span class="ps-num" id="profileTrips">—</span>
                <span class="ps-label">Trips</span>
            </div>
            <div class="profile-stat">
                <span class="ps-num" id="profileBookings">—</span>
                <span class="ps-label">Bookings</span>
            </div>
            <div class="profile-stat">
                <span class="ps-num" id="profileSaved">—</span>
                <span class="ps-label">Saved</span>
            </div>
        </div>
    </div>

    
    <div class="profile-forms">

        
        <div class="profile-card">
            <h2><i class="fas fa-user-edit"></i> Profile Information</h2>

            <form method="POST" action="{{ route('profile.update') }}">
                @csrf @method('PATCH')

                <div class="pf-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                    @error('name') <span class="pf-error">{{ $message }}</span> @enderror
                </div>

                <div class="pf-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                    @error('email') <span class="pf-error">{{ $message }}</span> @enderror
                    @if($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
                        <p class="pf-hint"><i class="fas fa-exclamation-circle"></i> Email not verified.
                            <form method="POST" action="{{ route('verification.send') }}" style="display:inline;">
                                @csrf
                                <button type="submit" class="pf-link-btn">Resend verification</button>
                            </form>
                        </p>
                    @endif
                </div>

                @if($user->user_type === 'agency')
                <div class="pf-group">
                    <label for="agency_name">Agency Name</label>
                    <input type="text" id="agency_name" name="agency_name" value="{{ old('agency_name', $user->agency_name) }}">
                </div>
                @endif

                <button type="submit" class="primary-button">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </form>
        </div>

        
        <div class="profile-card">
            <h2><i class="fas fa-lock"></i> Change Password</h2>

            <form method="POST" action="{{ route('password.update') }}">
                @csrf @method('PUT')

                <div class="pf-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" autocomplete="current-password">
                    @error('current_password', 'updatePassword') <span class="pf-error">{{ $message }}</span> @enderror
                </div>

                <div class="pf-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="password" autocomplete="new-password">
                    @error('password', 'updatePassword') <span class="pf-error">{{ $message }}</span> @enderror
                </div>

                <div class="pf-group">
                    <label for="password_confirmation">Confirm New Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password">
                </div>

                <button type="submit" class="primary-button">
                    <i class="fas fa-key"></i> Update Password
                </button>
            </form>
        </div>

        
        <div class="profile-card profile-card--danger">
            <h2><i class="fas fa-exclamation-triangle"></i> Danger Zone</h2>
            <p style="color:#6b5b4f;font-size:14px;margin-bottom:16px;">
                Once you delete your account, all data will be permanently removed.
            </p>
            <button class="danger-btn" onclick="confirmDelete()">
                <i class="fas fa-trash-alt"></i> Delete Account
            </button>

            <form method="POST" action="{{ route('profile.destroy') }}" id="deleteAccountForm" style="display:none;">
                @csrf @method('DELETE')
                <input type="hidden" name="password" id="deletePassword">
            </form>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
// Load stats
fetch('/api/user/statistics', { headers: { 'Accept': 'application/json' } })
    .then(r => r.json())
    .then(d => {
        const set = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
        set('profileTrips',    d.trips    ?? 0);
        set('profileBookings', d.bookings ?? 0);
        set('profileSaved',    d.saved    ?? 0);
    }).catch(() => {});

// Delete account confirmation
window.confirmDelete = function () {
    if (typeof Swal === 'undefined') {
        const pw = prompt('Enter your password to confirm account deletion:');
        if (!pw) return;
        document.getElementById('deletePassword').value = pw;
        document.getElementById('deleteAccountForm').submit();
        return;
    }
    Swal.fire({
        title: 'Delete Account?',
        html: '<p style="color:#6b5b4f;margin-bottom:12px;">This cannot be undone. Enter your password to confirm.</p>' +
              '<input type="password" id="swalPw" class="swal2-input" placeholder="Your password">',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f44336',
        cancelButtonColor: '#6b5b4f',
        confirmButtonText: 'Yes, delete my account',
        preConfirm: () => {
            const pw = document.getElementById('swalPw').value;
            if (!pw) { Swal.showValidationMessage('Password is required'); return false; }
            return pw;
        }
    }).then(result => {
        if (result.isConfirmed) {
            document.getElementById('deletePassword').value = result.value;
            document.getElementById('deleteAccountForm').submit();
        }
    });
};
</script>
@endpush