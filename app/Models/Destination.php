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
        'country_code',
        'region',
        'description',
        'image_url',
        'price_from',
        'tags',
        'source',
        'source_id',
        'lat',
        'lng',
        'raw_data',
        'is_featured',
        'is_editors_choice',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'tags' => 'array',
        'price_from' => 'float',
        'lat' => 'float',
        'lng' => 'float',
        'raw_data' => 'array',
        'is_featured' => 'boolean',
        'is_editors_choice' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Scope for active destinations
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for featured destinations
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope for editor's choice
     */
    public function scopeEditorsChoice($query)
    {
        return $query->where('is_editors_choice', true);
    }

    /**
     * Scope for ordered display
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('name');
    }
}
