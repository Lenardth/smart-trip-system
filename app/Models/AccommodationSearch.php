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
        'query',
        'style',
        'budget_tier',
        'results_count',
        'ip_address',
    ];

    protected $casts = [
        'results_count' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
