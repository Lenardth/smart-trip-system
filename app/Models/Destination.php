<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Destination extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'country',
        'region',
        'category',
        'mood',
        'price_from',
        'description',
        'image_url',
        'badge',
        'is_hidden_gem',
        'match_score',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_hidden_gem' => 'boolean',
        'is_active'     => 'boolean',
        'price_from'    => 'integer',
        'match_score'   => 'integer',
        'sort_order'    => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeHiddenGems($query)
    {
        return $query->where('is_hidden_gem', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByRegion($query, string $region)
    {
        return $query->where('region', $region);
    }
}
