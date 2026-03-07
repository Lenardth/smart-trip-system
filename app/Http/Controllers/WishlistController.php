<?php

namespace App\Http\Controllers;

use App\Models\SavedDestination;
use App\Models\Destination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function index()
    {
        $wishlistItems = SavedDestination::with('destination.continent')
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('wishlist', compact('wishlistItems'));
    }

    public function store(Request $request)
    {
        $request->validate(['destination_id' => 'required|exists:destinations,id']);

        SavedDestination::firstOrCreate([
            'user_id'        => Auth::id(),
            'destination_id' => $request->destination_id,
        ]);

        return response()->json(['success' => true, 'message' => 'Destination saved to wishlist.']);
    }

    public function destroy(int $destinationId)
    {
        SavedDestination::where('user_id', Auth::id())
            ->where('destination_id', $destinationId)
            ->delete();

        return response()->json(['success' => true, 'message' => 'Destination removed from wishlist.']);
    }
}
