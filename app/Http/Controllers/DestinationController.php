<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Services\DestinationEnrichmentService;

class DestinationController extends Controller
{
    public function __construct(private DestinationEnrichmentService $enrichment) {}

    public function index()
    {
        return view('destinations.index');
    }

    public function show($id)
    {
        // Check if it's an API result (starts with 'api_')
        if (str_starts_with($id, 'api_')) {
            // Handle API-based destination
            $name = request('name', '');
            $country = request('country', '');
            
            if (!$name || !$country) {
                abort(404);
            }

            $destination = $this->enrichment->findOrCreateDestination($name, $country);
            
            if (!$destination) {
                abort(404);
            }

            $enrichedData = $this->enrichment->getDestinationData($name, $country);
            
            return view('destinations.show', [
                'destination' => (object) $destination,
                'related' => [],
                'enrichedData' => $enrichedData,
                'isApiResult' => true,
            ]);
        }

        // Regular database destination
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

        // Get enriched data for database destinations too
        $enrichedData = $this->enrichment->getDestinationData(
            $destination->name,
            $destination->country
        );

        return view('destinations.show', [
            'destination' => $destination,
            'related' => $related,
            'enrichedData' => $enrichedData,
            'isApiResult' => false,
        ]);
    }
}
