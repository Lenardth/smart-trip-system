@extends('layouts.app')

@section('title', 'My Bookings — Smart Booking')
@section('page-title', 'My Bookings')
@section('page-description', 'Track and manage all your travel reservations')

@push('styles')
    @vite(['resources/css/blade/bookings/index.css'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endpush

@push('scripts_body')
    @vite(['resources/js/blade/bookings/index.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@section('content')

@php
    $allBookings     = $bookings ?? collect();
    $flightCount     = $allBookings->where('flight_id', '!=', null)->count();
    $activeCount     = $allBookings->whereIn('status', ['confirmed', 'pending'])->count();
    $totalSpent      = $allBookings->whereNotIn('status', ['cancelled'])->sum('total_price');
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
            <h3 id="statHotels">0</h3>
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

        @if($allBookings->count())

            @foreach($bookings as $booking)
                @php
                    $type          = $booking->flight_id ? 'flights' : ($booking->trip_id ? 'trips' : 'hotels');
                    $typeIcon      = $type === 'flights' ? 'fa-plane' : ($type === 'trips' ? 'fa-route' : 'fa-hotel');
                    $passengers    = $booking->seats_booked ?? 1;
                    $departureDate = $booking->flight?->departure_time;
                    $arrivalDate   = $booking->flight?->arrival_time;

                    if ($booking->flight) {
                        $from  = $booking->flight->departure_city;
                        $to    = $booking->flight->arrival_city;
                        $title = $from . ' (' . strtoupper(substr($from, 0, 3)) . ') → '
                               . $to  . ' (' . strtoupper(substr($to,   0, 3)) . ')';
                    } else {
                        $title = 'Booking #' . $booking->booking_reference;
                    }
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
                            <h3>{{ $title }}</h3>
                            <div class="booking-meta">
                                @if($departureDate)
                                    <span><i class="fas fa-calendar-alt"></i> {{ $departureDate->format('M j, Y') }}</span>
                                @endif
                                @if($arrivalDate)
                                    <span><i class="fas fa-calendar-check"></i> {{ $arrivalDate->format('M j, Y') }}</span>
                                @endif
                                <span>
                                    <i class="fas fa-users"></i>
                                    {{ $passengers }} {{ Str::plural('Passenger', $passengers) }}
                                </span>
                                @if($booking->flight?->airline)
                                    <span><i class="fas fa-plane-departure"></i> {{ $booking->flight->airline }}</span>
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
                                <i class="fas {{ $booking->status === 'confirmed' ? 'fa-check-circle' : ($booking->status === 'pending' ? 'fa-clock' : ($booking->status === 'completed' ? 'fa-flag-checkered' : 'fa-times-circle')) }}"></i>
                                {{ ucfirst($booking->status) }}
                            </span>
                            <div class="action-btns">
                                <button class="action-btn" onclick="toggleDetail('{{ $booking->id }}')">
                                    <i class="fas fa-chevron-down"></i> Details
                                </button>
                                @if(in_array($booking->status, ['confirmed', 'pending']))
                                    <button class="action-btn danger" onclick="cancelBooking('{{ $booking->id }}')">
                                        <i class="fas fa-times"></i>
                                    </button>
                                @elseif($booking->status === 'completed')
                                    <button class="action-btn primary" onclick="leaveReview('{{ $booking->id }}')">
                                        <i class="fas fa-star"></i> Review
                                    </button>
                                @elseif($booking->status === 'cancelled')
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
                            <div class="detail-item"><label>Passengers</label><span>{{ $passengers }}</span></div>
                            <div class="detail-item"><label>Total Paid</label><span>${{ number_format($booking->total_price, 2) }}</span></div>
                        </div>
                    </div>
                </div>
            @endforeach

        @else

            {{-- Demo cards shown when there are no real bookings --}}
            <div class="booking-card status-confirmed" data-type="flights" data-status="confirmed" data-price="850" data-date="2026-03-15">
                <div class="booking-inner">
                    <div class="booking-type-icon flight"><i class="fas fa-plane"></i></div>
                    <div class="booking-info">
                        <h3>Budapest (BUD) → London (LHR)</h3>
                        <div class="booking-meta">
                            <span><i class="fas fa-calendar-alt"></i> Apr 12, 2026</span>
                            <span><i class="fas fa-users"></i> 2 Passengers</span>
                            <span><i class="fas fa-plane-departure"></i> Wizz Air</span>
                        </div>
                        <span class="booking-ref">REF: BK-000123</span>
                    </div>
                    <div class="booking-price">
                        <div class="amount">$850.00</div>
                        <div class="per">per person</div>
                    </div>
                    <div class="booking-actions">
                        <span class="status-badge status-confirmed"><i class="fas fa-check-circle"></i> Confirmed</span>
                        <div class="action-btns">
                            <button class="action-btn" onclick="toggleDetailDemo('d1')"><i class="fas fa-chevron-down"></i> Details</button>
                            <button class="action-btn danger" onclick="cancelDemo()"><i class="fas fa-times"></i></button>
                        </div>
                    </div>
                </div>
                <div class="booking-detail-row" id="detail-d1">
                    <div class="detail-grid">
                        <div class="detail-item"><label>Booking Type</label><span>Round Trip Flight</span></div>
                        <div class="detail-item"><label>Booking Date</label><span>Mar 15, 2026 11:42</span></div>
                        <div class="detail-item"><label>Class</label><span>Economy</span></div>
                        <div class="detail-item"><label>Passengers</label><span>2 Adults</span></div>
                        <div class="detail-item"><label>Departure</label><span>Apr 12, 2026 — 08:30</span></div>
                        <div class="detail-item"><label>Return</label><span>Apr 19, 2026 — 17:15</span></div>
                        <div class="detail-item"><label>Total Paid</label><span>$1,700.00</span></div>
                        <div class="detail-item"><label>Payment Status</label><span>Paid</span></div>
                    </div>
                </div>
            </div>

            <div class="booking-card status-pending" data-type="hotels" data-status="pending" data-price="1200" data-date="2026-03-08">
                <div class="booking-inner">
                    <div class="booking-type-icon hotel"><i class="fas fa-hotel"></i></div>
                    <div class="booking-info">
                        <h3>The Grand Palace Hotel — Santorini</h3>
                        <div class="booking-meta">
                            <span><i class="fas fa-calendar-alt"></i> May 5, 2026</span>
                            <span><i class="fas fa-calendar-check"></i> Checkout: May 10, 2026</span>
                            <span><i class="fas fa-users"></i> 2 Guests</span>
                            <span><i class="fas fa-bed"></i> Deluxe Suite</span>
                        </div>
                        <span class="booking-ref">REF: BK-000098</span>
                    </div>
                    <div class="booking-price">
                        <div class="amount">$1,200.00</div>
                        <div class="per">5 nights</div>
                    </div>
                    <div class="booking-actions">
                        <span class="status-badge status-pending"><i class="fas fa-clock"></i> Pending</span>
                        <div class="action-btns">
                            <button class="action-btn" onclick="toggleDetailDemo('d2')"><i class="fas fa-chevron-down"></i> Details</button>
                            <button class="action-btn danger" onclick="cancelDemo()"><i class="fas fa-times"></i></button>
                        </div>
                    </div>
                </div>
                <div class="booking-detail-row" id="detail-d2">
                    <div class="detail-grid">
                        <div class="detail-item"><label>Booking Type</label><span>Hotel Reservation</span></div>
                        <div class="detail-item"><label>Booking Date</label><span>Mar 8, 2026 09:14</span></div>
                        <div class="detail-item"><label>Room Type</label><span>Deluxe Suite w/ Caldera View</span></div>
                        <div class="detail-item"><label>Guests</label><span>2 Adults</span></div>
                        <div class="detail-item"><label>Check-in</label><span>May 5, 2026 — 14:00</span></div>
                        <div class="detail-item"><label>Check-out</label><span>May 10, 2026 — 11:00</span></div>
                        <div class="detail-item"><label>Total Paid</label><span>$1,200.00</span></div>
                        <div class="detail-item"><label>Payment Status</label><span>Pending Verification</span></div>
                    </div>
                </div>
            </div>

            <div class="booking-card status-completed" data-type="trips" data-status="completed" data-price="2100" data-date="2026-01-20">
                <div class="booking-inner">
                    <div class="booking-type-icon trip"><i class="fas fa-route"></i></div>
                    <div class="booking-info">
                        <h3>7-Day Bali Cultural &amp; Wellness Trip</h3>
                        <div class="booking-meta">
                            <span><i class="fas fa-calendar-alt"></i> Feb 1, 2026</span>
                            <span><i class="fas fa-calendar-check"></i> Feb 7, 2026</span>
                            <span><i class="fas fa-users"></i> 1 Traveler</span>
                            <span><i class="fas fa-map-marker-alt"></i> Bali, Indonesia</span>
                        </div>
                        <span class="booking-ref">REF: BK-000071</span>
                    </div>
                    <div class="booking-price">
                        <div class="amount">$2,100.00</div>
                        <div class="per">package deal</div>
                    </div>
                    <div class="booking-actions">
                        <span class="status-badge status-completed"><i class="fas fa-flag-checkered"></i> Completed</span>
                        <div class="action-btns">
                            <button class="action-btn" onclick="toggleDetailDemo('d3')"><i class="fas fa-chevron-down"></i> Details</button>
                            <button class="action-btn primary" onclick="leaveReviewDemo()"><i class="fas fa-star"></i> Review</button>
                        </div>
                    </div>
                </div>
                <div class="booking-detail-row" id="detail-d3">
                    <div class="detail-grid">
                        <div class="detail-item"><label>Booking Type</label><span>Trip Package</span></div>
                        <div class="detail-item"><label>Booking Date</label><span>Jan 20, 2026 16:55</span></div>
                        <div class="detail-item"><label>Includes</label><span>Flights, Hotel, Activities</span></div>
                        <div class="detail-item"><label>Travelers</label><span>1 Adult</span></div>
                        <div class="detail-item"><label>Departure</label><span>Feb 1, 2026</span></div>
                        <div class="detail-item"><label>Return</label><span>Feb 7, 2026</span></div>
                        <div class="detail-item"><label>Total Paid</label><span>$2,100.00</span></div>
                        <div class="detail-item"><label>Payment Status</label><span>Paid &amp; Completed</span></div>
                    </div>
                </div>
            </div>

            <div class="booking-card status-cancelled" data-type="flights" data-status="cancelled" data-price="380" data-date="2026-02-01">
                <div class="booking-inner">
                    <div class="booking-type-icon flight"><i class="fas fa-plane"></i></div>
                    <div class="booking-info">
                        <h3>Dubai (DXB) → Paris (CDG)</h3>
                        <div class="booking-meta">
                            <span><i class="fas fa-calendar-alt"></i> Mar 3, 2026</span>
                            <span><i class="fas fa-users"></i> 1 Passenger</span>
                            <span><i class="fas fa-plane-departure"></i> Emirates</span>
                        </div>
                        <span class="booking-ref">REF: BK-000055</span>
                    </div>
                    <div class="booking-price">
                        <div class="amount">$380.00</div>
                        <div class="per">refunded</div>
                    </div>
                    <div class="booking-actions">
                        <span class="status-badge status-cancelled"><i class="fas fa-times-circle"></i> Cancelled</span>
                        <div class="action-btns">
                            <button class="action-btn" onclick="toggleDetailDemo('d4')"><i class="fas fa-chevron-down"></i> Details</button>
                            <button class="action-btn primary" onclick="rebookDemo()"><i class="fas fa-redo"></i> Rebook</button>
                        </div>
                    </div>
                </div>
                <div class="booking-detail-row" id="detail-d4">
                    <div class="detail-grid">
                        <div class="detail-item"><label>Booking Type</label><span>One-Way Flight</span></div>
                        <div class="detail-item"><label>Cancelled On</label><span>Feb 15, 2026</span></div>
                        <div class="detail-item"><label>Class</label><span>Business</span></div>
                        <div class="detail-item"><label>Passengers</label><span>1</span></div>
                        <div class="detail-item"><label>Airline</label><span>Emirates</span></div>
                        <div class="detail-item"><label>Refund Status</label><span>Refunded — $380.00</span></div>
                        <div class="detail-item"><label>Reason</label><span>Cancelled by traveler</span></div>
                        <div class="detail-item"><label>Refund Method</label><span>Original Payment</span></div>
                    </div>
                </div>
            </div>

        @endif

    </div>
</div>

@endsection
