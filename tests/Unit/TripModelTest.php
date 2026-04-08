<?php

namespace Tests\Unit;

use App\Models\Trip;
use PHPUnit\Framework\TestCase;

class TripModelTest extends TestCase
{
    private function makeTrip(array $attrs = []): Trip
    {
        $trip = new Trip();
        foreach ($attrs as $key => $value) {
            $trip->$key = $value;
        }
        return $trip;
    }

    public function test_duration_label_returns_human_readable_value(): void
    {
        $map = [
            'weekend'   => 'Long Weekend',
            'week'      => 'One Week',
            'two_weeks' => 'Two Weeks',
            'month'     => 'One Month+',
            'flexible'  => 'Flexible',
        ];

        foreach ($map as $key => $expected) {
            $trip = $this->makeTrip(['duration' => $key]);
            $this->assertSame($expected, $trip->getDurationLabelAttribute());
        }
    }

    public function test_duration_label_falls_back_to_raw_value(): void
    {
        $trip = $this->makeTrip(['duration' => 'custom_value']);
        $this->assertSame('custom_value', $trip->getDurationLabelAttribute());
    }

    public function test_duration_label_returns_dash_when_null(): void
    {
        $trip = $this->makeTrip(['duration' => null]);
        $this->assertSame('-', $trip->getDurationLabelAttribute());
    }

    public function test_budget_label_returns_human_readable_value(): void
    {
        $map = [
            'backpacker' => 'Backpacker',
            'budget'     => 'Budget',
            'mid'        => 'Mid-Range',
            'premium'    => 'Premium',
            'luxury'     => 'Luxury',
        ];

        foreach ($map as $key => $expected) {
            $trip = $this->makeTrip(['budget' => $key]);
            $this->assertSame($expected, $trip->getBudgetLabelAttribute());
        }
    }

    public function test_budget_label_falls_back_to_raw_value(): void
    {
        $trip = $this->makeTrip(['budget' => 'unknown_tier']);
        $this->assertSame('unknown_tier', $trip->getBudgetLabelAttribute());
    }

    public function test_budget_label_returns_dash_when_null(): void
    {
        $trip = $this->makeTrip(['budget' => null]);
        $this->assertSame('-', $trip->getBudgetLabelAttribute());
    }
}
