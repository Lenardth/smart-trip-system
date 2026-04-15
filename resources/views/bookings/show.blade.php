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
                <h3><i class="fas fa-info-circle" style="color:var(--gold);margin-right:8px;"></i> Booking Summary</h3>
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
                <h3><i class="fas fa-plane" style="color:var(--gold);margin-right:8px;"></i> Flight Details</h3>
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
                <div class="bshow-rows" style="margin-top:16px;">
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
                <h3><i class="fas fa-hotel" style="color:var(--gold);margin-right:8px;"></i> Hotel Details</h3>
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
                <h3><i class="fas fa-receipt" style="color:var(--gold);margin-right:8px;"></i> Price Breakdown</h3>
                <div class="bshow-price-row"><span>Base fare</span><span><span data-price-usd="{{ $booking->total_price * 0.88 }}">${{ number_format($booking->total_price * 0.88, 2) }}</span></span></div>
                <div class="bshow-price-row"><span>Taxes & fees</span><span><span data-price-usd="{{ $booking->total_price * 0.12 }}">${{ number_format($booking->total_price * 0.12, 2) }}</span></span></div>
                <div class="bshow-price-total"><span>Total</span><span><span data-price-usd="{{ $booking->total_price }}">${{ number_format($booking->total_price, 2) }}</span></span></div>
            </div>

            <div class="bshow-actions">
                <a href="{{ route('bookings.index') }}" class="secondary-button" style="text-decoration:none;display:flex;align-items:center;gap:8px;justify-content:center;">
                    <i class="fas fa-arrow-left"></i> All Bookings
                </a>
                @if($booking->isActive())
                <form method="POST" action="{{ route('bookings.cancel', $booking) }}">
                    @csrf
                    <button type="submit" class="primary-button" style="width:100%;background:var(--danger);justify-content:center;"
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
<style>
.booking-show-wrap { max-width: 900px; }

.booking-status-banner {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 20px;
    border-radius: 8px;
    font-weight: 700;
    font-size: 15px;
    margin-bottom: 24px;
}
.booking-status-banner.status-confirmed { background: var(--success-bg); color: #2e7d32; }
.booking-status-banner.status-pending   { background: var(--warning-bg); color: #e65100; }
.booking-status-banner.status-cancelled { background: #fdf0f0; color: var(--danger); }
.booking-status-banner.status-completed { background: rgba(201,169,110,.12); color: var(--deep); }
.bsb-ref { margin-left: auto; font-family: monospace; font-size: 13px; opacity: .7; }

.booking-show-grid { display: grid; grid-template-columns: 1fr 300px; gap: 20px; }
@media (max-width: 768px) { .booking-show-grid { grid-template-columns: 1fr; } }

.bshow-card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 22px;
    margin-bottom: 16px;
}
.bshow-card h3 { font-size: 16px; font-weight: 700; color: var(--deep); margin: 0 0 16px; }

.bshow-rows { display: flex; flex-direction: column; gap: 10px; }
.bshow-row { display: flex; justify-content: space-between; align-items: center; font-size: 14px; padding-bottom: 10px; border-bottom: 1px solid var(--border); }
.bshow-row:last-child { border-bottom: none; padding-bottom: 0; }
.bshow-row label { color: var(--text-muted); font-weight: 600; }
.bshow-row span { color: var(--deep); }

.bshow-flight-route { display: flex; align-items: center; gap: 16px; padding: 16px; background: var(--cream); border-radius: 8px; }
.bfr-point { text-align: center; flex: 1; }
.bfr-time { font-size: 24px; font-weight: 700; color: var(--deep); }
.bfr-airport { font-size: 13px; color: var(--text-muted); margin: 4px 0; }
.bfr-date { font-size: 12px; color: var(--text-sub); }
.bfr-line { display: flex; flex-direction: column; align-items: center; gap: 4px; color: var(--gold); font-size: 20px; flex-shrink: 0; }
.bfr-line span { font-size: 11px; color: var(--text-muted); }

.bshow-price-row { display: flex; justify-content: space-between; font-size: 14px; color: var(--text-muted); padding: 8px 0; border-bottom: 1px solid var(--border); }
.bshow-price-total { display: flex; justify-content: space-between; font-size: 17px; font-weight: 700; color: var(--deep); padding-top: 12px; margin-top: 4px; }

.bshow-actions { display: flex; flex-direction: column; gap: 10px; }
.bshow-actions form { margin: 0; }
</style>
@endpush
