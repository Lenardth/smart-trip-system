<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Destination extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'short_description',
        'continent_id',
        'country',
        'city',
        'category',
        'price_per_person',
        'rating',
        'popularity_score',
        'best_season',
        'image_url',
        'tags',
        'is_active',
        'is_featured',
    ];

    protected $casts = [
        'tags' => 'array',
    ];

    public function continent()
    {
        return $this->belongsTo(Continent::class);
    }

    public function savedByUsers()
    {
        return $this->hasMany(SavedDestination::class);
    }

    public function getContinentAttribute()
    {
        return $this->getRelationValue('continent')?->name ?? '';
    }

    public function getImageAttribute()
    {
        return $this->image_url;
    }

    public function getEstimatedCostAttribute()
    {
        return $this->price_per_person;
    }
}
