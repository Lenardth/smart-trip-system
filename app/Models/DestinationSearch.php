<?php

namespace App\Models;

use App\Models\Traits\HasSearchScopes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DestinationSearch extends Model
{
    use HasSearchScopes;

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