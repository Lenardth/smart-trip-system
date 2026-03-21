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
        $cols = Schema::getColumnListing('destinations');

        $query = DB::table('destinations');

        if (in_array('is_active', $cols)) {
            $query->where('is_active', true);
        }
        if (in_array('is_hidden_gem', $cols)) {
            $query->where('is_hidden_gem', false);
        }

        if ($request->filled('category') && $request->category !== 'all') {
            if (in_array('category', $cols)) {
                $query->where('category', $request->category);
            }
        }

        if ($request->filled('region') && $request->region !== 'all') {
            if (in_array('region', $cols)) {
                $query->where('region', $request->region);
            }
        }

        if ($request->filled('q')) {
            $search = '%' . $request->q . '%';
            $query->where(function ($q) use ($search, $cols) {
                $q->where('name', 'like', $search);
                if (in_array('country', $cols))     $q->orWhere('country',     'like', $search);
                if (in_array('description', $cols)) $q->orWhere('description', 'like', $search);
            });
        }

        if (in_array('sort_order', $cols)) {
            $query->orderBy('sort_order');
        }

        $rows = $query->get();

        $destinations = $rows->map(function ($row) use ($cols) {
            $row = (array) $row;
            return [
                'id'          => $row['id'] ?? null,
                'name'        => $row['name'] ?? $row['title'] ?? 'Unknown',
                'country'     => $row['country'] ?? null,
                'region'      => $row['region'] ?? null,
                'category'    => $row['category'] ?? 'general',
                'mood'        => $row['mood'] ?? $row['type'] ?? null,
                'price_from'  => $row['price_from'] ?? $row['price'] ?? $row['min_price'] ?? 0,
                'description' => $row['description'] ?? $row['summary'] ?? $row['excerpt'] ?? '',
                'image_url'   => $row['image_url'] ?? $row['image'] ?? $row['photo'] ?? $row['thumbnail'] ?? null,
                'badge'       => $row['badge'] ?? $row['label'] ?? null,
            ];
        });

        return response()->json($destinations);
    }

    public function hiddenGems(): JsonResponse
    {
        $cols  = Schema::getColumnListing('destinations');
        $query = DB::table('destinations');

        if (in_array('is_hidden_gem', $cols)) {
            $query->where('is_hidden_gem', true);
        } elseif (in_array('featured', $cols)) {
            $query->where('featured', true);
        } else {
            $query->orderByRaw('RANDOM()')->limit(6);
        }

        if (in_array('match_score', $cols)) {
            $query->orderByDesc('match_score');
        }

        $rows = $query->take(6)->get();

        $gems = $rows->map(fn($row) => [
            'id'          => $row->id ?? null,
            'name'        => $row->name ?? $row->title ?? 'Unknown',
            'country'     => $row->country ?? null,
            'description' => $row->description ?? $row->summary ?? '',
            'image_url'   => $row->image_url ?? $row->image ?? $row->photo ?? $row->thumbnail ?? null,
            'match_score' => $row->match_score ?? null,
        ]);

        return response()->json($gems);
    }

    public function debug(): JsonResponse
    {
        $cols  = Schema::getColumnListing('destinations');
        $count = DB::table('destinations')->count();
        $sample = DB::table('destinations')->first();

        return response()->json([
            'columns'      => $cols,
            'total_rows'   => $count,
            'sample_row'   => $sample,
        ]);
    }
}
