<?php

namespace App\Services;

use App\Models\TripMood;
use Illuminate\Support\Facades\Cache;

class TripMoodService
{
    public function list()
    {
        return Cache::remember('trip_moods_list', 60, function () {
            return TripMood::orderByDesc('use_count')
                ->orderByDesc('created_at')
                ->select(['id', 'label', 'use_count'])
                ->limit(100)
                ->get();
        });
    }

    public function create(string $label, ?int $userId): array
    {
        $mood = TripMood::findOrCreateByLabel($label, $userId);
        Cache::forget('trip_moods_list');

        return $mood->only(['id', 'label', 'use_count']);
    }

    public function recordUse(TripMood $mood): array
    {
        $mood->increment('use_count');
        Cache::forget('trip_moods_list');

        return ['id' => $mood->id, 'use_count' => $mood->use_count];
    }
}
