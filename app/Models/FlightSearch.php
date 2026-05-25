<?php

namespace App\Models;

use App\Models\Traits\HasSearchScopes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlightSearch extends Model
{
    use HasSearchScopes;

    protected $fillable = [
        'user_id',
        'search_hash',
        'request_payload',
        'response_payload',
        'from_query',
        'to_query',
        'from_code',
        'to_code',
        'departure_date',
        'return_date',
        'adults',
        'travel_class',
        'results_count',
        'cache_hit',
        'ip_address',
    ];

    protected $casts = [
        'request_payload' => 'array',
        'response_payload' => 'array',
        'departure_date' => 'date',
        'return_date'    => 'date',
        'adults'         => 'integer',
        'results_count'  => 'integer',
        'cache_hit'      => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
