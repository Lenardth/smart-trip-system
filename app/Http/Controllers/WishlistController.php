<?php

namespace App\Http\Controllers;

use App\Models\SavedDestination;
use App\Models\Destination;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function index()
    {
        $wishlistItems = SavedDestination::with('destination')
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('wishlist.index', compact('wishlistItems'));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate(['destination_id' => 'required|exists:destinations,id']);

        SavedDestination::firstOrCreate([
            'user_id'        => Auth::id(),
            'destination_id' => $request->destination_id,
        ]);

        return response()->json(['success' => true, 'message' => 'Destination saved to wishlist.']);
    }

    public function destroy(int $destinationId): JsonResponse
    {
        SavedDestination::where('user_id', Auth::id())
            ->where('destination_id', $destinationId)
            ->delete();

        return response()->json(['success' => true, 'message' => 'Destination removed from wishlist.']);
    }

    public function count(): JsonResponse
    {
        $ids = SavedDestination::where('user_id', Auth::id())
            ->pluck('destination_id');

        return response()->json([
            'count' => $ids->count(),
            'ids'   => $ids->values(),
        ]);
    }
}