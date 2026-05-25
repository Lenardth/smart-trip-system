<?php

namespace App\Models;

use App\Models\Traits\HasSearchScopes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccommodationSearch extends Model
{
    use HasSearchScopes;

    protected $fillable = [
        'user_id',
        'search_hash',
        'request_payload',
        'response_payload',
        'query',
        'style',
        'budget_tier',
        'results_count',
        'cache_hit',
        'ip_address',
    ];

    protected $casts = [
        'request_payload'  => 'array',
        'response_payload' => 'array',
        'results_count'    => 'integer',
        'cache_hit'        => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
