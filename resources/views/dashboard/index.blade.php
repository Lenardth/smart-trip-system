@extends('layouts.authenticated')

@section('title', 'Dashboard — Smart Booking')
@section('page-class', 'main-content')
@section('page-id', 'mainContent')

@push('scripts')
    <script>
        (function () {
            window.__dashboardConfig = {
                pusherKey:     "{{ config('broadcasting.connections.pusher.key') }}",
                pusherCluster: "{{ config('broadcasting.connections.pusher.options.cluster', 'mt1') }}",
                userId: {{ Auth::id() ?? 'null' }},
                user: {
                    id:        {{ Auth::id() ?? 'null' }},
                    name:      @json(Auth::user()->name ?? ''),
                    firstName: @json(Auth::check() ? explode(' ', Auth::user()->name)[0] : ''),
                    avatar:    @json(Auth::user()->avatar ?? ''),
                    type:      @json(Auth::user()->user_type ?? ''),
                    verified:  {{ Auth::user()->hasVerifiedEmail() ? 'true' : 'false' }}
                }
            };
        })();
    </script>
@endpush

@section('content')

    @php
        $user      = Auth::user();
        $firstName = explode(' ', $user->name)[0];
        $isNew     = $user->created_at->diffInDays(now()) < 1;
        $hour      = now()->hour;
        $greeting  = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    @endphp

    <div class="welcome-banner">
        <div class="welcome-avatar">
            @if($user->profile_picture)
                <img src="{{ asset('storage/'.$user->profile_picture) }}"
                     alt="{{ $user->name }}"
                     style="width:100%;height:100%;object-fit:cover;border-radius:50%;"
                     onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                <span style="display:none;">{{ strtoupper(substr($user->name,0,1)) }}</span>
            @else
                <span>{{ strtoupper(substr($user->name,0,1)) }}</span>
            @endif
        </div>
        <div class="welcome-text">
            <h2 id="welcomeMessage">{{ $greeting }}, {{ $firstName }}!</h2>
            <p id="welcomeSubtext">
                @if($isNew)
                    Your account is all set. Start by planning your first trip or exploring destinations.
                @else
                    Welcome back — here's what's happening with your travels today.
                @endif
            </p>
        </div>
        <a href="{{ route('plan-trip') }}" class="welcome-cta">
            <i class="fas fa-route"></i>
            Plan a Trip
        </a>
    </div>

    <div class="stats-grid">
        <div class="stat-card" onclick="openGallery()" style="cursor:pointer;">
            <div class="stat-icon photos"><i class="fas fa-images"></i></div>
            <div class="stat-info">
                @php $photos = \App\Models\Media::where('user_id', Auth::id())->count(); @endphp
                <h3 id="statPhotosCount">{{ $photos }}</h3>
                <p>Total Photos</p>
                <div class="stat-change"><span id="photosSubtext">{{ $photos > 0 ? $photos.' file'.($photos!==1?'s':'').' uploaded' : 'Upload to get started' }}</span></div>
            </div>
        </div>
        <div class="stat-card" onclick="window.location.href='/plan-trip'" style="cursor:pointer;">
            <div class="stat-icon trips"><i class="fas fa-route"></i></div>
            <div class="stat-info">
                @php $trips = \App\Models\Trip::where('user_id', Auth::id())->where('status','planned')->count(); @endphp
                <h3 id="statTripsCount">{{ $trips }}</h3>
                <p>Planned Trips</p>
                <div class="stat-change"><span id="tripsSubtext">{{ $trips > 0 ? $trips.' trip'.($trips!==1?'s':'').' planned' : 'No trips yet' }}</span></div>
            </div>
        </div>
        <div class="stat-card" onclick="window.location.href='/bookings'" style="cursor:pointer;">
            <div class="stat-icon bookings"><i class="fas fa-ticket-alt"></i></div>
            <div class="stat-info">
                @php $bookings = \App\Models\Booking::where('user_id', Auth::id())->whereIn('status',['confirmed','pending'])->count(); @endphp
                <h3 id="statBookingsCount">{{ $bookings }}</h3>
                <p>Active Bookings</p>
                <div class="stat-change"><span id="bookingsSubtext">{{ $bookings > 0 ? $bookings.' booking'.($bookings!==1?'s':'').' active' : 'No bookings yet' }}</span></div>
            </div>
        </div>
        <div class="stat-card" onclick="window.location.href='/wishlist'" style="cursor:pointer;">
            <div class="stat-icon saved"><i class="fas fa-heart"></i></div>
            <div class="stat-info">
                @php $saved = \App\Models\SavedDestination::where('user_id', Auth::id())->count(); @endphp
                <h3 id="statSavedCount">{{ $saved }}</h3>
                <p>Saved Places</p>
                <div class="stat-change"><span id="savedSubtext">{{ $saved > 0 ? $saved.' place'.($saved!==1?'s':'').' saved' : 'View your wishlist' }}</span></div>
            </div>
        </div>
    </div>

    <div class="settings-shortcut-bar">
        <a href="{{ route('profile.edit') }}" class="settings-shortcut-item">
            <i class="fas fa-user-edit"></i> Edit Profile
        </a>
        <a href="{{ route('notifications.index') }}" class="settings-shortcut-item">
            <i class="fas fa-bell"></i> Notifications
        </a>
        <a href="{{ route('wishlist.index') }}" class="settings-shortcut-item">
            <i class="fas fa-heart"></i> Wishlist
        </a>
        <a href="{{ route('bookings.index') }}" class="settings-shortcut-item">
            <i class="fas fa-ticket-alt"></i> Bookings
        </a>
        <a href="{{ route('settings') }}" class="settings-shortcut-item">
            <i class="fas fa-cog"></i> Settings
        </a>
    </div>

    <div class="actions-grid">
        <div class="action-btn" onclick="triggerCamera ? triggerCamera() : uploadPhotos()">
            <i class="fas fa-camera"></i><span>Take Photo</span>
        </div>
        <div class="action-btn" onclick="uploadPhotos()">
            <i class="fas fa-upload"></i><span>Upload Photos</span>
        </div>
        <div class="action-btn" onclick="window.location.href='/plan-trip'">
            <i class="fas fa-plus-circle"></i><span>Plan Trip</span>
        </div>
        <div class="action-btn" onclick="window.location.href='/flights'">
            <i class="fas fa-plane"></i><span>Book Flights</span>
        </div>
        <div class="action-btn" onclick="window.location.href='/discover'">
            <i class="fas fa-compass"></i><span>Discover</span>
        </div>
        <div class="action-btn" onclick="window.location.href='/community'">
            <i class="fas fa-users"></i><span>Community</span>
        </div>
    </div>

    <div class="dashboard-grid">
        <div class="dashboard-section">
            <div class="section-header">
                <h2><i class="fas fa-route"></i> Upcoming Trips</h2>
                <button class="btn" onclick="window.location.href='/plan-trip'">
                    <i class="fas fa-plus"></i> New Trip
                </button>
            </div>
            <div class="section-content" id="upcomingTripsContent">
                <div class="empty-state">
                    <i class="fas fa-route"></i>
                    <h3>No Trips Planned Yet</h3>
                    <p>Start planning your next adventure!</p>
                    <button class="btn" onclick="window.location.href='/plan-trip'">
                        <i class="fas fa-plus"></i> Create Your First Trip
                    </button>
                </div>
            </div>
        </div>

        <div class="dashboard-section">
            <div class="section-header">
                <h2><i class="fas fa-clock"></i> Recent Activity</h2>
                <a href="{{ route('bookings.index') }}" style="font-size:13px;color:var(--gold);text-decoration:none;">View all</a>
            </div>
            <div class="section-content" id="recentActivityContent">
                <div class="empty-state">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Loading activity…</p>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('modals')

    <div class="gallery-modal" id="galleryModal">
        <div class="gallery-header">
            <h3><i class="fas fa-images"></i> My Photos &amp; Videos</h3>
            <button class="gallery-close" onclick="closeGallery()"><i class="fas fa-times"></i></button>
        </div>
        <div class="gallery-content" id="galleryContent">

            {{-- Upload options row --}}
            <div class="upload-options-row">
                <div class="upload-option-btn" onclick="triggerFileInput()">
                    <i class="fas fa-folder-open"></i>
                    <span>Browse Files</span>
                </div>
                <div class="upload-option-btn upload-option-camera" onclick="triggerCamera()">
                    <i class="fas fa-camera"></i>
                    <span>Take Photo</span>
                </div>
                <div class="upload-option-btn" onclick="triggerVideoInput()">
                    <i class="fas fa-video"></i>
                    <span>Record Video</span>
                </div>
            </div>

            {{-- Hidden file inputs --}}
            <input type="file" id="mediaInput"       multiple accept="image/*,video/*"          style="display:none;" onchange="handleFileSelect(event)">
            <input type="file" id="cameraInput"               accept="image/*"    capture="environment" style="display:none;" onchange="handleFileSelect(event)">
            <input type="file" id="videoInput"                accept="video/*"    capture="environment" style="display:none;" onchange="handleFileSelect(event)">

            {{-- Upload drop zone --}}
            <div class="upload-area" id="uploadArea">
                <i class="fas fa-cloud-upload-alt"></i>
                <p>Drag &amp; drop files here</p>
            </div>

            <div class="gallery-grid" id="galleryGrid"></div>
        </div>
        <div class="gallery-toolbar">
            <button onclick="triggerFileInput()"  title="Add files"><i class="fas fa-plus"></i></button>
            <button onclick="triggerCamera()"     title="Camera"><i class="fas fa-camera"></i></button>
            <button onclick="selectAll()"         title="Select all"><i class="fas fa-check-double"></i></button>
            <button onclick="deleteSelected()"    title="Delete selected"><i class="fas fa-trash"></i></button>
        </div>
    </div>

    {{-- ── Unified full-screen viewer + editor ─────────────────────────────── --}}
    {{-- Tapping a photo opens this. Edit tools are always visible inline.     --}}
    <div class="media-viewer" id="mediaViewer">

        {{-- Top bar --}}
        <div class="viewer-header">
            <button class="gallery-close" onclick="closeViewer()">
                <i class="fas fa-arrow-left"></i>
            </button>
            <span class="viewer-title" id="viewerTitle"></span>
            <div class="viewer-header-actions">
                <button onclick="downloadMedia()"  title="Download"><i class="fas fa-download"></i></button>
                <button onclick="deleteMedia()"    title="Delete" style="color:#ff453a;"><i class="fas fa-trash"></i></button>
            </div>
        </div>

        {{-- Media display --}}
        <div class="viewer-media-wrap" id="viewerMediaWrap">
            {{-- Image shown here, canvas overlaid when editing --}}
            <img id="viewerImg" style="display:none;max-width:100%;max-height:100%;object-fit:contain;">
            <video id="viewerVideo" style="display:none;max-width:100%;max-height:100%;" controls></video>
            <canvas id="peCanvas" style="display:none;max-width:100%;max-height:100%;object-fit:contain;"></canvas>
        </div>

        {{-- Edit mode toggle (images only) --}}
        <div class="viewer-edit-toggle" id="viewerEditToggle" style="display:none;">
            <button class="ve-toggle-btn active" id="btnViewMode"  onclick="setViewerMode('view')"><i class="fas fa-eye"></i> View</button>
            <button class="ve-toggle-btn"        id="btnEditMode"  onclick="setViewerMode('edit')"><i class="fas fa-magic"></i> Edit</button>
        </div>

        {{-- Edit tools (hidden in view mode) --}}
        <div class="viewer-edit-tools" id="viewerEditTools" style="display:none;">

            {{-- Tab bar --}}
            <div class="pe-tabs">
                <button class="pe-tab active" onclick="peSwitchTab('adjust',this)"><i class="fas fa-sliders-h"></i><span>Adjust</span></button>
                <button class="pe-tab"        onclick="peSwitchTab('filters',this)"><i class="fas fa-magic"></i><span>Filters</span></button>
                <button class="pe-tab"        onclick="peSwitchTab('crop',this)"><i class="fas fa-crop-alt"></i><span>Crop</span></button>
            </div>

            {{-- Adjust --}}
            <div class="pe-panel" id="pe-panel-adjust">
                <div class="pe-sliders">
                    <div class="pe-slider-row"><span><i class="fas fa-sun"></i> Brightness</span><input type="range" id="peBrightness" min="-100" max="100" value="0" oninput="peApply()"><span id="peBrightnessVal">0</span></div>
                    <div class="pe-slider-row"><span><i class="fas fa-adjust"></i> Contrast</span><input type="range" id="peContrast" min="-100" max="100" value="0" oninput="peApply()"><span id="peContrastVal">0</span></div>
                    <div class="pe-slider-row"><span><i class="fas fa-palette"></i> Saturation</span><input type="range" id="peSaturation" min="-100" max="100" value="0" oninput="peApply()"><span id="peSaturationVal">0</span></div>
                    <div class="pe-slider-row"><span><i class="fas fa-thermometer-half"></i> Warmth</span><input type="range" id="peWarmth" min="-100" max="100" value="0" oninput="peApply()"><span id="peWarmthVal">0</span></div>
                    <div class="pe-slider-row"><span><i class="fas fa-circle" style="opacity:.4;"></i> Vignette</span><input type="range" id="peVignette" min="0" max="100" value="0" oninput="peApply()"><span id="peVignetteVal">0</span></div>
                </div>
            </div>

            {{-- Filters --}}
            <div class="pe-panel" id="pe-panel-filters" style="display:none;">
                <div class="pe-filters-row">
                    @foreach(['none'=>'Original','vivid'=>'Vivid','dramatic'=>'Dramatic','mono'=>'Mono','silvertone'=>'Silvertone','noir'=>'Noir','fade'=>'Fade','warm'=>'Warm','cool'=>'Cool'] as $key=>$label)
                    <div class="pe-filter-item {{ $key==='none'?'active':'' }}" onclick="peSetFilter('{{ $key }}',this)">
                        <canvas class="pe-filter-thumb" data-filter="{{ $key }}"></canvas>
                        <span>{{ $label }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Crop --}}
            <div class="pe-panel" id="pe-panel-crop" style="display:none;">
                <div class="pe-crop-ratios">
                    <button class="pe-ratio-btn active" onclick="peCropRatio('free',this)"><i class="fas fa-expand"></i> Free</button>
                    <button class="pe-ratio-btn" onclick="peCropRatio('1:1',this)">1:1</button>
                    <button class="pe-ratio-btn" onclick="peCropRatio('4:3',this)">4:3</button>
                    <button class="pe-ratio-btn" onclick="peCropRatio('16:9',this)">16:9</button>
                    <button class="pe-ratio-btn" onclick="peCropRatio('3:2',this)">3:2</button>
                </div>
                <div style="text-align:center;margin-top:10px;display:flex;gap:8px;justify-content:center;">
                    <button class="primary-button" onclick="peApplyCrop()" style="padding:9px 20px;font-size:13px;"><i class="fas fa-check"></i> Apply</button>
                    <button class="secondary-button" onclick="peResetCrop()" style="padding:9px 20px;font-size:13px;">Reset</button>
                </div>
            </div>

            {{-- Save / Reset row --}}
            <div class="pe-info-row">
                <button class="pe-btn-text" onclick="peReset()" style="color:#ff453a;"><i class="fas fa-undo"></i> Reset</button>
                <button class="pe-btn-text pe-btn-done" onclick="savePhotoEdit()"><i class="fas fa-cloud-upload-alt"></i> Save Edit</button>
            </div>
        </div>

    </div>

    {{-- Metadata modal (title / location / favourite) --}}
    <div class="modal-overlay" id="editMediaModal">
        <div class="modal" style="max-width:420px;">
            <div class="modal-header">
                <h2><i class="fas fa-tag" style="color:var(--gold);margin-right:8px;"></i> Photo Details</h2>
                <button class="modal-close" onclick="closeEditMedia()">&#x2715;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" id="editMediaTitle" class="auth-input" placeholder="Photo title">
                </div>
                <div class="form-group">
                    <label>Location</label>
                    <input type="text" id="editMediaLocation" class="auth-input" placeholder="Where was this taken?">
                </div>
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
                    <input type="checkbox" id="editMediaFavorite" style="accent-color:var(--gold);width:16px;height:16px;">
                    <label for="editMediaFavorite" style="font-size:14px;color:var(--deep);cursor:pointer;">
                        <i class="fas fa-star" style="color:var(--gold);margin-right:4px;"></i> Mark as favourite
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button class="secondary-button" onclick="closeEditMedia()">Cancel</button>
                <button class="primary-button" onclick="saveMediaEdit()"><i class="fas fa-save"></i> Save</button>
            </div>
        </div>
    </div>

@endpush