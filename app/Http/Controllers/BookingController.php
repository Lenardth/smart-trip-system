<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\PricingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function __construct(private PricingService $pricing) {}
    public function index()
    {
        $bookings = Booking::with(['flight', 'trip'])
            ->byUser(Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $all = Booking::byUser(Auth::id())->get();

        $flightCount = $all->filter(fn($b) =>
            $b->flight_id !== null ||
            ($b->passenger_details && ($b->passenger_details['type'] ?? '') !== 'accommodation')
        )->count();

        $hotelCount = $all->filter(fn($b) =>
            ($b->passenger_details && ($b->passenger_details['type'] ?? '') === 'accommodation')
        )->count();

        $activeCount = $all->whereIn('status', ['confirmed', 'pending'])->count();
        $totalSpent  = $all->whereNotIn('status', ['cancelled'])->sum('total_price');

        return view('bookings.index', compact('bookings', 'flightCount', 'hotelCount', 'activeCount', 'totalSpent'));
    }

    public function show(Booking $booking)
    {
        if ($booking->user_id !== Auth::id()) {
            abort(403);
        }

        $booking->load(['flight', 'trip', 'user']);

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
            'coupon_code'      => 'nullable|string|max:32',
        ]);

        $accommodation = \App\Models\Accommodation::findOrFail($data['accommodation_id']);
        $nights        = (int) \Carbon\Carbon::parse($data['check_in'])->diffInDays($data['check_out']);
        $guests        = (int) ($data['guests'] ?? 1);
        $subtotal      = ($accommodation->nightly_rate ?? 0) * $nights * $guests;

        $pricing = $this->pricing->calculate($subtotal, Auth::user(), $data['coupon_code'] ?? null);

        $booking = Booking::create([
            'user_id'           => Auth::id(),
            'subtotal'          => $pricing['subtotal'],
            'discount_amount'   => $pricing['discount'],
            'service_fee'       => $pricing['service_fee'],
            'total_price'       => $pricing['total'],
            'coupon_code'       => $pricing['coupon']?->code,
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

        $this->pricing->recordRevenue($booking, $pricing);

        return response()->json([
            'success'           => true,
            'booking_reference' => $booking->booking_reference,
            'subtotal'          => $pricing['subtotal'],
            'discount'          => $pricing['discount'],
            'service_fee'       => $pricing['service_fee'],
            'total_price'       => $pricing['total'],
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
            'coupon_code'      => 'nullable|string|max:32',
        ]);

        $adults     = (int) ($data['adults'] ?? 1);
        $priceEach  = (float) ($data['price'] ?? 0);
        $subtotal   = $priceEach * $adults;

        $pricing = $this->pricing->calculate($subtotal, Auth::user(), $data['coupon_code'] ?? null);

        $booking = Booking::create([
            'user_id'           => Auth::id(),
            'flight_id'         => null,
            'subtotal'          => $pricing['subtotal'],
            'discount_amount'   => $pricing['discount'],
            'service_fee'       => $pricing['service_fee'],
            'total_price'       => $pricing['total'],
            'coupon_code'       => $pricing['coupon']?->code,
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

        $this->pricing->recordRevenue($booking, $pricing);

        return response()->json([
            'success'           => true,
            'booking_reference' => $booking->booking_reference,
            'subtotal'          => $pricing['subtotal'],
            'discount'          => $pricing['discount'],
            'service_fee'       => $pricing['service_fee'],
            'total_price'       => $pricing['total'],
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