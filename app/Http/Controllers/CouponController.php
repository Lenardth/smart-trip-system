<?php

namespace App\Http\Controllers;

use App\Contracts\PricingServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CouponController extends Controller
{
    public function __construct(private PricingServiceInterface $pricing) {}

    public function validate(Request $request): JsonResponse
    {
        $request->validate([
            'code'     => 'required|string|max:32',
            'subtotal' => 'required|numeric|min:0',
        ]);

        $result = $this->pricing->validateCoupon(
            $request->code,
            (float) $request->subtotal,
            Auth::id()
        );

        return response()->json($result, $result['valid'] ? 200 : 422);
    }
}
