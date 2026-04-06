<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    const PREMIUM_PRICE = 9.99;
    const PREMIUM_DAYS  = 30;

    public function status(): JsonResponse
    {
        $user = Auth::user();
        $sub  = Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('ends_at', '>', now())
            ->latest()
            ->first();

        return response()->json([
            'is_premium'    => $user->is_premium,
            'premium_until' => $user->premium_until?->toDateString(),
            'subscription'  => $sub ? [
                'plan'       => $sub->plan,
                'ends_at'    => $sub->ends_at->toDateString(),
                'days_left'  => now()->diffInDays($sub->ends_at),
            ] : null,
            'price'         => self::PREMIUM_PRICE,
            'benefits'      => [
                'No service fees on bookings',
                'Priority AI trip suggestions',
                'Exclusive member-only deals',
                'Advanced itinerary export (PDF)',
                'Early access to new features',
            ],
        ]);
    }

    public function subscribe(Request $request): JsonResponse
    {
        $request->validate([
            'payment_reference' => 'required|string|max:100',
        ]);

        $user = Auth::user();

        // In production, verify payment with your gateway here
        // For now we trust the payment_reference as proof of payment

        $starts = now();
        $ends   = now()->addDays(self::PREMIUM_DAYS);

        Subscription::create([
            'user_id'           => $user->id,
            'plan'              => 'premium',
            'amount_paid'       => self::PREMIUM_PRICE,
            'status'            => 'active',
            'starts_at'         => $starts,
            'ends_at'           => $ends,
            'payment_reference' => $request->payment_reference,
        ]);

        $user->update([
            'is_premium'    => true,
            'premium_until' => $ends,
        ]);

        return response()->json([
            'success'       => true,
            'message'       => 'Welcome to Smart Booking Premium!',
            'premium_until' => $ends->toDateString(),
        ]);
    }

    public function cancel(): JsonResponse
    {
        $user = Auth::user();

        Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->update(['status' => 'cancelled']);

        $user->update(['is_premium' => false, 'premium_until' => null]);

        return response()->json(['success' => true, 'message' => 'Subscription cancelled.']);
    }

    public function page()
    {
        $user   = Auth::user();
        $status = $this->status()->getData(true);
        return view('subscription.index', compact('user', 'status'));
    }
}
