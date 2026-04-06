<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $coupons = [
            ['code' => 'WELCOME10',  'type' => 'percent', 'value' => 10,  'description' => '10% off your first booking',    'uses_limit' => null],
            ['code' => 'SAVE20',     'type' => 'percent', 'value' => 20,  'description' => '20% off — limited time',         'uses_limit' => 100, 'max_discount' => 50],
            ['code' => 'FLAT25',     'type' => 'fixed',   'value' => 25,  'description' => '$25 off orders over $100',       'min_order'  => 100],
            ['code' => 'SUMMER15',   'type' => 'percent', 'value' => 15,  'description' => 'Summer travel discount',         'uses_limit' => 200],
            ['code' => 'AGENCY5',    'type' => 'percent', 'value' => 5,   'description' => 'Agency partner discount',        'uses_limit' => null],
        ];

        foreach ($coupons as $c) {
            Coupon::firstOrCreate(['code' => $c['code']], array_merge([
                'type'         => 'percent',
                'value'        => 10,
                'min_order'    => 0,
                'max_discount' => null,
                'uses_limit'   => null,
                'uses_per_user'=> 1,
                'is_active'    => true,
            ], $c));
        }
    }
}
