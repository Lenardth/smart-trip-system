<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItineraryDayPlan extends Model
{
    protected $fillable = ['destination_code', 'day', 'title', 'description'];

    protected $casts = ['day' => 'integer'];

    public function destination(): BelongsTo
    {
        return $this->belongsTo(ItineraryDestination::class, 'destination_code', 'code');
    }
}
