@extends('layouts.authenticated')

@section('title', 'Agency Flight Management — Smart Booking')
@section('page-title', 'Flight Management')
@section('page-description', 'Create and manage your agency flights')

@section('content')
<div style="max-width:900px;margin:0 auto;">
    <div style="background:#fff;border-radius:12px;padding:32px;box-shadow:0 2px 8px rgba(0,0,0,.06);margin-bottom:24px;">
        <h2 style="margin:0 0 8px;display:flex;align-items:center;gap:10px;">
            <i class="fas fa-plane" style="color:var(--gold,#c9a96e);"></i>
            Your Flights
        </h2>
        <p style="margin:0 0 24px;color:#666;">List and manage all flights created by your agency.</p>

        <div style="display:flex;gap:12px;flex-wrap:wrap;">
            <a href="{{ route('flights.create') }}"
               style="display:inline-flex;align-items:center;gap:8px;padding:10px 20px;background:var(--gold,#c9a96e);color:#fff;border-radius:6px;text-decoration:none;font-weight:600;">
                <i class="fas fa-plus"></i> Add New Flight
            </a>
            <a href="{{ route('flights.index') }}"
               style="display:inline-flex;align-items:center;gap:8px;padding:10px 20px;background:#f3f4f6;color:#374151;border-radius:6px;text-decoration:none;font-weight:600;">
                <i class="fas fa-search"></i> Browse All Flights
            </a>
            <a href="{{ route('bookings.agency') }}"
               style="display:inline-flex;align-items:center;gap:8px;padding:10px 20px;background:#f3f4f6;color:#374151;border-radius:6px;text-decoration:none;font-weight:600;">
                <i class="fas fa-ticket-alt"></i> View Bookings
            </a>
        </div>
    </div>

    <div style="background:#fff;border-radius:12px;padding:32px;box-shadow:0 2px 8px rgba(0,0,0,.06);">
        <p style="color:#888;text-align:center;margin:0;">
            <i class="fas fa-info-circle"></i>
            Use <strong>Add New Flight</strong> to create listings, or visit <strong>View Bookings</strong> to manage reservations.
        </p>
    </div>
</div>
@endsection
