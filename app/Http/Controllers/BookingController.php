<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    /**
     * Display user's bookings
     */
    public function index()
    {
        $bookings = Booking::with(['flight', 'flight.agency'])
            ->byUser(Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('bookings.index', compact('bookings'));
    }

    /**
     * Show booking details
     */
    public function show(Booking $booking)
    {
        // Check authorization
        if ($booking->user_id !== Auth::id() && 
            $booking->flight->agency_id !== Auth::id()) {
            abort(403);
        }

        $booking->load(['flight', 'flight.agency', 'user']);

        return view('bookings.show', compact('booking'));
    }

    /**
     * Cancel booking
     */
    public function cancel(Booking $booking)
    {
        if ($booking->user_id !== Auth::id()) {
            return back()->with('error', 'Unauthorized.');
        }

        if ($booking->status === 'cancelled') {
            return back()->with('error', 'Booking already cancelled.');
        }

        // Return seats to flight
        $booking->flight->increment('available_seats', $booking->seats_booked);

        // Update booking status
        $booking->update(['status' => 'cancelled']);

        return back()->with('success', 'Booking cancelled successfully. Seats have been returned to the flight.');
    }

    /**
     * Show agency's received bookings
     */
    public function agencyBookings()
    {
        if (!Auth::user()->isAgency()) {
            return redirect()->route('dashboard');
        }

        $bookings = Booking::with(['user', 'flight'])
            ->whereHas('flight', function($query) {
                $query->where('agency_id', Auth::id());
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('bookings.agency-bookings', compact('bookings'));
    }
}
