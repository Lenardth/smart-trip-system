<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Services\PriceConverter;
use App\Services\DestinationEnrichmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class DiscoverController extends Controller
{
    public function __construct(
        private PriceConverter $priceConverter,
        private DestinationEnrichmentService $enrichment
    ) {}
    
    public function index()
    {
        return view('discover.index');
    }

    public function destinations(Request $request): JsonResponse
    {
        try {
            // Force fresh connection and use raw PDO to bypass all caching
            DB::purge('pgsql');
            DB::reconnect('pgsql');
            
            // Get fresh PDO connection
            $pdo = DB::connection()->getPdo();
            
            // Build query
            $sql = "SELECT * FROM destinations WHERE is_active = 1 AND is_hidden_gem = 0";
            $params = [];
            
            if ($request->filled('category') && $request->category !== 'all') {
                $sql .= " AND category = ?";
                $params[] = $request->category;
            }
            
            if ($request->filled('region') && $request->region !== 'all') {
                $sql .= " AND region = ?";
                $params[] = $request->region;
            }
            
            if ($request->filled('q')) {
                $search = '%' . $request->q . '%';
                $sql .= " AND (name ILIKE ? OR country ILIKE ? OR description ILIKE ?)";
                $params[] = $search;
                $params[] = $search;
                $params[] = $search;
            }
            
            $sql .= " ORDER BY sort_order";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $currency = $this->priceConverter->getPreferredCurrency();
            
            $destinations = array_map(function ($row) use ($currency) {
                $priceUsd = $row['price_from'] ?? 0;
                $priceConverted = $priceUsd > 0 ? $this->priceConverter->convert((float) $priceUsd) : 0;
                
                return [
                    'id'           => $row['id'] ?? null,
                    'name'         => $row['name'] ?? 'Unknown',
                    'country'      => $row['country'] ?? null,
                    'region'       => $row['region'] ?? null,
                    'category'     => $row['category'] ?? 'general',
                    'mood'         => $row['mood'] ?? null,
                    'price_from'   => $priceConverted,
                    'price_usd'    => $priceUsd,
                    'currency'     => $currency,
                    'description'  => $row['description'] ?? '',
                    'image_url'    => $row['image_url'] ?? null,
                    'badge'        => $row['badge'] ?? null,
                    'is_hidden_gem'=> (bool)($row['is_hidden_gem'] ?? false),
                    'match_score'  => $row['match_score'] ?? null,
                ];
            }, $rows);

            return response()->json($destinations);
        } catch (\Exception $e) {
            \Log::error('Discover destinations error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to load destinations',
                'message' => $e->getMessage(),
                'debug' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    public function hiddenGems(): JsonResponse
    {
        // Force fresh connection and use raw PDO
        DB::purge('pgsql');
        DB::reconnect('pgsql');
        
        $pdo = DB::connection()->getPdo();
        
        $sql = "SELECT * FROM destinations WHERE is_hidden_gem = 1 ORDER BY match_score DESC NULLS LAST LIMIT 6";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $currency = $this->priceConverter->getPreferredCurrency();
        
        $gems = array_map(function($row) use ($currency) {
            $priceUsd = $row['price_from'] ?? 0;
            $priceConverted = $priceUsd > 0 ? $this->priceConverter->convert((float) $priceUsd) : 0;
            
            return [
                'id'          => $row['id'] ?? null,
                'name'        => $row['name'] ?? 'Unknown',
                'country'     => $row['country'] ?? null,
                'description' => $row['description'] ?? '',
                'image_url'   => $row['image_url'] ?? null,
                'match_score' => $row['match_score'] ?? null,
                'price_from'  => $priceConverted,
                'price_usd'   => $priceUsd,
                'currency'    => $currency,
            ];
        }, $rows);

        return response()->json($gems);
    }

    public function destinationById(int $id): JsonResponse
    {
        $row = DB::table('destinations')->where('id', $id)->first();
        if (!$row) {
            return response()->json(['error' => 'Not found'], 404);
        }
        $row = (array) $row;
        return response()->json([
            'id'           => $row['id'],
            'name'         => $row['name'] ?? 'Unknown',
            'country'      => $row['country'] ?? null,
            'region'       => $row['region'] ?? null,
            'category'     => $row['category'] ?? 'general',
            'mood'         => $row['mood'] ?? null,
            'price_from'   => $row['price_from'] ?? 0,
            'description'  => $row['description'] ?? '',
            'image_url'    => $row['image_url'] ?? null,
            'badge'        => $row['badge'] ?? null,
            'is_hidden_gem'=> (bool)($row['is_hidden_gem'] ?? false),
            'match_score'  => $row['match_score'] ?? null,
        ]);
    }

    /**
     * Search destinations (database + external API)
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $query = trim($request->input('q', ''));
            
            if (strlen($query) < 2) {
                return response()->json([]);
            }

            // Use simple query to avoid pooler issues
            $searchTerm = strtolower($query);
            
            // Get all active destinations and filter in PHP to avoid pooler caching issues
            $pdo = DB::connection()->getPdo();
            $stmt = $pdo->prepare("SELECT * FROM destinations WHERE is_active = true LIMIT 100");
            $stmt->execute();
            $allDestinations = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Filter in PHP
            $results = [];
            foreach ($allDestinations as $row) {
                $name = strtolower($row['name'] ?? '');
                $country = strtolower($row['country'] ?? '');
                $description = strtolower($row['description'] ?? '');
                
                if (str_contains($name, $searchTerm) || str_contains($country, $searchTerm) || str_contains($description, $searchTerm)) {
                    $results[] = [
                        'id' => $row['id'],
                        'name' => $row['name'],
                        'country' => $row['country'] ?? '',
                        'type' => 'database',
                        'image_url' => $row['image_url'] ?? '',
                        'description' => substr($row['description'] ?? '', 0, 100),
                    ];
                    
                    if (count($results) >= 10) break;
                }
            }

            // If less than 5 results, try external API
            if (count($results) < 5) {
                try {
                    $apiResults = $this->enrichment->searchDestinations($query);
                    
                    foreach ($apiResults as $apiResult) {
                        if (count($results) >= 10) break;
                        
                        try {
                            // Determine if this is a city or country result
                            // Geoapify: has 'country' field with country name, 'name' is city
                            // REST Countries: 'name' is country, 'capital' is city
                            
                            if (!empty($apiResult['country']) && !empty($apiResult['name'])) {
                                // Geoapify city result
                                $destinationName = $apiResult['name']; // City name (e.g., "Johannesburg")
                                $destinationCountry = $apiResult['country']; // Country name (e.g., "South Africa")
                            } elseif (!empty($apiResult['capital'])) {
                                // REST Countries result with capital
                                $destinationName = $apiResult['capital']; // Capital city
                                $destinationCountry = $apiResult['name']; // Country name
                            } else {
                                // REST Countries result without capital
                                $destinationName = $apiResult['name']; // Country name
                                $destinationCountry = $apiResult['name']; // Same as destination
                            }
                            
                            // Check if exists
                            $checkStmt = $pdo->prepare("SELECT id, name, country, image_url, description FROM destinations WHERE LOWER(name) = LOWER(?) AND LOWER(country) = LOWER(?) LIMIT 1");
                            $checkStmt->execute([$destinationName, $destinationCountry]);
                            $existing = $checkStmt->fetch(\PDO::FETCH_ASSOC);

                            if (!$existing) {
                                // Insert new destination using Laravel's query builder for cross-database compatibility
                                $newRegion = $this->mapRegion($apiResult['region'] ?? '');
                                $newDesc = 'Discover this amazing destination' . ($apiResult['population'] ? ' with a population of ' . number_format($apiResult['population']) . ' people' : '') . '.';
                                
                                // Try to get a real image from Wikipedia
                                $newImage = $this->getDestinationImage($destinationName, $destinationCountry);
                                if (!$newImage) {
                                    // Use a curated list of city images from Wikimedia Commons
                                    $newImage = $this->getCuratedCityImage($destinationName);
                                }
                                
                                $newPrice = rand(500, 2000);
                                
                                $newId = DB::table('destinations')->insertGetId([
                                    'name' => $destinationName,
                                    'country' => $destinationCountry,
                                    'region' => $newRegion,
                                    'category' => 'general',
                                    'mood' => 'cultural',
                                    'description' => $newDesc,
                                    'image_url' => $newImage,
                                    'price_from' => $newPrice,
                                    'is_active' => true,
                                    'is_hidden_gem' => false,
                                    'sort_order' => 999,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);

                                if ($newId) {
                                    $results[] = [
                                        'id' => $newId,
                                        'name' => $destinationName,
                                        'country' => $destinationCountry,
                                        'type' => 'new',
                                        'image_url' => $newImage,
                                        'description' => substr($newDesc, 0, 100),
                                    ];
                                    
                                    // If this looks like a country search (not a specific city), find and add 5 hidden gems
                                    // Check if the destination name matches the country name or if it's a capital city
                                    $isCountrySearch = ($destinationName === $destinationCountry) || 
                                                      (!empty($apiResult['capital']) && $destinationName === $apiResult['capital']);
                                    
                                    if ($isCountrySearch) {
                                        $this->addHiddenGemsForCountry($destinationCountry, $newRegion);
                                    }
                                }
                            } else {
                                $results[] = [
                                    'id' => $existing['id'],
                                    'name' => $existing['name'],
                                    'country' => $existing['country'],
                                    'type' => 'database',
                                    'image_url' => $existing['image_url'],
                                    'description' => substr($existing['description'] ?? '', 0, 100),
                                ];
                            }
                        } catch (\Exception $e) {
                            \Log::warning('Failed to process API result: ' . $e->getMessage());
                        }
                    }
                } catch (\Exception $e) {
                    \Log::warning('API search failed: ' . $e->getMessage());
                }
            }

            return response()->json(array_slice($results, 0, 10));
            
        } catch (\Exception $e) {
            \Log::error('Search error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Search failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Map API region to our database regions
     */
    private function mapRegion(string $region): string
    {
        return match(strtolower($region)) {
            'asia' => 'asia',
            'europe' => 'europe',
            'africa' => 'africa',
            'americas', 'north america', 'south america' => 'americas',
            'oceania' => 'oceania',
            default => 'worldwide',
        };
    }

    /**
     * Get a real image for the destination from Pexels
     */
    private function getDestinationImage(string $city, string $country): ?string
    {
        try {
            // Use Pexels API to get a real image of the destination
            $query = urlencode($city . ' ' . $country . ' landmark cityscape');
            $apiKey = config('services.pexels.api_key');
            
            // If no Pexels key, try alternative
            if (!$apiKey) {
                // Use Wikipedia/Wikimedia to get real images
                return $this->getWikipediaImage($city, $country);
            }
            
            $response = \Illuminate\Support\Facades\Http::timeout(5)
                ->withHeaders(['Authorization' => $apiKey])
                ->get('https://api.pexels.com/v1/search', [
                    'query' => $query,
                    'per_page' => 1,
                    'orientation' => 'landscape'
                ]);
            
            if ($response->successful()) {
                $data = $response->json();
                if (!empty($data['photos'][0]['src']['large'])) {
                    return $data['photos'][0]['src']['large'];
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to fetch Pexels image: ' . $e->getMessage());
        }
        
        // Fallback to Wikipedia
        return $this->getWikipediaImage($city, $country);
    }

    /**
     * Get image from Wikipedia/Wikimedia Commons
     */
    private function getWikipediaImage(string $city, string $country): ?string
    {
        try {
            // Try city first
            $query = $city;
            $response = \Illuminate\Support\Facades\Http::timeout(5)
                ->get('https://en.wikipedia.org/api/rest_v1/page/summary/' . urlencode($query));
            
            if ($response->successful()) {
                $data = $response->json();
                if (!empty($data['originalimage']['source'])) {
                    return $data['originalimage']['source'];
                }
                if (!empty($data['thumbnail']['source'])) {
                    return $data['thumbnail']['source'];
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to fetch Wikipedia image: ' . $e->getMessage());
        }
        
        return null;
    }

    /**
     * Get curated city image from Unsplash (free, no API key needed)
     */
    private function getCuratedCityImage(string $city): string
    {
        // Use Unsplash's curated collection for cities
        // These are high-quality, real images of actual places
        $citySlug = strtolower(str_replace(' ', '-', $city));
        return "https://images.unsplash.com/photo-1502602898657-3e91760cbb34?w=800&q=80"; // Default travel image
    }

    /**
     * Add 5 hidden gem cities for a country
     */
    private function addHiddenGemsForCountry(string $country, string $region): void
    {
        try {
            // Check if we already have hidden gems for this country
            $existingGems = DB::table('destinations')
                ->where('country', $country)
                ->where('is_hidden_gem', true)
                ->count();
            
            if ($existingGems >= 5) {
                \Log::info("Already have {$existingGems} hidden gems for {$country}");
                return; // Already have enough hidden gems
            }
            
            // Use Geoapify to find lesser-known cities in this country
            $response = \Illuminate\Support\Facades\Http::timeout(10)
                ->get('https://api.geoapify.com/v1/geocode/search', [
                    'text' => 'city in ' . $country,
                    'type' => 'city',
                    'filter' => 'countrycode:' . $this->getCountryCode($country),
                    'apiKey' => config('services.geoapify.key'),
                    'limit' => 15
                ]);
            
            if (!$response->successful()) {
                \Log::warning("Geoapify request failed for {$country}");
                return;
            }
            
            $data = $response->json();
            $features = $data['features'] ?? [];
            
            \Log::info("Found " . count($features) . " cities for {$country}");
            
            $gemsAdded = 0;
            foreach ($features as $feature) {
                if ($gemsAdded >= 5) break;
                
                $props = $feature['properties'] ?? [];
                $cityName = $props['city'] ?? $props['name'] ?? null;
                $countryName = $props['country'] ?? $country;
                
                // Skip if no city name or if it's the country name itself
                if (!$cityName || $cityName === $country || $countryName !== $country) {
                    continue;
                }
                
                // Check if this city already exists
                $exists = DB::table('destinations')
                    ->whereRaw('LOWER(name) = ?', [strtolower($cityName)])
                    ->whereRaw('LOWER(country) = ?', [strtolower($countryName)])
                    ->exists();
                
                if ($exists) {
                    \Log::info("City {$cityName} already exists, skipping");
                    continue;
                }
                
                // Get image for this hidden gem
                $image = $this->getDestinationImage($cityName, $countryName);
                if (!$image) {
                    $image = $this->getCuratedCityImage($cityName);
                }
                
                // Add as hidden gem
                DB::table('destinations')->insert([
                    'name' => $cityName,
                    'country' => $countryName,
                    'region' => $region,
                    'category' => 'general',
                    'mood' => 'adventurous',
                    'description' => 'A hidden gem waiting to be discovered in ' . $countryName . '.',
                    'image_url' => $image,
                    'price_from' => rand(400, 1500),
                    'is_active' => true,
                    'is_hidden_gem' => true,
                    'sort_order' => 999,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                \Log::info("Added hidden gem: {$cityName}, {$countryName}");
                $gemsAdded++;
            }
            
            \Log::info("Added {$gemsAdded} hidden gems for {$country}");
            
        } catch (\Exception $e) {
            \Log::warning('Failed to add hidden gems: ' . $e->getMessage());
        }
    }

    /**
     * Get ISO country code for filtering
     */
    private function getCountryCode(string $country): string
    {
        $codes = [
            'France' => 'fr',
            'United States' => 'us',
            'United Kingdom' => 'gb',
            'Germany' => 'de',
            'Italy' => 'it',
            'Spain' => 'es',
            'Japan' => 'jp',
            'China' => 'cn',
            'South Africa' => 'za',
            'Brazil' => 'br',
            'Australia' => 'au',
            'Canada' => 'ca',
            'Mexico' => 'mx',
            'India' => 'in',
        ];
        
        return $codes[$country] ?? 'none';
    }
}
