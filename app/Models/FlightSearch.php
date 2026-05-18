<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlightSearch extends Model
{
    protected $fillable = [
        'user_id',
        'from_query',
        'to_query',
        'from_code',
        'to_code',
        'departure_date',
        'return_date',
        'adults',
        'travel_class',
        'results_count',
        'ip_address',
    ];

    protected $casts = [
        'departure_date' => 'date',
        'return_date'    => 'date',
        'adults'         => 'integer',
        'results_count'  => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
