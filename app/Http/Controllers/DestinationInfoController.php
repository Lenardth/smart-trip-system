<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Services\DestinationEnrichmentService;

class DestinationInfoController extends Controller
{
    public function __construct(private DestinationEnrichmentService $enrichment) {}

    public function show($id)
    {
        // Check if it's an API result (starts with 'api_')
        if (str_starts_with($id, 'api_')) {
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
            
            return view('destination-info.show', [
                'destination' => (object) $destination,
                'enrichedData' => $enrichedData,
                'isApiResult' => true,
            ]);
        }

        // Regular database destination
        $destination = Destination::findOrFail($id);

        // Get enriched data
        $enrichedData = $this->enrichment->getDestinationData(
            $destination->name,
            $destination->country
        );

        return view('destination-info.show', [
            'destination' => $destination,
            'enrichedData' => $enrichedData,
            'isApiResult' => false,
        ]);
    }
}
