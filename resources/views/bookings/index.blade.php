@extends('layouts.authenticated')


@section('content')

@php
    $allBookings = $bookings ?? collect();
    $flightCount = $allBookings->whereNotNull('flight_id')->count();
    $hotelCount  = $allBookings->whereNotNull('hotel_id')->count();
    $activeCount = $allBookings->whereIn('status', ['confirmed', 'pending'])->count();
    $totalSpent  = $allBookings->whereNotIn('status', ['cancelled'])->sum('total_price');
@endphp

<div class="stats-strip">
    <div class="strip-card">
        <div class="strip-icon flights"><i class="fas fa-plane"></i></div>
        <div class="strip-info">
            <h3 id="statFlights">{{ $flightCount }}</h3>
            <p>Flight Bookings</p>
        </div>
    </div>
    <div class="strip-card">
        <div class="strip-icon hotels"><i class="fas fa-hotel"></i></div>
        <div class="strip-info">
            <h3 id="statHotels">{{ $hotelCount }}</h3>
            <p>Hotel Reservations</p>
        </div>
    </div>
    <div class="strip-card">
        <div class="strip-icon active"><i class="fas fa-check-circle"></i></div>
        <div class="strip-info">
            <h3 id="statActive">{{ $activeCount }}</h3>
            <p>Active Bookings</p>
        </div>
    </div>
    <div class="strip-card">
        <div class="strip-icon spent"><i class="fas fa-dollar-sign"></i></div>
        <div class="strip-info">
            <h3 id="statSpent">${{ number_format($totalSpent) }}</h3>
            <p>Total Spent</p>
        </div>
    </div>
</div>

<div class="filter-bar">
    <div class="filter-tabs" id="filterTabs">
        <button class="ftab active" data-filter="all"       onclick="filterBookings('all')">      <i class="fas fa-th-large"></i> All</button>
        <button class="ftab"        data-filter="flights"   onclick="filterBookings('flights')">  <i class="fas fa-plane"></i> Flights</button>
        <button class="ftab"        data-filter="hotels"    onclick="filterBookings('hotels')">   <i class="fas fa-hotel"></i> Hotels</button>
        <button class="ftab"        data-filter="trips"     onclick="filterBookings('trips')">    <i class="fas fa-route"></i> Trips</button>
        <button class="ftab"        data-filter="confirmed" onclick="filterBookings('confirmed')"><i class="fas fa-check-circle"></i> Confirmed</button>
        <button class="ftab"        data-filter="pending"   onclick="filterBookings('pending')">  <i class="fas fa-clock"></i> Pending</button>
    </div>
    <div class="filter-right">
        <select class="filter-select" id="sortSelect" onchange="sortBookings()">
            <option value="newest">Newest First</option>
            <option value="oldest">Oldest First</option>
            <option value="price-high">Price: High → Low</option>
            <option value="price-low">Price: Low → High</option>
            <option value="departure">Departure Date</option>
        </select>
        <div class="search-mini">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search bookings…" id="bookingSearch" oninput="searchBookings(this.value)">
        </div>
    </div>
</div>

<div class="bookings-section" id="bookingsList">
    <div class="bookings-grid" id="bookingsGrid">

        @forelse($allBookings as $booking)
            @php
                $type       = $booking->type;
                $typeIcon   = $type === 'flights' ? 'fa-plane' : ($type === 'trips' ? 'fa-route' : 'fa-hotel');
                $passengers = $booking->seats_booked ?? 1;
            @endphp

            <div class="booking-card status-{{ $booking->status }}"
                 data-type="{{ $type }}"
                 data-status="{{ $booking->status }}"
                 data-price="{{ $booking->total_price }}"
                 data-date="{{ $booking->created_at->format('Y-m-d') }}">

                <div class="booking-inner">
                    <div class="booking-type-icon {{ $type }}">
                        <i class="fas {{ $typeIcon }}"></i>
                    </div>
                    <div class="booking-info">
                        <h3>{{ $booking->title }}</h3>
                        <div class="booking-meta">
                            @if($booking->flight?->departure_time)
                                <span><i class="fas fa-calendar-alt"></i> {{ $booking->flight->departure_time->format('M j, Y') }}</span>
                            @endif
                            @if($booking->flight?->arrival_time)
                                <span><i class="fas fa-calendar-check"></i> {{ $booking->flight->arrival_time->format('M j, Y') }}</span>
                            @endif
                            @if($booking->hotel?->check_in)
                                <span><i class="fas fa-calendar-alt"></i> {{ \Carbon\Carbon::parse($booking->hotel->check_in)->format('M j, Y') }}</span>
                            @endif
                            @if($booking->hotel?->check_out)
                                <span><i class="fas fa-calendar-check"></i> {{ \Carbon\Carbon::parse($booking->hotel->check_out)->format('M j, Y') }}</span>
                            @endif
                            <span>
                                <i class="fas fa-users"></i>
                                {{ $passengers }} {{ Str::plural('Passenger', $passengers) }}
                            </span>
                            @if($booking->flight?->airline)
                                <span><i class="fas fa-plane-departure"></i> {{ $booking->flight->airline }}</span>
                            @endif
                            @if($booking->hotel?->name)
                                <span><i class="fas fa-bed"></i> {{ $booking->hotel->name }}</span>
                            @endif
                        </div>
                        <span class="booking-ref">REF: {{ $booking->booking_reference }}</span>
                    </div>
                    <div class="booking-price">
                        <div class="amount">${{ number_format($booking->total_price, 2) }}</div>
                        <div class="per">total</div>
                    </div>
                    <div class="booking-actions">
                        <span class="status-badge status-{{ $booking->status }}">
                            <i class="fas {{ $booking->isConfirmed() ? 'fa-check-circle' : ($booking->isPending() ? 'fa-clock' : ($booking->isCompleted() ? 'fa-flag-checkered' : 'fa-times-circle')) }}"></i>
                            {{ ucfirst($booking->status) }}
                        </span>
                        <div class="action-btns">
                            <button class="action-btn" onclick="toggleDetail('{{ $booking->id }}')">
                                <i class="fas fa-chevron-down"></i> Details
                            </button>
                            @if($booking->isActive())
                                <button class="action-btn danger" onclick="cancelBooking('{{ $booking->id }}')">
                                    <i class="fas fa-times"></i>
                                </button>
                            @elseif($booking->isCompleted())
                                <button class="action-btn primary" onclick="leaveReview('{{ $booking->id }}')">
                                    <i class="fas fa-star"></i> Review
                                </button>
                            @elseif($booking->isCancelled())
                                <button class="action-btn primary" onclick="rebookBooking('{{ $booking->id }}')">
                                    <i class="fas fa-redo"></i> Rebook
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="booking-detail-row" id="detail-{{ $booking->id }}">
                    <div class="detail-grid">
                        <div class="detail-item"><label>Booking Type</label><span>{{ ucfirst($type) }}</span></div>
                        <div class="detail-item"><label>Booking Date</label><span>{{ $booking->created_at->format('M j, Y H:i') }}</span></div>
                        <div class="detail-item"><label>Reference</label><span>{{ $booking->booking_reference }}</span></div>
                        <div class="detail-item"><label>Status</label><span>{{ ucfirst($booking->status) }}</span></div>
                        @if($booking->flight)
                            <div class="detail-item"><label>Flight</label><span>{{ $booking->flight->flight_number }} — {{ $booking->flight->airline }}</span></div>
                            <div class="detail-item"><label>Class</label><span>{{ ucfirst($booking->flight->class) }}</span></div>
                        @endif
                        @if($booking->hotel)
                            <div class="detail-item"><label>Hotel</label><span>{{ $booking->hotel->name }}</span></div>
                            <div class="detail-item"><label>Room</label><span>{{ $booking->hotel->room_type ?? 'Standard' }}</span></div>
                        @endif
                        @if($booking->trip)
                            <div class="detail-item"><label>Trip</label><span>{{ $booking->trip->name }}</span></div>
                        @endif
                        <div class="detail-item"><label>Passengers</label><span>{{ $passengers }}</span></div>
                        <div class="detail-item"><label>Total Paid</label><span>${{ number_format($booking->total_price, 2) }}</span></div>
                    </div>
                </div>

            </div>

        @empty
            <div class="empty-state">
                <i class="fas fa-ticket-alt"></i>
                <h3>No Bookings Yet</h3>
                <p>Your flights, hotels and trips will appear here once you make a booking.</p>
                <a href="{{ route('flights.index') }}" class="btn">
                    <i class="fas fa-plane"></i> Browse Flights
                </a>
            </div>
        @endforelse

    </div>
</div>

@endsection
