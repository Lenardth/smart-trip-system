<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Flight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function index()
    {
        $bookings = Booking::where('user_id', Auth::id())
            ->with('flight')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('bookings.index', compact('bookings'));
    }

    public function create(Flight $flight)
    {
        // Check if flight is available
        if (!$flight->isAvailable()) {
            return redirect()->back()
                ->with('error', 'This flight is no longer available.');
        }

        return view('bookings.create', compact('flight'));
    }

    public function store(Request $request, Flight $flight)
    {
        // Check if flight is available
        if (!$flight->isAvailable()) {
            return redirect()->back()
                ->with('error', 'This flight is no longer available.');
        }

        $request->validate([
            'passenger_count' => 'required|integer|min:1|max:' . $flight->seats_available,
            'passenger_details' => 'required|array',
            'passenger_details.*.name' => 'required|string',
            'passenger_details.*.passport' => 'required|string',
            'passenger_details.*.dob' => 'required|date',
            'special_requests' => 'nullable|string',
        ]);

        // Calculate total price
        $totalPrice = $flight->price * $request->passenger_count;

        // Create booking
        $booking = Booking::create([
            'user_id' => Auth::id(),
            'flight_id' => $flight->id,
            'passenger_count' => $request->passenger_count,
            'total_price' => $totalPrice,
            'booking_reference' => Booking::generateReference(),
            'passenger_details' => $request->passenger_details,
            'special_requests' => $request->special_requests,
        ]);

        // Update flight seats
        $flight->decrement('seats_available', $request->passenger_count);

        return redirect()->route('bookings.show', $booking)
            ->with('success', 'Booking confirmed! Reference: ' . $booking->booking_reference);
    }

    public function show(Booking $booking)
    {
        // Check if user owns this booking
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        return view('bookings.show', compact('booking'));
    }

    public function cancel(Booking $booking)
    {
        // Check if user owns this booking
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Check if booking can be cancelled
        if ($booking->isConfirmed()) {
            return redirect()->back()
                ->with('error', 'Confirmed bookings cannot be cancelled.');
        }

        // Restore flight seats
        $booking->flight->increment('seats_available', $booking->passenger_count);

        // Update booking status
        $booking->update(['status' => 'cancelled']);

        return redirect()->route('bookings.index')
            ->with('success', 'Booking cancelled successfully.');
    }
}
