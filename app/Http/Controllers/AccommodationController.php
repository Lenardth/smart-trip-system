<?php

namespace App\Http\Controllers;

use App\Models\Accommodation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccommodationController extends Controller
{
    public function index()
    {
        return view('accommodations.index');
    }

    public function list(Request $request): JsonResponse
    {
        $query = Accommodation::query()->where('is_active', true);

        if ($request->filled('style') && $request->string('style')->value() !== 'any') {
            $query->where('style', $request->string('style')->value());
        }

        if ($request->filled('q')) {
            $q = $request->string('q')->value();
            $query->where(function ($inner) use ($q) {
                $inner->where('name', 'like', "%{$q}%")
                    ->orWhere('city', 'like', "%{$q}%")
                    ->orWhere('country', 'like', "%{$q}%");
            });
        }

        return response()->json([
            'accommodations' => $query->orderByDesc('rating')->orderBy('nightly_rate')->limit(100)->get(),
        ]);
    }
}
