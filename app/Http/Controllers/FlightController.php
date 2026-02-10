<?php

namespace App\Http\Controllers;

use App\Models\Flight;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FlightController extends Controller
{
    public function index(Request $request)
    {
        $query = Flight::query();

        if ($request->filled('from')) {
            $query->where('departure_city', 'like', '%' . $request->from . '%');
        }
        if ($request->filled('to')) {
            $query->where('arrival_city', 'like', '%' . $request->to . '%');
        }
        if ($request->filled('date')) {
            $query->whereDate('departure_time', $request->date);
        }
        if ($request->filled('class')) {
            $query->where('class', $request->class);
        }

        $flights = $query->orderBy('departure_time')->paginate(12);
        return view('flights', compact('flights'));
    }

    public function show(Flight $flight)
    {
        $flight->load('user', 'bookings');
        return view('flights.show', compact('flight'));
    }

    public function create()
    {
        if (!Auth::user()->isAgency()) {
            return redirect()->route('flights.index')
                ->with('error', 'Only travel agencies can create flights.');
        }
        return view('flights.create');
    }

    public function store(Request $request)
    {
        if (!Auth::user()->isAgency()) {
            return redirect()->route('flights.index')
                ->with('error', 'Only travel agencies can create flights.');
        }

        $validated = $request->validate([
            'flight_number' => 'required|unique:flights|max:20',
            'airline' => 'required|max:100',
            'departure_city' => 'required|max:100',
            'arrival_city' => 'required|max:100',
            'departure_time' => 'required|date|after:now',
            'arrival_time' => 'required|date|after:departure_time',
            'price' => 'required|numeric|min:0',
            'total_seats' => 'required|integer|min:1',
            'aircraft_type' => 'nullable|max:50',
            'class' => 'required|in:economy,business,first',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['seats_available'] = $validated['total_seats'];
        $validated['is_active'] = true;

        $flight = Flight::create($validated);

        return redirect()->route('flights.index')
            ->with('success', 'Flight created successfully! Flight Number: ' . $flight->flight_number);
    }

    public function myFlights()
    {
        if (!Auth::user()->isAgency()) {
            return redirect()->route('dashboard');
        }

        $flights = Flight::where('user_id', Auth::id())
            ->with('bookings')
            ->orderBy('departure_time', 'desc')
            ->paginate(10);

        return view('flights.my-flights', compact('flights'));
    }

    public function book(Request $request, Flight $flight)
    {
        if (Auth::user()->isAgency()) {
            return back()->with('error', 'Agencies cannot book flights.');
        }

        $validated = $request->validate([
            'seats' => 'required|integer|min:1|max:9',
        ]);

        if ($flight->seats_available < $validated['seats']) {
            return back()->with('error', 'Not enough seats available.');
        }

        DB::beginTransaction();
        try {
            $booking = Booking::create([
                'user_id' => Auth::id(),
                'flight_id' => $flight->id,
                'seats_booked' => $validated['seats'],
                'total_price' => $flight->price * $validated['seats'],
                'status' => 'confirmed',
            ]);

            $flight->decrement('seats_available', $validated['seats']);

            DB::commit();

            return redirect()->route('bookings.show', $booking)
                ->with('success', 'Flight booked successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Booking failed. Please try again.');
        }
    }

    public function cancel(Flight $flight)
    {
        if ($flight->user_id !== Auth::id()) {
            return back()->with('error', 'Unauthorized.');
        }

        $flight->update(['is_active' => false]);
        return back()->with('success', 'Flight cancelled successfully.');
    }
}
