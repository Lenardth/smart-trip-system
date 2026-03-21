<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Trip extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'destination',
        'country',
        'mood',
        'budget',
        'duration',
        'companion',
        'region',
        'accommodation',
        'origin',
        'month',
        'estimated_cost',
        'status',
        'notes',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getDurationLabelAttribute(): string
    {
        return [
            'weekend'   => 'Long Weekend',
            'week'      => 'One Week',
            'two_weeks' => 'Two Weeks',
            'month'     => 'One Month+',
            'flexible'  => 'Flexible',
        ][$this->duration] ?? $this->duration ?? '—';
    }

    public function getBudgetLabelAttribute(): string
    {
        return [
            'backpacker' => 'Backpacker',
            'budget'     => 'Budget',
            'mid'        => 'Mid-Range',
            'premium'    => 'Premium',
            'luxury'     => 'Luxury',
        ][$this->budget] ?? $this->budget ?? '—';
    }
}
