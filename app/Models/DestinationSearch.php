<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DestinationSearch extends Model
{
    protected $fillable = [
        'user_id',
        'query',
        'resolved_query',
        'region_code',
        'mood',
        'results_count',
        'ip_address',
        'source',
    ];

    protected $casts = [
        'results_count' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for recent searches
     */
    public function scopeRecent($query, $limit = 20)
    {
        return $query->latest()->limit($limit);
    }

    /**
     * Scope for searches by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Check if a similar search was already logged today
     */
    public static function alreadyLoggedToday($userId, $query, $regionCode = null, $mood = null): bool
    {
        return self::where('user_id', $userId)
            ->where('query', $query)
            ->when($regionCode, fn($q) => $q->where('region_code', $regionCode))
            ->when($mood, fn($q) => $q->where('mood', $mood))
            ->whereDate('created_at', now()->toDateString())
            ->exists();
    }
}