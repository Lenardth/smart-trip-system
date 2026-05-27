<?php

namespace App\Services;

use App\Models\AccommodationSearch;
use App\Models\Booking;
use App\Models\Trip;

class DashboardService
{
    public function overviewData(int $userId): array
    {
        $stats = $this->statistics($userId);
        $user = auth()->user();
        $hour = now()->hour;

        return [
            'firstName' => explode(' ', $user->name ?? 'Traveler')[0],
            'isNew' => $user ? $user->created_at->diffInDays(now()) < 1 : false,
            'greeting' => $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening'),
            'tripsCount' => $stats['trips'],
            'bookingsCount' => $stats['bookings'],
            'staySearchesCount' => $stats['stay_searches'],
            'activeTab' => request('tab', 'overview'),
        ];
    }

    public function recentActivity(int $userId): array
    {
        $activities = [];
        $limit = config('dashboard.activity.recent_limit', 5);

        Trip::where('user_id', $userId)->latest()->limit($limit)->get()
            ->each(function ($trip) use (&$activities) {
                $activities[] = [
                    'type'  => 'trip',
                    'icon'  => config('dashboard.activity_icons.trip'),
                    'color' => config('dashboard.activity_colors.trip'),
                    'title' => sprintf(config('dashboard.activity_labels.trip'), $trip->destination),
                    'sub'   => trim($trip->budget_label . ($trip->duration_label !== '-' ? ' · ' . $trip->duration_label : '')),
                    'time'  => $trip->created_at->diffForHumans(),
                    'ts'    => $trip->created_at->timestamp,
                    'url'   => '/plan-trip',
                ];
            });

        Booking::where('user_id', $userId)->latest()->limit($limit)->get()
            ->each(function ($booking) use (&$activities) {
                $statusDisplay = config('dashboard.booking_status_display', []);
                $status = $statusDisplay[$booking->status] ?? ucfirst($booking->status);

                $activities[] = [
                    'type'  => 'booking',
                    'icon'  => config('dashboard.activity_icons.booking'),
                    'color' => config('dashboard.activity_colors.booking'),
                    'title' => sprintf(config('dashboard.activity_labels.booking'), $booking->title ?? 'Ref #' . $booking->booking_reference),
                    'sub'   => config('dashboard.money.symbol', '$') . number_format((float) $booking->total_price, config('dashboard.money.decimals', 2)) . ' · ' . $status,
                    'time'  => $booking->created_at->diffForHumans(),
                    'ts'    => $booking->created_at->timestamp,
                    'url'   => '/bookings',
                ];
            });

        AccommodationSearch::where('user_id', $userId)->latest()->limit($limit)->get()
            ->each(function ($search) use (&$activities) {
                $query = $search->query ?: 'Stay search';

                $activities[] = [
                    'type'  => 'search',
                    'icon'  => config('dashboard.activity_icons.search'),
                    'color' => config('dashboard.activity_colors.search'),
                    'title' => sprintf(config('dashboard.activity_labels.search'), $query),
                    'sub'   => ($search->results_count !== null ? $search->results_count . ' results' : 'Search'),
                    'time'  => $search->created_at->diffForHumans(),
                    'ts'    => $search->created_at->timestamp,
                    'url'   => '/accommodations',
                ];
            });

        usort($activities, fn ($a, $b) => $b['ts'] - $a['ts']);

        return array_slice($activities, 0, config('dashboard.activity.display_limit', 10));
    }

    public function statistics(int $userId): array
    {
        $all = Booking::where('user_id', $userId)->get();
        $confirmedStatus = config('booking.statuses.confirmed');
        $pendingStatus = config('booking.statuses.pending');
        $cancelledStatus = config('booking.statuses.cancelled');

        return [
            'trips'         => Trip::where('user_id', $userId)->where('status', 'planned')->count(),
            'bookings'      => $all->whereIn('status', [$confirmedStatus, $pendingStatus])->count(),
            'stay_searches' => AccommodationSearch::where('user_id', $userId)->count(),
            'flights'       => $all->where('type', config('booking.types.flight'))->count(),
            'hotels'        => $all->where('type', config('booking.types.accommodation'))->count(),
            'spent'         => round((float) $all->whereNotIn('status', [$cancelledStatus])->sum('total_price'), 2),
        ];
    }
}
