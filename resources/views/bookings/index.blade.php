@extends('layouts.authenticated')

@section('content')

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
            <h3 id="statSpent" data-price-usd="{{ $totalSpent }}">${{ number_format($totalSpent) }}</h3>
            <p>Total Spent</p>
        </div>
    </div>
</div>

<div class="filter-bar">
    <div class="filter-tabs" id="filterTabs">
        <button class="ftab active" data-filter="all" data-action="filterBookings" data-params='{"args":["all"]}'><i class="fas fa-th-large"></i> All</button>
        <button class="ftab" data-filter="flight" data-action="filterBookings" data-params='{"args":["flight"]}'><i class="fas fa-plane"></i> Flights</button>
        <button class="ftab" data-filter="hotels" data-action="filterBookings" data-params='{"args":["hotels"]}'><i class="fas fa-hotel"></i> Hotels</button>
        <button class="ftab" data-filter="trips" data-action="filterBookings" data-params='{"args":["trips"]}'><i class="fas fa-route"></i> Trips</button>
        <button class="ftab" data-filter="confirmed" data-action="filterBookings" data-params='{"args":["confirmed"]}'><i class="fas fa-check-circle"></i> Confirmed</button>
        <button class="ftab" data-filter="pending" data-action="filterBookings" data-params='{"args":["pending"]}'><i class="fas fa-clock"></i> Pending</button>
    </div>
    <div class="filter-right">
        <select class="filter-select" id="sortSelect" data-change-action="sortBookings">
            <option value="newest">Newest First</option>
            <option value="oldest">Oldest First</option>
            <option value="price-high">Price: High → Low</option>
            <option value="price-low">Price: Low → High</option>
            <option value="departure">Departure Date</option>
        </select>
        <div class="search-mini">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search bookings…" id="bookingSearch" data-input-action="searchBookings">
        </div>
    </div>
</div>

<div class="bookings-section" id="bookingsList">
    <div class="bookings-grid" id="bookingsGrid">

        @forelse($bookings ?? [] as $booking)

            <div class="booking-card status-{{ $booking->status }}"
                 data-type="{{ $booking->type }}"
                 data-status="{{ $booking->status }}"
                 data-price="{{ $booking->total_price }}"
                 data-date="{{ $booking->created_at->format('Y-m-d') }}">

                <div class="booking-inner">
                    <div class="booking-type-icon {{ $booking->type }}">
                        <i class="fas {{ config('booking.type_icons.' . $booking->type, 'fa-hotel') }}"></i>
                    </div>
                    <div class="booking-info">
                        <h3>{{ $booking->title }}</h3>
                        <div class="booking-meta">
                            @if(isset($booking->passenger_details['departure_date']))
                                <span><i class="fas fa-calendar-alt"></i> {{ \Carbon\Carbon::parse($booking->passenger_details['departure_date'])->format('M j, Y') }}</span>
                            @endif
                            @if(isset($booking->passenger_details['check_in']))
                                <span><i class="fas fa-calendar-alt"></i> {{ \Carbon\Carbon::parse($booking->passenger_details['check_in'])->format('M j, Y') }}</span>
                            @endif
                            @if(isset($booking->passenger_details['check_out']))
                                <span><i class="fas fa-calendar-check"></i> {{ \Carbon\Carbon::parse($booking->passenger_details['check_out'])->format('M j, Y') }}</span>
                            @endif
                            <span>
                                <i class="fas fa-users"></i>
                                {{ $booking->seats_booked ?? 1 }} {{ Str::plural('Passenger', $booking->seats_booked ?? 1) }}
                            </span>
                            @if(isset($booking->passenger_details['airline']))
                                <span><i class="fas fa-plane-departure"></i> {{ $booking->passenger_details['airline'] }}</span>
                            @endif
                            @if(($booking->passenger_details && isset($booking->passenger_details['name'])) && $booking->type === 'hotels')
                                <span><i class="fas fa-bed"></i> {{ $booking->passenger_details['name'] }}</span>
                            @endif
                        </div>
                        <span class="booking-ref">REF: {{ $booking->booking_reference }}</span>
                    </div>
                    <div class="booking-price">
                        <div class="amount" data-price-usd="{{ $booking->total_price }}">${{ number_format($booking->total_price, 2) }}</div>
                        <div class="per">total</div>
                    </div>
                    <div class="booking-actions">
                        <span class="status-badge status-{{ $booking->status }}">
                            <i class="fas {{ $booking->status === 'confirmed' ? 'fa-check-circle' : ($booking->status === 'pending' ? 'fa-clock' : ($booking->status === 'completed' ? 'fa-flag-checkered' : 'fa-times-circle')) }}"></i>
                            {{ ucfirst($booking->status) }}
                        </span>
                        <div class="action-btns">
                            <button class="action-btn" data-action="toggleDetail" data-params='{"args":["{{ $booking->id }}"]}'>
                                <i class="fas fa-chevron-down"></i> Details
                            </button>
                            @if(in_array($booking->status, ['confirmed', 'pending']))
                                <button class="action-btn danger" data-action="cancelBooking" data-params='{"args":["{{ $booking->id }}"]}'>
                                    <i class="fas fa-times"></i>
                                </button>
                            @elseif($booking->status === 'completed')
                                <button class="action-btn primary" data-action="leaveReview" data-params='{"args":["{{ $booking->id }}"]}'>
                                    <i class="fas fa-star"></i> Review
                                </button>
                            @elseif($booking->status === 'cancelled')
                                <button class="action-btn primary" data-action="rebookBooking" data-params='{"args":["{{ $booking->type }}"]}'>
                                    <i class="fas fa-redo"></i> Rebook
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="booking-detail-row" id="detail-{{ $booking->id }}">
                    <div class="detail-grid">
                        <div class="detail-item"><label>Booking Type</label><span>{{ ucfirst($booking->type) }}</span></div>
                        <div class="detail-item"><label>Booking Date</label><span>{{ $booking->created_at->format('M j, Y H:i') }}</span></div>
                        <div class="detail-item"><label>Reference</label><span>{{ $booking->booking_reference }}</span></div>
                        <div class="detail-item"><label>Status</label><span>{{ ucfirst($booking->status) }}</span></div>
                        @if($booking->type === 'flight' && $booking->passenger_details)
                            <div class="detail-item"><label>Flight</label><span>{{ $booking->passenger_details['flight_number'] ?? '—' }} — {{ $booking->passenger_details['airline'] ?? '—' }}</span></div>
                            <div class="detail-item"><label>Class</label><span>{{ ucfirst(strtolower($booking->passenger_details['travel_class'] ?? 'economy')) }}</span></div>
                        @endif
                        @if($booking->type === 'hotels' && $booking->passenger_details)
                            <div class="detail-item"><label>Accommodation</label><span>{{ $booking->passenger_details['name'] ?? '—' }}</span></div>
                            <div class="detail-item"><label>Style</label><span>{{ ucfirst($booking->passenger_details['style'] ?? 'Standard') }}</span></div>
                        @endif
                        @if($booking->trip)
                            <div class="detail-item"><label>Trip</label><span>{{ $booking->trip->name }}</span></div>
                        @endif
                        <div class="detail-item"><label>Passengers</label><span>{{ $booking->seats_booked ?? 1 }}</span></div>
                        <div class="detail-item"><label>Total Paid</label><span data-price-usd="{{ $booking->total_price }}">${{ number_format($booking->total_price, 2) }}</span></div>
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
