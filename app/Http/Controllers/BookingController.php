<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function __construct(private readonly BookingService $bookings) {}

    public function index(): View
    {
        return view('bookings.index', $this->bookings->indexData(Auth::id()));
    }

    public function show(Booking $booking): View
    {
        abort_unless($booking->user_id === Auth::id(), 403);

        return view('bookings.show', ['booking' => $booking->load(['trip', 'user'])]);
    }

    public function cancel(Request $request, Booking $booking): JsonResponse|RedirectResponse
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

    private function cancelResponse(Request $request, bool $success, string $message, int $status = 200): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['success' => $success, 'message' => $message], $status);
        }

        return back()->with($success ? 'success' : 'error', $message);
    }
}
