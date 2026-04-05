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

@push('styles')
<style>
.settings-wrap {
    display: grid;
    grid-template-columns: 200px 1fr;
    gap: 24px;
    align-items: start;
    max-width: 900px;
}

.settings-nav {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 10px;
    overflow: hidden;
    position: sticky;
    top: 20px;
}

.settings-nav-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 13px 18px;
    color: var(--text-muted);
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    border-left: 3px solid transparent;
    transition: all .2s;
    cursor: pointer;
    border-bottom: 1px solid var(--border);
}

.settings-nav-item:last-child { border-bottom: none; }

.settings-nav-item:hover,
.settings-nav-item.active {
    background: rgba(201,169,110,.08);
    color: var(--deep);
    border-left-color: var(--gold);
}

.settings-nav-item i { width: 16px; text-align: center; color: var(--gold); }
.settings-nav-item--danger:hover,
.settings-nav-item--danger.active { border-left-color: var(--danger); color: var(--danger); }
.settings-nav-item--danger i { color: var(--danger); }

.settings-card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 28px;
    box-shadow: 0 3px 10px rgba(59,31,43,.06);
}

.settings-card--danger { border-color: rgba(244,67,54,.25); }

.settings-section-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--deep);
    margin: 0 0 20px;
    padding-bottom: 12px;
    border-bottom: 2px solid var(--border);
    display: flex;
    align-items: center;
    gap: 8px;
}

.settings-section-title i { color: var(--gold); }

.settings-card-header {
    display: flex;
    align-items: center;
    gap: 16px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--border);
    margin-bottom: 4px;
}

.settings-avatar-wrap {
    position: relative;
    width: 64px;
    height: 64px;
    flex-shrink: 0;
}

.settings-avatar-wrap img,
.settings-avatar-initials {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--gold);
}

.settings-avatar-initials {
    background: linear-gradient(135deg, var(--gold), var(--deep-alt));
    color: #fff;
    font-size: 22px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
}

.settings-avatar-btn {
    position: absolute;
    inset: 0;
    border-radius: 50%;
    background: rgba(0,0,0,.45);
    color: #fff;
    font-size: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    cursor: pointer;
    transition: opacity .2s;
}

.settings-avatar-wrap:hover .settings-avatar-btn { opacity: 1; }

.settings-info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid var(--border);
    font-size: 14px;
    color: var(--text-muted);
}

.settings-info-row:last-child { border-bottom: none; }
.settings-info-row i { color: var(--gold); margin-right: 6px; }

.settings-pref-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 0;
    border-bottom: 1px solid var(--border);
    gap: 16px;
}

.settings-pref-row:last-of-type { border-bottom: none; }
.settings-pref-row strong { display: block; font-size: 14px; color: var(--deep); margin-bottom: 3px; }
.settings-pref-row p { font-size: 12px; color: var(--text-muted); margin: 0; }

.settings-toggle {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px;
    flex-shrink: 0;
}

.settings-toggle input { opacity: 0; width: 0; height: 0; }

.settings-toggle-slider {
    position: absolute;
    inset: 0;
    background: var(--border);
    border-radius: 24px;
    cursor: pointer;
    transition: background .2s;
}

.settings-toggle-slider::before {
    content: '';
    position: absolute;
    width: 18px;
    height: 18px;
    left: 3px;
    top: 3px;
    background: #fff;
    border-radius: 50%;
    transition: transform .2s;
    box-shadow: 0 1px 3px rgba(0,0,0,.2);
}

.settings-toggle input:checked + .settings-toggle-slider { background: var(--gold); }
.settings-toggle input:checked + .settings-toggle-slider::before { transform: translateX(20px); }

@media (max-width: 768px) {
    .settings-wrap { grid-template-columns: 1fr; }
    .settings-nav { position: static; display: flex; overflow-x: auto; }
    .settings-nav-item { border-bottom: none; border-left: none; border-bottom: 3px solid transparent; white-space: nowrap; }
    .settings-nav-item.active { border-bottom-color: var(--gold); border-left: none; }
}
</style>
@endpush

@push('scripts')
<script>
function showTab(name, el) {
    document.querySelectorAll('.settings-tab').forEach(t => t.style.display = 'none');
    document.querySelectorAll('.settings-nav-item').forEach(n => n.classList.remove('active'));
    const tab = document.getElementById('tab-' + name);
    if (tab) tab.style.display = 'block';
    if (el) el.classList.add('active');
    return false;
}

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

// Auto-show tab from hash
(function () {
    const hash = window.location.hash.replace('#', '');
    if (hash) {
        const el = document.querySelector('.settings-nav-item[href="#' + hash + '"]');
        if (el) showTab(hash, el);
    }
})();
</script>
@endpush