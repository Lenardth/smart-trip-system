<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Contracts\PricingServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function __construct(private PricingServiceInterface $pricing) {}

    public function index()
    {
        $perPage = config('booking.pagination.index_per_page', 10);
        $bookings = Booking::with('trip')
            ->byUser(Auth::id())
            ->orderByDesc('created_at')
            ->paginate($perPage);

        $all = Booking::byUser(Auth::id())->get();

        $flightCount = $all->where('type', config('booking.types.flight'))->count();
        $hotelCount  = $all->where('type', config('booking.types.accommodation'))->count();
        $activeCount = $all->whereIn('status', [config('booking.statuses.confirmed'), config('booking.statuses.pending')])->count();
        $totalSpent  = $all->whereNotIn('status', [config('booking.statuses.cancelled')])->sum('total_price');

        return view('bookings.index', compact('bookings', 'flightCount', 'hotelCount', 'activeCount', 'totalSpent'));
    }

    public function show(Booking $booking)
    {
        if ($booking->user_id !== Auth::id()) {
            abort(403);
        }

        $booking->load(['trip', 'user']);

        return view('bookings.show', compact('booking'));
    }

    public function cancel(Request $request, Booking $booking): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        if ($booking->user_id !== Auth::id()) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
            }

            return back()->with('error', 'Unauthorized.');
        }

        if ($booking->status === config('booking.statuses.cancelled')) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Booking already cancelled.'], 422);
            }

            return back()->with('error', 'Booking already cancelled.');
        }

        DB::transaction(fn () => $booking->update(['status' => config('booking.statuses.cancelled')]));

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Booking cancelled successfully.',
            ]);
        }

        return back()->with('success', 'Booking cancelled successfully.');
    }

    public function storeAccommodation(Request $request): JsonResponse
    {
        $data = $request->validate([
            'accommodation_id' => 'required|exists:accommodations,id',
            'check_in'         => 'required|date|after_or_equal:today',
            'check_out'        => 'required|date|after:check_in',
            'guests'           => 'nullable|integer|min:1|max:20',
            'coupon_code'      => 'nullable|string|max:32',
        ]);

        return DB::transaction(function () use ($data) {
            $accommodation = \App\Models\Accommodation::findOrFail($data['accommodation_id']);
            $nights        = (int) \Carbon\Carbon::parse($data['check_in'])->diffInDays($data['check_out']);
            $guests        = (int) ($data['guests'] ?? config('booking.default_guests', 1));
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
                'status'            => config('booking.statuses.confirmed'),
                'passenger_details' => [
                    'type'             => config('booking.passenger_detail_types.accommodation'),
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
        });
    }

    public function bookFlight(Request $request): JsonResponse
    {
        $data = $request->validate([
            'airline'           => 'required|string|max:100',
            'flight_number'     => 'required|string|max:20',
            'departure_airport' => 'nullable|string|max:100',
            'arrival_airport'   => 'nullable|string|max:100',
            'departure_time'    => 'nullable|string',
            'arrival_time'      => 'nullable|string',
            'departure_date'    => 'required|string',
            'duration'          => 'nullable|string',
            'price'             => 'required|numeric|min:0',
            'adults'            => 'nullable|integer|min:1|max:9',
            'travel_class'      => 'nullable|string',
            'coupon_code'       => 'nullable|string|max:32',
        ]);

        return DB::transaction(function () use ($data) {
            $adults    = (int) ($data['adults'] ?? config('booking.default_adults', 1));
            $priceEach = max(0, (float) $data['price']);
            $subtotal  = $priceEach * $adults;

            $pricing = $this->pricing->calculate($subtotal, Auth::user(), $data['coupon_code'] ?? null);

            $booking = Booking::create([
                'user_id'           => Auth::id(),
                'subtotal'          => $pricing['subtotal'],
                'discount_amount'   => $pricing['discount'],
                'service_fee'       => $pricing['service_fee'],
                'total_price'       => $pricing['total'],
                'coupon_code'       => $pricing['coupon']?->code,
                'seats_booked'      => $adults,
                'status'            => config('booking.statuses.confirmed'),
                'passenger_details' => [
                    'airline'           => $data['airline'],
                    'flight_number'     => $data['flight_number'],
                    'departure_airport' => $data['departure_airport'],
                    'arrival_airport'   => $data['arrival_airport'],
                    'departure_time'    => $data['departure_time'] ?? null,
                    'arrival_time'      => $data['arrival_time'] ?? null,
                    'departure_date'    => $data['departure_date'],
                    'duration'          => $data['duration'] ?? null,
                    'travel_class'      => $data['travel_class'] ?? config('booking.default_travel_class'),
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
        });
    }
}
