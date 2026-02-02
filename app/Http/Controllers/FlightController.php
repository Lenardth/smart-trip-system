<?php

namespace App\Http\Controllers;

use App\Models\Flight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FlightController extends Controller
{
    public function index()
    {
        $flights = Flight::where('is_active', true)
            ->orderBy('departure_time', 'asc')
            ->paginate(10);

        return view('flights.index', compact('flights'));
    }

    public function create()
    {
        // Only agencies can create flights
        if (!Auth::user()->isAgency()) {
            abort(403, 'Only travel agencies can list flights.');
        }

        return view('flights.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'flight_number' => 'required|string|max:20',
            'airline' => 'required|string|max:100',
            'departure_city' => 'required|string|max:100',
            'arrival_city' => 'required|string|max:100',
            'departure_time' => 'required|date',
            'arrival_time' => 'required|date|after:departure_time',
            'price' => 'required|numeric|min:0',
            'seats_available' => 'required|integer|min:1',
            'class' => 'required|in:economy,business,first',
            'description' => 'nullable|string',
        ]);

        $flight = Flight::create([
            'user_id' => Auth::id(),
            'flight_number' => $request->flight_number,
            'airline' => $request->airline,
            'departure_city' => $request->departure_city,
            'arrival_city' => $request->arrival_city,
            'departure_time' => $request->departure_time,
            'arrival_time' => $request->arrival_time,
            'price' => $request->price,
            'seats_available' => $request->seats_available,
            'class' => $request->class,
            'description' => $request->description,
        ]);

        return redirect()->route('flights.show', $flight)
            ->with('success', 'Flight listed successfully!');
    }

    public function show(Flight $flight)
    {
        return view('flights.show', compact('flight'));
    }

    public function edit(Flight $flight)
    {
        // Only the agency that owns the flight can edit it
        if (Auth::id() !== $flight->user_id) {
            abort(403, 'Unauthorized action.');
        }

        return view('flights.edit', compact('flight'));
    }

    public function update(Request $request, Flight $flight)
    {
        // Only the agency that owns the flight can update it
        if (Auth::id() !== $flight->user_id) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'flight_number' => 'required|string|max:20',
            'airline' => 'required|string|max:100',
            'departure_city' => 'required|string|max:100',
            'arrival_city' => 'required|string|max:100',
            'departure_time' => 'required|date',
            'arrival_time' => 'required|date|after:departure_time',
            'price' => 'required|numeric|min:0',
            'seats_available' => 'required|integer|min:0',
            'class' => 'required|in:economy,business,first',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $flight->update($request->all());

        return redirect()->route('flights.show', $flight)
            ->with('success', 'Flight updated successfully!');
    }

    public function destroy(Flight $flight)
    {
        // Only the agency that owns the flight can delete it
        if (Auth::id() !== $flight->user_id) {
            abort(403, 'Unauthorized action.');
        }

        $flight->delete();

        return redirect()->route('dashboard')
            ->with('success', 'Flight deleted successfully!');
    }
}
