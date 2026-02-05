<?php

namespace App\Http\Controllers;

use App\Models\Flight;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FlightController extends Controller
{
    /**
     * Display available flights
     */
    public function index(Request $request)
    {
        $query = Flight::with('agency')->active();

        // Search filters
        if ($request->filled('from')) {
            $query->where('from_city', 'like', '%' . $request->from . '%');
        }
        if ($request->filled('to')) {
            $query->where('to_city', 'like', '%' . $request->to . '%');
        }
        if ($request->filled('date')) {
            $query->whereDate('departure_time', $request->date);
        }
        if ($request->filled('class')) {
            $query->where('class', $request->class);
        }

        $flights = $query->orderBy('departure_time')->paginate(12);

        return view('flights.index', compact('flights'));
    }

    /**
     * Show flight details
     */
    public function show(Flight $flight)
    {
        $flight->load('agency', 'bookings');
        return view('flights.show', compact('flight'));
    }

    /**
     * Show create flight form (agencies only)
     */
    public function create()
    {
        if (!Auth::user()->isAgency()) {
            return redirect()->route('flights.index')
                ->with('error', 'Only travel agencies can create flights.');
        }

        return view('flights.create');
    }

    /**
     * Store new flight (agencies only)
     */
    public function store(Request $request)
    {
        if (!Auth::user()->isAgency()) {
            return redirect()->route('flights.index')
                ->with('error', 'Only travel agencies can create flights.');
        }

        $validated = $request->validate([
            'flight_number' => 'required|unique:flights|max:20',
            'airline' => 'required|max:100',
            'from_city' => 'required|max:100',
            'to_city' => 'required|max:100',
            'departure_time' => 'required|date|after:now',
            'arrival_time' => 'required|date|after:departure_time',
            'price' => 'required|numeric|min:0',
            'total_seats' => 'required|integer|min:1',
            'aircraft_type' => 'nullable|max:50',
            'class' => 'required|in:economy,business,first',
        ]);

        $validated['agency_id'] = Auth::id();
        $validated['available_seats'] = $validated['total_seats'];
        $validated['status'] = 'active';

        $flight = Flight::create($validated);

        return redirect()->route('flights.index')
            ->with('success', 'Flight created successfully! Flight Number: ' . $flight->flight_number);
    }

    /**
     * Show my flights (agency dashboard)
     */
    public function myFlights()
    {
        if (!Auth::user()->isAgency()) {
            return redirect()->route('dashboard');
        }

        $flights = Flight::byAgency(Auth::id())
            ->with('bookings')
            ->orderBy('departure_time', 'desc')
            ->paginate(10);

        return view('flights.my-flights', compact('flights'));
    }

    /**
     * Book a flight
     */
    public function book(Request $request, Flight $flight)
    {
        if (Auth::user()->isAgency()) {
            return back()->with('error', 'Agencies cannot book flights.');
        }

        $validated = $request->validate([
            'seats' => 'required|integer|min:1|max:9',
        ]);

        if (!$flight->hasAvailableSeats($validated['seats'])) {
            return back()->with('error', 'Not enough seats available.');
        }

        DB::beginTransaction();
        try {
            // Create booking
            $booking = Booking::create([
                'user_id' => Auth::id(),
                'flight_id' => $flight->id,
                'seats_booked' => $validated['seats'],
                'total_price' => $flight->price * $validated['seats'],
                'status' => 'confirmed',
            ]);

            // Update available seats
            $flight->decrement('available_seats', $validated['seats']);

            DB::commit();

            return redirect()->route('bookings.show', $booking)
                ->with('success', 'Flight booked successfully! Booking Reference: ' . $booking->booking_reference);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Booking failed. Please try again.');
        }
    }

    /**
     * Cancel flight (agency only)
     */
    public function cancel(Flight $flight)
    {
        if ($flight->agency_id !== Auth::id()) {
            return back()->with('error', 'Unauthorized.');
        }

        $flight->update(['status' => 'cancelled']);

        return back()->with('success', 'Flight cancelled successfully.');
    }
}
