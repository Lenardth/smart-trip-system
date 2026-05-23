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
        $userId       = Auth::id();
        $activities   = [];
        $tripLimit    = config('dashboard.activity.recent_limit', 5);
        $displayLimit = config('dashboard.activity.display_limit', 10);
        $tripIcon     = config('dashboard.activity_icons.trip');
        $tripColor    = config('dashboard.activity_colors.trip');
        $tripLabel    = config('dashboard.activity_labels.trip');

        Trip::where('user_id', $userId)->latest()->limit($tripLimit)->get()
            ->each(function ($t) use (&$activities, $tripIcon, $tripColor, $tripLabel) {
                $activities[] = [
                    'type'  => 'trip',
                    'icon'  => $tripIcon,
                    'color' => $tripColor,
                    'title' => sprintf($tripLabel, $t->destination),
                    'sub'   => trim($t->budget_label.($t->duration_label !== '-' ? ' · '.$t->duration_label : '')),
                    'time'  => $t->created_at->diffForHumans(),
                    'ts'    => $t->created_at->timestamp,
                    'url'   => '/plan-trip',
                ];
            });

        $bookingLimit  = config('dashboard.activity.recent_limit', 5);
        $bookingIcon   = config('dashboard.activity_icons.booking');
        $bookingColor  = config('dashboard.activity_colors.booking');
        $bookingLabel  = config('dashboard.activity_labels.booking');
        $moneySymbol   = config('dashboard.money.symbol', '$');
        $moneyDecimals = config('dashboard.money.decimals', 2);
        $statusDisplay = config('dashboard.booking_status_display', []);

        Booking::where('user_id', $userId)->latest()->limit($bookingLimit)->get()
            ->each(function ($b) use (&$activities, $bookingIcon, $bookingColor, $bookingLabel, $moneySymbol, $moneyDecimals, $statusDisplay) {
                $status = $statusDisplay[$b->status] ?? ucfirst($b->status);
                $activities[] = [
                    'type'  => 'booking',
                    'icon'  => $bookingIcon,
                    'color' => $bookingColor,
                    'title' => sprintf($bookingLabel, $b->title ?? 'Ref #'.$b->booking_reference),
                    'sub'   => $moneySymbol.number_format((float) $b->total_price, $moneyDecimals).' · '.$status,
                    'time'  => $b->created_at->diffForHumans(),
                    'ts'    => $b->created_at->timestamp,
                    'url'   => '/bookings',
                ];
            });

        $searchLimit = config('dashboard.activity.recent_limit', 5);
        $searchIcon  = config('dashboard.activity_icons.search');
        $searchColor = config('dashboard.activity_colors.search');
        $searchLabel = config('dashboard.activity_labels.search');

        AccommodationSearch::where('user_id', $userId)->latest()->limit($searchLimit)->get()
            ->each(function ($s) use (&$activities, $searchIcon, $searchColor, $searchLabel) {
                $q = $s->query ?: 'Stay search';
                $activities[] = [
                    'type'  => 'search',
                    'icon'  => $searchIcon,
                    'color' => $searchColor,
                    'title' => sprintf($searchLabel, $q),
                    'sub'   => ($s->results_count !== null ? $s->results_count.' results' : 'Search'),
                    'time'  => $s->created_at->diffForHumans(),
                    'ts'    => $s->created_at->timestamp,
                    'url'   => '/accommodations',
                ];
            });

        usort($activities, fn ($a, $b) => $b['ts'] - $a['ts']);

        return response()->json(['activities' => array_slice($activities, 0, $displayLimit)]);
    }

    public function statistics(): JsonResponse
    {
        $userId = Auth::id();
        $all    = Booking::where('user_id', $userId)->get();
        $flightType = config('booking.types.flight');
        $accommodationType = config('booking.types.accommodation');
        $confirmedStatus = config('booking.statuses.confirmed');
        $pendingStatus = config('booking.statuses.pending');
        $cancelledStatus = config('booking.statuses.cancelled');

        $trips        = Trip::where('user_id', $userId)->where('status', 'planned')->count();
        $bookings     = $all->whereIn('status', [$confirmedStatus, $pendingStatus])->count();
        $staySearches = AccommodationSearch::where('user_id', $userId)->count();
        $flights      = $all->where('type', $flightType)->count();
        $hotels       = $all->where('type', $accommodationType)->count();
        $spent        = $all->whereNotIn('status', [$cancelledStatus])->sum('total_price');

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
