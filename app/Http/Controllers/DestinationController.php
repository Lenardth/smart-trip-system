<?php

namespace App\Http\Controllers;

use App\Models\Destination;

class DestinationController extends Controller
{
    public function index()
    {
        return view('destinations.index');
    }

    public function show($id)
    {
        $destination = Destination::findOrFail($id);

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
}
