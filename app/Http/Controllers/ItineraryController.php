<?php

namespace App\Http\Controllers;

use App\Models\Itinerary;
use App\Models\ItineraryDayPlan;
use App\Models\ItineraryDestination;
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

    public function apiIndex(): \Illuminate\Http\JsonResponse
    {
        $itineraries = Itinerary::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($it) {
                return [
                    'id'          => $it->id,
                    'destination' => $it->formatted_destination,
                    'mood'        => $it->mood,
                    'companion'   => $it->companion,
                    'travelers'   => $it->travelers,
                    'budget'      => $it->budget,
                    'departure'   => $it->departure_date?->format('M j, Y'),
                    'return'      => $it->return_date?->format('M j, Y'),
                    'duration'    => $it->duration,
                    'created_at'  => $it->created_at->diffForHumans(),
                ];
            });

        return response()->json(['itineraries' => $itineraries]);
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
        $budget      = (int) ($validated['budget'] ?? 2500);

        try {
            $depDate = new \DateTime($validated['departureDate'] ?? '+7 days');
            $retDate = new \DateTime($validated['returnDate']    ?? '+14 days');
        } catch (\Exception) {
            $depDate = new \DateTime('+7 days');
            $retDate = new \DateTime('+14 days');
        }

        $data = [
            'mood'          => $mood,
            'destination'   => Itinerary::resolveDestinationName($destination),
            'companion'     => $validated['companion'] ?? 'solo',
            'travelers'     => $validated['travelers'] ?? 1,
            'departureDate' => $depDate->format('Y-m-d'),
            'returnDate'    => $retDate->format('Y-m-d'),
            'budget'        => $budget,
            'requirements'  => $validated['requirements'] ?? null,
            'itineraryId'   => 'SB-' . strtoupper(substr(md5($destination . now()), 0, 8)),
            'generatedAt'   => now()->format('F j, Y g:i A'),
            'itinerary'     => $this->buildDayPlans($destination, $mood, $budget),
        ];

        $pdf = Pdf::loadView('pdf.itinerary', ['data' => $data])
            ->setPaper('a4', 'portrait');

        return $pdf->download('SmartBooking_Itinerary_' . date('Ymd') . '.pdf');
    }

    private function buildDayPlans(string $destination, string $mood, int $budget): array
    {
        $plans = ItineraryDayPlan::where('destination_code', $destination)
            ->orderBy('day')
            ->get();

        if ($plans->isEmpty()) {
            $plans = ItineraryDayPlan::where('destination_code', 'bali')
                ->orderBy('day')
                ->get();
        }

        return $plans->map(function ($plan) use ($mood, $budget) {
            $desc = $plan->description;

            if ($plan->day === 3) {
                if ($mood === 'adventurous') {
                    $desc .= ' Add volcano hiking.';
                } elseif ($mood === 'relaxed') {
                    return ['day' => 3, 'title' => 'Spa Day', 'desc' => 'Full-day spa retreat. Meditation and yoga sessions.'];
                }
            }

            if ($plan->day === 4 && $mood === 'foodie') {
                return ['day' => 4, 'title' => 'Culinary Experience', 'desc' => 'Food market tour and cooking masterclass.'];
            }

            if ($budget > 5000) {
                $desc .= ' Luxury accommodations and private tours included.';
            }

            return ['day' => $plan->day, 'title' => $plan->title, 'desc' => $desc];
        })->toArray();
    }
}
