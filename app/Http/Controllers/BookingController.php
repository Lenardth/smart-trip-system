<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function index()
    {
        $bookings = Booking::with(['flight', 'hotel', 'trip'])
            ->byUser(Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $all = Booking::byUser(Auth::id())->get();

        $flightCount = $all->filter(fn($b) =>
            $b->flight_id !== null ||
            ($b->passenger_details && ($b->passenger_details['type'] ?? '') !== 'accommodation')
        )->count();

        $hotelCount = $all->filter(fn($b) =>
            $b->hotel_id !== null ||
            ($b->passenger_details && ($b->passenger_details['type'] ?? '') === 'accommodation')
        )->count();

        $activeCount = $all->whereIn('status', ['confirmed', 'pending'])->count();
        $totalSpent  = $all->whereNotIn('status', ['cancelled'])->sum('total_price');

        return view('bookings.index', compact('bookings', 'flightCount', 'hotelCount', 'activeCount', 'totalSpent'));
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

    public function create(Request $request)
    {
        $accommodationId = $request->query('accommodation_id');
        $accommodation   = $accommodationId
            ? \App\Models\Accommodation::find($accommodationId)
            : null;

        return view('bookings.create', compact('accommodation'));
    }

    public function storeAccommodation(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validate([
            'accommodation_id' => 'required|exists:accommodations,id',
            'check_in'         => 'required|date|after_or_equal:today',
            'check_out'        => 'required|date|after:check_in',
            'guests'           => 'nullable|integer|min:1|max:20',
        ]);

        $accommodation = \App\Models\Accommodation::findOrFail($data['accommodation_id']);
        $nights        = (int) \Carbon\Carbon::parse($data['check_in'])->diffInDays($data['check_out']);
        $guests        = (int) ($data['guests'] ?? 1);
        $total         = ($accommodation->nightly_rate ?? 0) * $nights * $guests;

        $booking = Booking::create([
            'user_id'           => Auth::id(),
            'total_price'       => $total,
            'seats_booked'      => $guests,
            'status'            => 'confirmed',
            'passenger_details' => [
                'type'             => 'accommodation',
                'accommodation_id' => $accommodation->id,
                'name'             => $accommodation->name,
                'city'             => $accommodation->city,
                'country'          => $accommodation->country,
                'style'            => $accommodation->style,
                'check_in'         => $data['check_in'],
                'check_out'        => $data['check_out'],
                'nights'           => $nights,
                'guests'           => $guests,
                'nightly_rate'     => $accommodation->nightly_rate,
            ],
        ]);

        return response()->json([
            'success'           => true,
            'booking_reference' => $booking->booking_reference,
            'total_price'       => $total,
            'message'           => 'Accommodation booked successfully!',
        ], 201);
    }

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
