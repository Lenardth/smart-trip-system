<?php

namespace App\Http\Controllers;

use App\Models\Flight;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

        return view('flights.index', compact('flights'));
    }

    public function search(Request $request)
    {
        $request->validate([
            'from'           => 'required|string',
            'to'             => 'required|string',
            'departure_date' => 'required|date',
            'return_date'    => 'nullable|date|after:departure_date',
            'passengers'     => 'required|integer|min:1',
            'class'          => 'required|in:economy,business,first',
        ]);

        $flights = Flight::where('departure_city', 'like', '%' . $request->from . '%')
            ->where('arrival_city', 'like', '%' . $request->to . '%')
            ->whereDate('departure_time', $request->departure_date)
            ->where('class', $request->class)
            ->where('seats_available', '>=', $request->passengers)
            ->where('is_active', true)
            ->orderBy('departure_time')
            ->get();

        return response()->json([
            'success' => true,
            'flights' => $flights,
            'count'   => $flights->count(),
        ]);
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
            'flight_number'  => 'required|unique:flights|max:20',
            'airline'        => 'required|max:100',
            'departure_city' => 'required|max:100',
            'arrival_city'   => 'required|max:100',
            'departure_time' => 'required|date|after:now',
            'arrival_time'   => 'required|date|after:departure_time',
            'price'          => 'required|numeric|min:0',
            'total_seats'    => 'required|integer|min:1',
            'aircraft_type'  => 'nullable|max:50',
            'class'          => 'required|in:economy,business,first',
        ]);

        $validated['user_id']         = Auth::id();
        $validated['seats_available'] = $validated['total_seats'];
        $validated['is_active']       = true;

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
                'user_id'           => Auth::id(),
                'flight_id'         => $flight->id,
                'passenger_count'   => $validated['seats'],
                'total_price'       => $flight->price * $validated['seats'],
                'status'            => 'confirmed',
                'booking_reference' => strtoupper(Str::random(8)),
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
