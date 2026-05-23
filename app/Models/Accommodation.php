<?php

namespace App\Models;

use App\Models\Traits\HasActiveScope;
use Illuminate\Database\Eloquent\Model;

class Accommodation extends Model
{
    use HasActiveScope;

    protected $fillable = [
        'geoapify_id',
        'name',
        'city',
        'country',
        'style',
        'budget_tier',
        'nightly_rate',
        'rating',
        'review_count',
        'amenities',
        'description',
        'lat',
        'lng',
        'image_url',
        'is_active',
    ];

    protected $casts = [
        'nightly_rate' => 'float',
        'rating'       => 'float',
        'review_count' => 'integer',
        'amenities'    => 'array',
        'lat'          => 'float',
        'lng'          => 'float',
        'is_active'    => 'boolean',
    ];

    public function scopeByCity($query, string $city)
    {
        return $query->where('city', 'like', "%{$city}%");
    }
}
