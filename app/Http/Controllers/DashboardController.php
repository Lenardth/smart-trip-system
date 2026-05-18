<?php

namespace App\Http\Controllers;

use App\Models\AccommodationSearch;
use App\Models\Booking;
use App\Models\Trip;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        return view('dashboard.index', compact('user'));
    }

    public function recentActivity(): JsonResponse
    {
        $userId      = Auth::id();
        $activities = [];

        Trip::where('user_id', $userId)->latest()->limit(5)->get()
            ->each(function ($t) use (&$activities) {
                $activities[] = [
                    'type'  => 'trip',
                    'icon'  => 'fa-route',
                    'color' => '#9c27b0',
                    'title' => 'Trip planned to '.$t->destination,
                    'sub'   => trim($t->budget_label.($t->duration_label !== '-' ? ' · '.$t->duration_label : '')),
                    'time'  => $t->created_at->diffForHumans(),
                    'ts'    => $t->created_at->timestamp,
                    'url'   => '/plan-trip',
                ];
            });

        Booking::where('user_id', $userId)->latest()->limit(5)->get()
            ->each(function ($b) use (&$activities) {
                $activities[] = [
                    'type'  => 'booking',
                    'icon'  => 'fa-ticket-alt',
                    'color' => '#4caf50',
                    'title' => 'Booking: '.($b->title ?? 'Ref #'.$b->booking_reference),
                    'sub'   => '$'.number_format((float) $b->total_price, 2).' · '.ucfirst($b->status),
                    'time'  => $b->created_at->diffForHumans(),
                    'ts'    => $b->created_at->timestamp,
                    'url'   => '/bookings',
                ];
            });

        AccommodationSearch::where('user_id', $userId)->latest()->limit(5)->get()
            ->each(function ($s) use (&$activities) {
                $q = $s->query ?: 'Stay search';
                $activities[] = [
                    'type'  => 'search',
                    'icon'  => 'fa-hotel',
                    'color' => '#2196f3',
                    'title' => 'Accommodation search: '.$q,
                    'sub'   => ($s->results_count !== null ? $s->results_count.' results' : 'Search'),
                    'time'  => $s->created_at->diffForHumans(),
                    'ts'    => $s->created_at->timestamp,
                    'url'   => '/accommodations',
                ];
            });

        usort($activities, fn ($a, $b) => $b['ts'] - $a['ts']);

        return response()->json(['activities' => array_slice($activities, 0, 10)]);
    }

    public function statistics(): JsonResponse
    {
        $userId = Auth::id();
        $all    = Booking::where('user_id', $userId)->get();

        $trips        = Trip::where('user_id', $userId)->where('status', 'planned')->count();
        $bookings     = $all->whereIn('status', ['confirmed', 'pending'])->count();
        $staySearches = AccommodationSearch::where('user_id', $userId)->count();
        $flights      = $all->where('type', 'flights')->count();
        $hotels       = $all->where('type', 'hotels')->count();
        $spent        = $all->whereNotIn('status', ['cancelled'])->sum('total_price');

        return response()->json([
            'trips'         => $trips,
            'bookings'      => $bookings,
            'stay_searches' => $staySearches,
            'flights'       => $flights,
            'hotels'        => $hotels,
            'spent'         => round((float) $spent, 2),
        ]);
    }
}
