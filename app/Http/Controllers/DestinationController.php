<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use Illuminate\Http\Request;

class DestinationController extends Controller
{
    public function index()
    {
        return view('destinations.index');
    }

    public function show($id)
    {
        $destination = Destination::findOrFail($id);

        // Related destinations: same mood or category, excluding current
        $related = Destination::active()
            ->where('id', '!=', $destination->id)
            ->where(function ($q) use ($destination) {
                $q->where('mood', $destination->mood)
                  ->orWhere('category', $destination->category);
            })
            ->inRandomOrder()
            ->limit(4)
            ->get();

        return view('destinations.show', compact('destination', 'related'));
    }

    public function addToCompare(Request $request)
    {
        $destinationId = $request->input('destination_id');
        $compareList   = session('compare_list', []);

        if (!in_array($destinationId, $compareList) && count($compareList) < 3) {
            $compareList[] = $destinationId;
            session(['compare_list' => $compareList]);
            return back()->with('success', 'Destination added to compare list');
        }

        return back()->with('error', 'Cannot add more than 3 destinations to compare');
    }

    public function removeFromCompare(Request $request)
    {
        $destinationId = $request->input('destination_id');
        $compareList   = session('compare_list', []);

        $compareList = array_values(array_filter($compareList, fn($id) => $id != $destinationId));

        session(['compare_list' => $compareList]);
        return back()->with('success', 'Destination removed from compare list');
    }

    public function compare()
    {
        $compareIds   = session('compare_list', []);
        $destinations = Destination::whereIn('id', $compareIds)->get();

        return view('destinations.compare', compact('destinations', 'compareIds'));
    }
}
