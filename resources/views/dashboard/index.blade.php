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
                    verified:  {{ Auth::user()->email_verified_at ? 'true' : 'false' }}
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
        $tripsCount = \App\Models\Trip::where('user_id', Auth::id())->where('status','planned')->count();
        $bookingsCount = \App\Models\Booking::where('user_id', Auth::id())->whereIn('status',['confirmed','pending'])->count();
        $staySearchesCount = \App\Models\AccommodationSearch::where('user_id', Auth::id())->count();
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
                    Your account is ready. Plan a trip, search flights or stays, and manage bookings from here.
                @else
                    Welcome back — here is a snapshot of your travel activity.
                @endif
            </p>
        </div>
        <a href="{{ route('plan-trip') }}" class="welcome-cta">
            <i class="fas fa-route"></i>
            Plan a Trip
        </a>
    </div>

    <div class="stats-grid">
        <div class="stat-card" onclick="window.location.href='/plan-trip'" style="cursor:pointer;">
            <div class="stat-icon trips"><i class="fas fa-route"></i></div>
            <div class="stat-info">
                <h3 id="statTripsCount">{{ $tripsCount }}</h3>
                <p>Planned Trips</p>
                <div class="stat-change"><span id="tripsSubtext">{{ $tripsCount > 0 ? $tripsCount.' trip'.($tripsCount!==1?'s':'').' planned' : 'No trips yet' }}</span></div>
            </div>
        </div>
        <div class="stat-card" onclick="window.location.href='/bookings'" style="cursor:pointer;">
            <div class="stat-icon bookings"><i class="fas fa-ticket-alt"></i></div>
            <div class="stat-info">
                <h3 id="statBookingsCount">{{ $bookingsCount }}</h3>
                <p>Active Bookings</p>
                <div class="stat-change"><span id="bookingsSubtext">{{ $bookingsCount > 0 ? $bookingsCount.' booking'.($bookingsCount!==1?'s':'').' active' : 'No bookings yet' }}</span></div>
            </div>
        </div>
        <div class="stat-card" onclick="window.location.href='/accommodations'" style="cursor:pointer;">
            <div class="stat-icon saved"><i class="fas fa-hotel"></i></div>
            <div class="stat-info">
                <h3 id="statStaySearchesCount">{{ $staySearchesCount }}</h3>
                <p>Stay Searches</p>
                <div class="stat-change"><span id="staySearchesSubtext">{{ $staySearchesCount > 0 ? $staySearchesCount.' search'.($staySearchesCount!==1?'es':'') : 'Browse accommodations' }}</span></div>
            </div>
        </div>
    </div>

    <div class="settings-shortcut-bar">
        <a href="{{ route('plan-trip') }}" class="settings-shortcut-item">
            <i class="fas fa-route"></i> Plan trip
        </a>
        <a href="{{ route('flights.index') }}" class="settings-shortcut-item">
            <i class="fas fa-plane"></i> Flights
        </a>
        <a href="{{ route('accommodations.index') }}" class="settings-shortcut-item">
            <i class="fas fa-hotel"></i> Stays
        </a>
        <a href="{{ route('bookings.index') }}" class="settings-shortcut-item">
            <i class="fas fa-ticket-alt"></i> Bookings
        </a>
    </div>

    <div class="actions-grid">
        <div class="action-btn" onclick="window.location.href='/plan-trip'">
            <i class="fas fa-plus-circle"></i><span>Plan Trip</span>
        </div>
        <div class="action-btn" onclick="window.location.href='/flights'">
            <i class="fas fa-plane"></i><span>Book Flights</span>
        </div>
        <div class="action-btn" onclick="window.location.href='/accommodations'">
            <i class="fas fa-hotel"></i><span>Find Stays</span>
        </div>
        <div class="action-btn" onclick="window.location.href='/bookings'">
            <i class="fas fa-ticket-alt"></i><span>My Bookings</span>
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
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Loading trips…</p>
                </div>
            </div>
        </div>

        <div class="dashboard-section">
            <div class="section-header">
                <h2><i class="fas fa-clock"></i> Recent Activity</h2>
                <a href="{{ route('bookings.index') }}" style="font-size:13px;color:var(--gold);text-decoration:none;">View bookings</a>
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
