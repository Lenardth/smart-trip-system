<?php

namespace App\Services;

use App\Contracts\PricingServiceInterface;
use App\Models\Accommodation;
use App\Models\Booking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BookingService
{
    public function __construct(private readonly PricingServiceInterface $pricing) {}

    public function indexData(int $userId): array
    {
        $perPage = config('booking.pagination.index_per_page', 10);
        $bookings = Booking::with('trip')
            ->byUser($userId)
            ->orderByDesc('created_at')
            ->paginate($perPage);

        $all = Booking::byUser($userId)->get();

        return [
            'bookings' => $bookings,
            'flightCount' => $all->where('type', config('booking.types.flight'))->count(),
            'hotelCount' => $all->where('type', config('booking.types.accommodation'))->count(),
            'activeCount' => $all->whereIn('status', [config('booking.statuses.confirmed'), config('booking.statuses.pending')])->count(),
            'totalSpent' => $all->whereNotIn('status', [config('booking.statuses.cancelled')])->sum('total_price'),
        ];
    }

    public function cancel(Booking $booking): void
    {
        DB::transaction(fn () => $booking->update(['status' => config('booking.statuses.cancelled')]));
    }

    public function accommodation(array $data, User $user): array
    {
        return DB::transaction(function () use ($data, $user) {
            $accommodation = Accommodation::findOrFail($data['accommodation_id']);
            $nights        = (int) Carbon::parse($data['check_in'])->diffInDays($data['check_out']);
            $guests        = (int) ($data['guests'] ?? config('booking.default_guests', 1));
            $subtotal      = ($accommodation->nightly_rate ?? 0) * $nights * $guests;
            $pricing       = $this->pricing->calculate($subtotal, $user, $data['coupon_code'] ?? null);

            $booking = Booking::create([
                'user_id'           => $user->id,
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

            return $this->bookingResponse($booking, $pricing, 'Accommodation booked successfully!');
        });
    }

    public function flight(array $data, User $user): array
    {
        return DB::transaction(function () use ($data, $user) {
            $adults    = (int) ($data['adults'] ?? config('booking.default_adults', 1));
            $priceEach = max(0, (float) $data['price']);
            $subtotal  = $priceEach * $adults;
            $pricing   = $this->pricing->calculate($subtotal, $user, $data['coupon_code'] ?? null);

            $booking = Booking::create([
                'user_id'           => $user->id,
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
                    'departure_airport' => $data['departure_airport'] ?? null,
                    'arrival_airport'   => $data['arrival_airport'] ?? null,
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

            return $this->bookingResponse($booking, $pricing, 'Flight booked successfully!');
        });
    }

    private function bookingResponse(Booking $booking, array $pricing, string $message): array
    {
        return [
            'success'           => true,
            'booking_reference' => $booking->booking_reference,
            'subtotal'          => $pricing['subtotal'],
            'discount'          => $pricing['discount'],
            'service_fee'       => $pricing['service_fee'],
            'total_price'       => $pricing['total'],
            'message'           => $message,
        ];
    }
}
