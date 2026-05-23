@extends('layouts.authenticated')

@section('title', 'Booking — ' . $booking->booking_reference)
@section('page-title', 'Booking Details')
@section('page-description', 'Ref: ' . $booking->booking_reference)

@section('content')

@php
    $pd = $booking->passenger_details ? (array) $booking->passenger_details : [];
@endphp

<div class="booking-show-wrap">

    <div class="booking-status-banner status-{{ $booking->status }}">
        <i class="fas fa-{{ $booking->status === 'confirmed' ? 'check-circle' : ($booking->status === 'pending' ? 'clock' : ($booking->status === 'completed' ? 'flag-checkered' : 'times-circle')) }}"></i>
        <span>{{ ucfirst($booking->status) }}</span>
        <span class="bsb-ref">REF: {{ $booking->booking_reference }}</span>
    </div>

    <div class="booking-show-grid">

        <div class="booking-show-main">
            <div class="bshow-card">
                <h3><i class="fas fa-info-circle"></i> Booking Summary</h3>
                <div class="bshow-rows">
                    <div class="bshow-row"><label>Type</label><span>{{ ucfirst($booking->type) }}</span></div>
                    <div class="bshow-row"><label>Reference</label><span class="booking-ref-mono">{{ $booking->booking_reference }}</span></div>
                    <div class="bshow-row"><label>Status</label><span class="status-badge status-{{ $booking->status }}">{{ ucfirst($booking->status) }}</span></div>
                    <div class="bshow-row"><label>Booked On</label><span>{{ $booking->created_at->format('D, M j, Y H:i') }}</span></div>
                    <div class="bshow-row"><label>Passengers</label><span>{{ $booking->seats_booked ?? 1 }}</span></div>
                    <div class="bshow-row"><label>Total Paid</label><span class="booking-total" data-price-usd="{{ $booking->total_price }}">${{ number_format($booking->total_price, 2) }}</span></div>
                </div>
            </div>

            @if($booking->type === 'flight' && $pd)
            <div class="bshow-card">
                <h3><i class="fas fa-plane"></i> Flight Details</h3>
                <div class="bshow-flight-route">
                    <div class="bfr-point">
                        <div class="bfr-time">{{ !empty($pd['departure_time']) ? \Carbon\Carbon::parse($pd['departure_time'])->format('H:i') : '--:--' }}</div>
                        <div class="bfr-airport">{{ $pd['departure_airport'] ?? '—' }}</div>
                        <div class="bfr-date">{{ !empty($pd['departure_date']) ? \Carbon\Carbon::parse($pd['departure_date'])->format('M j, Y') : '' }}</div>
                    </div>
                    <div class="bfr-line">
                        <i class="fas fa-plane"></i>
                        <span>{{ $pd['duration'] ?? '' }}</span>
                    </div>
                    <div class="bfr-point">
                        <div class="bfr-time">{{ !empty($pd['arrival_time']) ? \Carbon\Carbon::parse($pd['arrival_time'])->format('H:i') : '--:--' }}</div>
                        <div class="bfr-airport">{{ $pd['arrival_airport'] ?? '—' }}</div>
                    </div>
                </div>
                <div class="bshow-rows">
                    <div class="bshow-row"><label>Airline</label><span>{{ $pd['airline'] ?? '—' }}</span></div>
                    <div class="bshow-row"><label>Flight No.</label><span>{{ $pd['flight_number'] ?? '—' }}</span></div>
                    <div class="bshow-row"><label>Class</label><span>{{ ucfirst(strtolower($pd['travel_class'] ?? 'economy')) }}</span></div>
                </div>
            </div>
            @endif

            @if($booking->type === 'hotels' && $pd)
            <div class="bshow-card">
                <h3><i class="fas fa-hotel"></i> Stay Details</h3>
                <div class="bshow-rows">
                    <div class="bshow-row"><label>Property</label><span>{{ $pd['name'] ?? '—' }}</span></div>
                    <div class="bshow-row"><label>Style</label><span>{{ ucfirst($pd['style'] ?? 'standard') }}</span></div>
                    @if(!empty($pd['check_in']))
                    <div class="bshow-row"><label>Check-in</label><span>{{ \Carbon\Carbon::parse($pd['check_in'])->format('D, M j, Y') }}</span></div>
                    @endif
                    @if(!empty($pd['check_out']))
                    <div class="bshow-row"><label>Check-out</label><span>{{ \Carbon\Carbon::parse($pd['check_out'])->format('D, M j, Y') }}</span></div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <div class="booking-show-side">
            <div class="bshow-card">
                <h3><i class="fas fa-receipt"></i> Price Breakdown</h3>
                <div class="bshow-price-row"><span>Subtotal</span><span data-price-usd="{{ $booking->subtotal }}">${{ number_format($booking->subtotal ?? 0, 2) }}</span></div>
                @if($booking->discount_amount > 0)
                <div class="bshow-price-row"><span>Discount</span><span>-<span data-price-usd="{{ $booking->discount_amount }}">${{ number_format($booking->discount_amount, 2) }}</span></span></div>
                @endif
                <div class="bshow-price-row"><span>Service fee</span><span data-price-usd="{{ $booking->service_fee }}">${{ number_format($booking->service_fee ?? 0, 2) }}</span></div>
                <div class="bshow-price-total"><span>Total</span><span data-price-usd="{{ $booking->total_price }}">${{ number_format($booking->total_price, 2) }}</span></div>
            </div>

            <div class="bshow-actions">
                <a href="{{ route('bookings.index') }}" class="secondary-button">
                    <i class="fas fa-arrow-left"></i> All Bookings
                </a>
                @if(in_array($booking->status, ['confirmed', 'pending']))
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
