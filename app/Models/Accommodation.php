<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Accommodation extends Model
{
    protected $fillable = [
        'geoapify_id',
        'name',
        'city',
        'country',
        'style',
        'budget_tier',
        'nightly_rate',
        'rating',
        'lat',
        'lng',
        'image_url',
        'is_active',
    ];

    protected $casts = [
        'nightly_rate' => 'float',
        'rating'       => 'integer',
        'lat'          => 'float',
        'lng'          => 'float',
        'is_active'    => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByStyle($query, string $style)
    {
        return $query->where('style', $style);
    }

    public function scopeByBudget($query, string $tier)
    {
        return $query->where('budget_tier', $tier);
    }

    public function scopeByCity($query, string $city)
    {
        return $query->where('city', 'like', "%{$city}%");
    }
}
