<?php

namespace App\Http\Controllers;

use App\Models\Itinerary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class ItineraryController extends Controller
{
    public function index()
    {
        $itineraries = Itinerary::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('itineraries.index', compact('itineraries'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'mood'          => 'required|string',
            'destination'   => 'required|string',
            'companion'     => 'required|string',
            'travelers'     => 'required|integer|min:1|max:20',
            'departureDate' => 'required|date',
            'returnDate'    => 'required|date|after:departureDate',
            'budget'        => 'required|integer|min:500',
            'requirements'  => 'nullable|string',
            'itineraryId'   => 'required|string',
            'generatedAt'   => 'required|date',
        ]);

        $itinerary = Itinerary::create([
            'user_id'        => Auth::id(),
            'itinerary_id'   => $validated['itineraryId'],
            'mood'           => $validated['mood'],
            'destination'    => $validated['destination'],
            'companion'      => $validated['companion'],
            'travelers'      => $validated['travelers'],
            'departure_date' => $validated['departureDate'],
            'return_date'    => $validated['returnDate'],
            'budget'         => $validated['budget'],
            'requirements'   => $validated['requirements'] ?? null,
            'generated_at'   => $validated['generatedAt'],
        ]);

        return response()->json([
            'success'   => true,
            'message'   => 'Itinerary saved successfully!',
            'itinerary' => $itinerary,
        ]);
    }

    public function show($id)
    {
        $itinerary = Itinerary::where('user_id', Auth::id())->findOrFail($id);

        return view('itineraries.show', compact('itinerary'));
    }

    public function destroy($id)
    {
        $itinerary = Itinerary::where('user_id', Auth::id())->findOrFail($id);

        $itinerary->delete();

        return response()->json([
            'success' => true,
            'message' => 'Itinerary deleted successfully!',
        ]);
    }

    public function export(Request $request)
    {
        $validated = $request->validate([
            'mood'          => 'required|string',
            'destination'   => 'required|string',
            'companion'     => 'nullable|string',
            'travelers'     => 'nullable|integer',
            'departureDate' => 'nullable|string',
            'returnDate'    => 'nullable|string',
            'budget'        => 'nullable|integer',
            'requirements'  => 'nullable|string',
        ]);

        $destination = $validated['destination'];
        $mood        = strtolower($validated['mood']);
        $budget      = (int)($validated['budget'] ?? 2500);

        try {
            $depDate = new \DateTime($validated['departureDate'] ?? '+7 days');
            $retDate = new \DateTime($validated['returnDate']    ?? '+14 days');
        } catch (\Exception $e) {
            $depDate = new \DateTime('+7 days');
            $retDate = new \DateTime('+14 days');
        }

        $data = [
            'mood'          => $mood,
            'destination'   => $this->getDestinationName($destination),
            'companion'     => $validated['companion'] ?? 'solo',
            'travelers'     => $validated['travelers'] ?? 1,
            'departureDate' => $depDate->format('Y-m-d'),
            'returnDate'    => $retDate->format('Y-m-d'),
            'budget'        => $budget,
            'requirements'  => $validated['requirements'] ?? null,
            'itineraryId'   => 'SB-' . strtoupper(substr(md5($destination . now()), 0, 8)),
            'generatedAt'   => now()->format('F j, Y g:i A'),
        ];

        $data['itinerary'] = $this->generateItinerary($destination, $mood, $budget);

        $pdf = Pdf::loadView('pdf.itinerary', ['data' => $data])
            ->setPaper('a4', 'portrait');

        return $pdf->download('SmartBooking_Itinerary_' . date('Ymd') . '.pdf');
    }

    private function getDestinationName($code)
    {
        $destinations = [
            'bali'      => 'Bali, Indonesia',
            'kyoto'     => 'Kyoto, Japan',
            'swiss'     => 'Swiss Alps, Switzerland',
            'santorini' => 'Santorini, Greece',
            'paris'     => 'Paris, France',
            'lisbon'    => 'Lisbon, Portugal',
            'bangkok'   => 'Bangkok, Thailand',
            'amalfi'    => 'Amalfi Coast, Italy',
            'nz'        => 'New Zealand',
            'morocco'   => 'Morocco',
        ];

        return $destinations[$code] ?? ucfirst($code);
    }

    private function generateItinerary($destination, $mood, $budget)
    {
        $itineraries = [
            'bali' => [
                ['day' => 1, 'title' => 'Arrival in Ubud',        'desc' => 'Arrive at Ngurah Rai Airport. Transfer to your villa in Ubud. Traditional Balinese welcome ceremony.'],
                ['day' => 2, 'title' => 'Rice Terraces & Temples', 'desc' => 'Morning at Tegallalang Rice Terraces. Visit Tirta Empul temple for purification.'],
                ['day' => 3, 'title' => 'Adventure Day',           'desc' => 'White-water rafting on Ayung River. Evening Kecak dance performance.'],
                ['day' => 4, 'title' => 'Cooking & Culture',       'desc' => 'Balinese cooking class. Explore Ubud art market and local crafts.'],
                ['day' => 5, 'title' => 'Beach Time',              'desc' => 'Transfer to Seminyak. Relax at the beach, enjoy sunset cocktails.'],
                ['day' => 6, 'title' => 'Island Exploration',      'desc' => 'Day trip to Nusa Penida for snorkeling and cliff views.'],
                ['day' => 7, 'title' => 'Spa & Departure',         'desc' => 'Morning spa treatment. Last-minute shopping. Departure transfer.'],
            ],
            'paris' => [
                ['day' => 1, 'title' => 'Arrival in Paris',        'desc' => 'Arrive at CDG. Check into hotel near Louvre. Evening Seine River walk.'],
                ['day' => 2, 'title' => 'Eiffel Tower & Louvre',   'desc' => 'Morning at Eiffel Tower. Afternoon at Louvre Museum.'],
                ['day' => 3, 'title' => 'Notre-Dame & Montmartre', 'desc' => 'Visit Notre-Dame Cathedral. Explore Montmartre and Sacré-Cœur.'],
                ['day' => 4, 'title' => 'Versailles Day Trip',     'desc' => 'Full-day trip to Palace of Versailles. Hall of Mirrors tour.'],
                ['day' => 5, 'title' => 'Art & Fashion',           'desc' => 'Visit Musée d\'Orsay. Shopping in Le Marais district.'],
                ['day' => 6, 'title' => 'Food Tour',               'desc' => 'French cooking class. Cheese and wine tasting in Latin Quarter.'],
                ['day' => 7, 'title' => 'Au Revoir Paris',         'desc' => 'Morning at Luxembourg Gardens. Final croissants. Departure.'],
            ],
        ];

        $baseItinerary = $itineraries[$destination] ?? $itineraries['bali'];

        if ($mood === 'adventurous') {
            $baseItinerary[2]['desc'] .= ' Add volcano hiking.';
        } elseif ($mood === 'relaxed') {
            $baseItinerary[2] = ['day' => 3, 'title' => 'Spa Day', 'desc' => 'Full-day spa retreat. Meditation and yoga sessions.'];
        } elseif ($mood === 'foodie') {
            $baseItinerary[3] = ['day' => 4, 'title' => 'Culinary Experience', 'desc' => 'Food market tour and cooking masterclass.'];
        }

        if ($budget > 5000) {
            foreach ($baseItinerary as &$day) {
                $day['desc'] .= ' Luxury accommodations and private tours included.';
            }
        }

        return $baseItinerary;
    }
}
