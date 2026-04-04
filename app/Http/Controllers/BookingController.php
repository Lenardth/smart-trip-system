<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function index()
    {
        $bookings = Booking::with(['flight', 'flight.user', 'hotel', 'trip'])
            ->byUser(Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('bookings.index', compact('bookings'));
    }

    public function show(Booking $booking)
    {
        if ($booking->user_id !== Auth::id()) {
            if (!$booking->flight || $booking->flight->user_id !== Auth::id()) {
                abort(403);
            }
        }

        $booking->load(['flight', 'hotel', 'trip', 'user']);

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

        if ($booking->flight && $booking->seats_booked) {
            $booking->flight->increment('seats_available', $booking->seats_booked);
        }

        $booking->update(['status' => 'cancelled']);

        return back()->with('success', 'Booking cancelled successfully.');
    }

    /**
     * Book a flight from search results (no DB flight record needed).
     */
    public function bookFlight(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validate([
            'airline'          => 'required|string|max:100',
            'flight_number'    => 'required|string|max:20',
            'departure_airport'=> 'required|string|max:100',
            'arrival_airport'  => 'required|string|max:100',
            'departure_time'   => 'nullable|string',
            'arrival_time'     => 'nullable|string',
            'departure_date'   => 'required|string',
            'duration'         => 'nullable|string',
            'price'            => 'nullable|numeric|min:0',
            'adults'           => 'nullable|integer|min:1|max:9',
            'travel_class'     => 'nullable|string',
        ]);

        $adults     = (int) ($data['adults'] ?? 1);
        $priceEach  = (float) ($data['price'] ?? 0);
        $totalPrice = $priceEach * $adults;

        $booking = Booking::create([
            'user_id'           => \Illuminate\Support\Facades\Auth::id(),
            'flight_id'         => null,
            'total_price'       => $totalPrice,
            'seats_booked'      => $adults,
            'status'            => 'confirmed',
            'passenger_details' => [
                'airline'           => $data['airline'],
                'flight_number'     => $data['flight_number'],
                'departure_airport' => $data['departure_airport'],
                'arrival_airport'   => $data['arrival_airport'],
                'departure_time'    => $data['departure_time'] ?? null,
                'arrival_time'      => $data['arrival_time']   ?? null,
                'departure_date'    => $data['departure_date'],
                'duration'          => $data['duration']       ?? null,
                'travel_class'      => $data['travel_class']   ?? 'ECONOMY',
                'adults'            => $adults,
                'price_per_person'  => $priceEach,
            ],
        ]);

        return response()->json([
            'success'           => true,
            'booking_reference' => $booking->booking_reference,
            'total_price'       => $totalPrice,
            'message'           => 'Flight booked successfully!',
        ], 201);
    }

    public function agencyBookings()
    {        if (! Auth::user()->isAgency()) {
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
