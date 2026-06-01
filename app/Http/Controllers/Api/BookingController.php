<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreAccommodationBookingRequest;
use App\Http\Requests\Api\StoreFlightBookingRequest;
use App\Http\Requests\Api\UpdateAccommodationBookingRequest;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function __construct(private readonly BookingService $bookings) {}

    public function storeAccommodation(StoreAccommodationBookingRequest $request): JsonResponse
    {
        return response()->json(
            $this->bookings->accommodation($request->validated(), Auth::user()),
            201
        );
    }

    public function bookFlight(StoreFlightBookingRequest $request): JsonResponse
    {
        return response()->json(
            $this->bookings->flight($request->validated(), Auth::user()),
            201
        );
    }

    public function updateAccommodation(UpdateAccommodationBookingRequest $request, Booking $booking): JsonResponse
    {
        abort_unless($booking->user_id === Auth::id(), 403);

        return response()->json(
            $this->bookings->updateAccommodation($booking, $request->validated(), Auth::user())
        );
    }
}
