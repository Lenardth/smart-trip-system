@extends('layouts.authenticated')

@section('content')
<div class="filter-bar">
    <h2>Incoming Bookings</h2>
</div>

<div class="bookings-section">
    <div class="bookings-grid">
        @forelse($bookings as $booking)
            <div class="booking-card status-{{ $booking->status }}">
                <div class="booking-inner">
                    <div class="booking-type-icon flight"><i class="fas fa-ticket-alt"></i></div>
                    <div class="booking-info">
                        <h3>{{ $booking->title }}</h3>
                        <div class="booking-meta">
                            <span>{{ $booking->passenger_details['flight_number'] ?? '—' }}</span>
                            <span>{{ $booking->user?->name ?? 'Traveler' }}</span>
                            <span>{{ $booking->seats_booked }} {{ Str::plural('seat', $booking->seats_booked) }}</span>
                            <span>{{ $booking->created_at->format('M j, Y H:i') }}</span>
                        </div>
                        <span class="booking-ref">REF: {{ $booking->booking_reference }}</span>
                    </div>
                    <div class="booking-price"><div class="amount" data-price-usd="{{ $booking->total_price }}">${{ number_format((float) $booking->total_price, 2) }}</div><div class="per">total</div></div>
                    <div class="booking-actions">
                        <span class="status-badge status-{{ $booking->status }}">{{ ucfirst($booking->status) }}</span>
                    </div>
                </div>
            </div>
        @empty
            <div class="empty-state"><i class="fas fa-inbox"></i><h3>No Incoming Bookings</h3><p>Bookings for your published flights will appear here.</p></div>
        @endforelse
    </div>
    {{ $bookings->links() }}
</div>
@endsection
