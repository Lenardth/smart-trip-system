@extends('layouts.authenticated')

@section('title', 'Booking — ' . $booking->booking_reference)
@section('page-title', 'Booking Details')
@section('page-description', 'Ref: ' . $booking->booking_reference)

@section('content')

<div class="booking-show-wrap">

    {{-- Status banner --}}
    <div class="booking-status-banner status-{{ $booking->status }}">
        <i class="fas fa-{{ $booking->isConfirmed() ? 'check-circle' : ($booking->isPending() ? 'clock' : ($booking->isCompleted() ? 'flag-checkered' : 'times-circle')) }}"></i>
        <span>{{ ucfirst($booking->status) }}</span>
        <span class="bsb-ref">REF: {{ $booking->booking_reference }}</span>
    </div>

    <div class="booking-show-grid">

        {{-- Main details --}}
        <div class="booking-show-main">
            <div class="bshow-card">
                <h3><i class="fas fa-info-circle"></i> Booking Summary</h3>
                <div class="bshow-rows">
                    <div class="bshow-row"><label>Type</label><span>{{ ucfirst($booking->type) }}</span></div>
                    <div class="bshow-row"><label>Reference</label><span style="font-family:monospace;font-weight:700;">{{ $booking->booking_reference }}</span></div>
                    <div class="bshow-row"><label>Status</label><span class="status-badge status-{{ $booking->status }}">{{ ucfirst($booking->status) }}</span></div>
                    <div class="bshow-row"><label>Booked On</label><span>{{ $booking->created_at->format('D, M j, Y H:i') }}</span></div>
                    <div class="bshow-row"><label>Passengers</label><span>{{ $booking->seats_booked ?? 1 }}</span></div>
                    <div class="bshow-row"><label>Total Paid</label><span style="font-size:18px;font-weight:700;color:var(--deep);"><span data-price-usd="{{ $booking->total_price }}">${{ number_format($booking->total_price, 2) }}</span></span></div>
                </div>
            </div>

            @if($booking->flight)
            <div class="bshow-card">
                <h3><i class="fas fa-plane"></i> Flight Details</h3>
                <div class="bshow-flight-route">
                    <div class="bfr-point">
                        <div class="bfr-time">{{ $booking->flight->departure_time ? \Carbon\Carbon::parse($booking->flight->departure_time)->format('H:i') : '--:--' }}</div>
                        <div class="bfr-airport">{{ $booking->flight->departure_airport ?? $booking->flight->departure_city ?? '—' }}</div>
                        <div class="bfr-date">{{ $booking->flight->departure_time ? \Carbon\Carbon::parse($booking->flight->departure_time)->format('M j, Y') : '' }}</div>
                    </div>
                    <div class="bfr-line">
                        <i class="fas fa-plane"></i>
                        <span>{{ $booking->flight->duration ?? '' }}</span>
                    </div>
                    <div class="bfr-point">
                        <div class="bfr-time">{{ $booking->flight->arrival_time ? \Carbon\Carbon::parse($booking->flight->arrival_time)->format('H:i') : '--:--' }}</div>
                        <div class="bfr-airport">{{ $booking->flight->arrival_airport ?? $booking->flight->arrival_city ?? '—' }}</div>
                        <div class="bfr-date">{{ $booking->flight->arrival_time ? \Carbon\Carbon::parse($booking->flight->arrival_time)->format('M j, Y') : '' }}</div>
                    </div>
                </div>
                <div class="bshow-rows">
                    <div class="bshow-row"><label>Airline</label><span>{{ $booking->flight->airline ?? '—' }}</span></div>
                    <div class="bshow-row"><label>Flight No.</label><span>{{ $booking->flight->flight_number ?? '—' }}</span></div>
                    <div class="bshow-row"><label>Class</label><span>{{ ucfirst($booking->flight->class ?? 'Economy') }}</span></div>
                    @if($booking->flight->baggage)
                    <div class="bshow-row"><label>Baggage</label><span>{{ $booking->flight->baggage }}</span></div>
                    @endif
                </div>
            </div>
            @endif

            @if($booking->hotel)
            <div class="bshow-card">
                <h3><i class="fas fa-hotel"></i> Hotel Details</h3>
                <div class="bshow-rows">
                    <div class="bshow-row"><label>Hotel</label><span>{{ $booking->hotel->name }}</span></div>
                    <div class="bshow-row"><label>Room</label><span>{{ $booking->hotel->room_type ?? 'Standard' }}</span></div>
                    @if($booking->hotel->check_in)
                    <div class="bshow-row"><label>Check-in</label><span>{{ \Carbon\Carbon::parse($booking->hotel->check_in)->format('D, M j, Y') }}</span></div>
                    @endif
                    @if($booking->hotel->check_out)
                    <div class="bshow-row"><label>Check-out</label><span>{{ \Carbon\Carbon::parse($booking->hotel->check_out)->format('D, M j, Y') }}</span></div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="booking-show-side">
            <div class="bshow-card">
                <h3><i class="fas fa-receipt"></i> Price Breakdown</h3>
                <div class="bshow-price-row"><span>Base fare</span><span><span data-price-usd="{{ $booking->total_price * 0.88 }}">${{ number_format($booking->total_price * 0.88, 2) }}</span></span></div>
                <div class="bshow-price-row"><span>Taxes & fees</span><span><span data-price-usd="{{ $booking->total_price * 0.12 }}">${{ number_format($booking->total_price * 0.12, 2) }}</span></span></div>
                <div class="bshow-price-total"><span>Total</span><span><span data-price-usd="{{ $booking->total_price }}">${{ number_format($booking->total_price, 2) }}</span></span></div>
            </div>

            <div class="bshow-actions">
                <a href="{{ route('bookings.index') }}" class="secondary-button">
                    <i class="fas fa-arrow-left"></i> All Bookings
                </a>
                @if($booking->isActive())
                <form method="POST" action="{{ route('bookings.cancel', $booking) }}">
                    @csrf
                    <button type="submit" class="primary-button"
                            onclick="return confirm('Cancel this booking?')">
                        <i class="fas fa-times"></i> Cancel Booking
                    </button>
                </form>
                @endif
            </div>
        </div>

    </div>
</div>

@endsection

@push('styles')

@endpush
