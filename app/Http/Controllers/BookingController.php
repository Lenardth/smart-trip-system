<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function index()
    {
        $bookings = Booking::with(['flight', 'flight.user'])
            ->byUser(Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('bookings.index', compact('bookings'));
    }

    public function show(Booking $booking)
    {
        if (
            $booking->user_id !== Auth::id() &&
            $booking->flight->user_id !== Auth::id()
        ) {
            abort(403);
        }

        $booking->load(['flight', 'flight.user', 'user']);

        return view('bookings.show', compact('booking'));
    }

    public function cancel(Booking $booking)
    {
        if ($booking->user_id !== Auth::id()) {
            return back()->with('error', 'Unauthorized.');
        }

        if ($booking->status === 'cancelled') {
            return back()->with('error', 'Booking already cancelled.');
        }

        $booking->flight->increment('seats_available', $booking->passenger_count);

        $booking->update(['status' => 'cancelled']);

        return back()->with('success', 'Booking cancelled successfully. Seats have been returned to the flight.');
    }

    public function agencyBookings()
    {
        if (!Auth::user()->isAgency()) {
            return redirect()->route('dashboard');
        }

        $bookings = Booking::with(['user', 'flight'])
            ->whereHas('flight', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('bookings.agency-bookings', compact('bookings'));
    }
}
