@extends('layouts.authenticated')

@section('content')
<div class="stats-strip">
    <div class="strip-card">
        <div class="strip-icon flights"><i class="fas fa-plane-departure"></i></div>
        <div class="strip-info"><h3>{{ $publishedCount }}</h3><p>Published</p></div>
    </div>
    <div class="strip-card">
        <div class="strip-icon active"><i class="fas fa-edit"></i></div>
        <div class="strip-info"><h3>{{ $draftCount }}</h3><p>Drafts</p></div>
    </div>
    <div class="strip-card">
        <div class="strip-icon bookings"><i class="fas fa-inbox"></i></div>
        <div class="strip-info"><h3>{{ $incomingCount }}</h3><p>Incoming Bookings</p></div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="filter-bar">
    <h2>Create Flight Listing</h2>
</div>

<form method="POST" action="{{ route('agency.flights.store') }}" class="search-card">
    @csrf
    <div class="hero-form-grid">
        <div class="form-group"><label>Airline</label><input class="form-input" name="airline" value="{{ old('airline') }}" required></div>
        <div class="form-group"><label>Flight Number</label><input class="form-input" name="flight_number" value="{{ old('flight_number') }}" required></div>
    </div>
    <div class="hero-form-grid">
        <div class="form-group"><label>Departure Airport</label><input class="form-input" name="departure_airport" value="{{ old('departure_airport') }}" required></div>
        <div class="form-group"><label>Arrival Airport</label><input class="form-input" name="arrival_airport" value="{{ old('arrival_airport') }}" required></div>
    </div>
    <div class="hero-form-grid">
        <div class="form-group"><label>Departure IATA</label><input class="form-input" name="departure_iata" maxlength="3" value="{{ old('departure_iata') }}"></div>
        <div class="form-group"><label>Arrival IATA</label><input class="form-input" name="arrival_iata" maxlength="3" value="{{ old('arrival_iata') }}"></div>
    </div>
    <div class="hero-form-grid">
        <div class="form-group"><label>Date</label><input type="date" class="form-input" name="departure_date" value="{{ old('departure_date') }}" required></div>
        <div class="form-group"><label>Class</label>
            <select class="form-input" name="travel_class" required>
                @foreach(config('booking.travel_classes') as $class)
                    <option value="{{ $class }}" @selected(old('travel_class', 'ECONOMY') === $class)>{{ ucwords(strtolower(str_replace('_', ' ', $class))) }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="hero-form-grid">
        <div class="form-group"><label>Departure Time</label><input class="form-input" name="departure_time" value="{{ old('departure_time') }}"></div>
        <div class="form-group"><label>Arrival Time</label><input class="form-input" name="arrival_time" value="{{ old('arrival_time') }}"></div>
    </div>
    <div class="hero-form-grid">
        <div class="form-group"><label>Duration</label><input class="form-input" name="duration" value="{{ old('duration') }}"></div>
        <div class="form-group"><label>Price Per Seat</label><input type="number" step="0.01" min="0" class="form-input" name="price" value="{{ old('price') }}" required></div>
    </div>
    <div class="hero-form-grid">
        <div class="form-group"><label>Total Seats</label><input type="number" min="1" class="form-input" name="seats_total" value="{{ old('seats_total', 10) }}" required></div>
        <div class="form-group"><label>Status</label>
            <select class="form-input" name="status">
                <option value="draft">Draft</option>
                <option value="published">Published</option>
            </select>
        </div>
    </div>
    <button class="search-btn" type="submit"><i class="fas fa-save"></i> Save Listing</button>
</form>

<div class="bookings-section">
    <div class="bookings-grid">
        @forelse($listings as $listing)
            <div class="booking-card status-{{ $listing->status }}">
                <div class="booking-inner">
                    <div class="booking-type-icon flight"><i class="fas fa-plane"></i></div>
                    <div class="booking-info">
                        <h3>{{ $listing->airline }} {{ $listing->flight_number }}</h3>
                        <div class="booking-meta">
                            <span>{{ $listing->departure_airport }} → {{ $listing->arrival_airport }}</span>
                            <span>{{ $listing->departure_date->format('M j, Y') }}</span>
                            <span>{{ $listing->seats_available }} / {{ $listing->seats_total }} seats</span>
                        </div>
                    </div>
                    <div class="booking-price"><div class="amount" data-price-usd="{{ $listing->price }}">${{ number_format((float) $listing->price, 2) }}</div><div class="per">per seat</div></div>
                    <div class="booking-actions">
                        <span class="status-badge status-{{ $listing->status }}">{{ ucfirst($listing->status) }}</span>
                        <div class="action-btns">
                            @if($listing->status !== 'published')
                                <form method="POST" action="{{ route('agency.flights.publish', $listing) }}">@csrf<button class="action-btn primary"><i class="fas fa-upload"></i> Publish</button></form>
                            @endif
                            @if($listing->status !== 'archived')
                                <form method="POST" action="{{ route('agency.flights.archive', $listing) }}">@csrf<button class="action-btn danger"><i class="fas fa-box-archive"></i></button></form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="empty-state"><i class="fas fa-plane"></i><h3>No Listings Yet</h3><p>Create your first flight listing above.</p></div>
        @endforelse
    </div>
    {{ $listings->links() }}
</div>
@endsection
