<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class DiscoverController extends Controller
{
    public function index()
    {
        return view('discover.index');
    }

    public function destinations(Request $request): JsonResponse
    {
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
        
        $destinations = array_map(function ($row) {
            return [
                'id'           => $row['id'] ?? null,
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
            ];
        }, $rows);

        return response()->json($destinations);
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
        
        $gems = array_map(function($row) {
            return [
                'id'          => $row['id'] ?? null,
                'name'        => $row['name'] ?? 'Unknown',
                'country'     => $row['country'] ?? null,
                'description' => $row['description'] ?? '',
                'image_url'   => $row['image_url'] ?? null,
                'match_score' => $row['match_score'] ?? null,
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
}
