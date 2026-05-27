<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    public function __construct(private readonly BookingService $bookings) {}

    public function index()
    {
        return view('bookings.index', $this->bookings->indexData(Auth::id()));
    }

    public function show(Booking $booking)
    {
        abort_unless($booking->user_id === Auth::id(), 403);

        return view('bookings.show', ['booking' => $booking->load(['trip', 'user'])]);
    }

    public function cancel(Request $request, Booking $booking): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        if ($booking->user_id !== Auth::id()) {
            return $this->cancelResponse($request, false, 'Unauthorized.', 403);
        }

        if ($booking->status === config('booking.statuses.cancelled')) {
            return $this->cancelResponse($request, false, 'Booking already cancelled.', 422);
        }

        $this->bookings->cancel($booking);

        return $this->cancelResponse($request, true, 'Booking cancelled successfully.');
    }

    public function storeAccommodation(Request $request): JsonResponse
    {
        return response()->json(
            $this->bookings->accommodation($request->validate($this->accommodationRules()), Auth::user()),
            201
        );
    }

    public function bookFlight(Request $request): JsonResponse
    {
        return response()->json(
            $this->bookings->flight($request->validate($this->flightRules()), Auth::user()),
            201
        );
    }

    private function cancelResponse(Request $request, bool $success, string $message, int $status = 200)
    {
        if ($request->expectsJson()) {
            return response()->json(['success' => $success, 'message' => $message], $status);
        }

        return back()->with($success ? 'success' : 'error', $message);
    }

    private function accommodationRules(): array
    {
        return [
            'accommodation_id' => 'required|exists:accommodations,id',
            'check_in'         => 'required|date|after_or_equal:today',
            'check_out'        => 'required|date|after:check_in',
            'guests'           => 'nullable|integer|min:1|max:20',
            'coupon_code'      => 'nullable|string|max:32',
        ];
    }

    private function flightRules(): array
    {
        return [
            'airline'           => 'required|string|max:100',
            'flight_number'     => 'required|string|max:20',
            'departure_airport' => 'nullable|string|max:100',
            'arrival_airport'   => 'nullable|string|max:100',
            'departure_time'    => 'nullable|string|max:30',
            'arrival_time'      => 'nullable|string|max:30',
            'departure_date'    => 'required|date_format:Y-m-d|after_or_equal:today',
            'duration'          => 'nullable|string',
            'price'             => 'required|numeric|min:0',
            'adults'            => 'nullable|integer|min:1|max:9',
            'travel_class'      => ['nullable', 'string', Rule::in(config('booking.travel_classes'))],
            'coupon_code'       => 'nullable|string|max:32',
        ];
    }
}
