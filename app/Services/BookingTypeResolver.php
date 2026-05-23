<?php

namespace App\Services;

class BookingTypeResolver
{
    public const TYPE_FLIGHT = 'flight';
    public const TYPE_ACCOMMODATION = 'hotels';
    public const TYPE_UNKNOWN = 'unknown';

    /**
     * Determine booking type from passenger details or trip id.
     * Accepts the Eloquent cast object or plain array.
     *
     * @param mixed $passengerDetails
     */
    public static function resolve($passengerDetails, ?int $tripId): string
    {
        if ($passengerDetails && isset($passengerDetails['airline'])) {
            return self::TYPE_FLIGHT;
        }

        if ($passengerDetails && (($passengerDetails['type'] ?? '') === 'accommodation')) {
            return self::TYPE_ACCOMMODATION;
        }

        if ($tripId) {
            return self::TYPE_UNKNOWN;
        }

        return self::TYPE_UNKNOWN;
    }

    /**
     * Resolve a human-friendly title for a booking.
     * @param mixed $passengerDetails
     */
    public static function resolveTitle($passengerDetails, string $bookingReference): string
    {
        if (!$passengerDetails) {
            return "Booking #{$bookingReference}";
        }

        $dep = $passengerDetails['departure_airport'] ?? null;
        $arr = $passengerDetails['arrival_airport'] ?? null;
        if ($dep && $arr) {
            return "{$dep} → {$arr}";
        }

        $name = $passengerDetails['name'] ?? null;
        if ($name) {
            return $name;
        }

        $city = $passengerDetails['city'] ?? null;
        if ($city) {
            return "Stay in {$city}";
        }

        return "Booking #{$bookingReference}";
    }
}
