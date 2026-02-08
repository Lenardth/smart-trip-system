<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Destination extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'short_description',
        'continent_id',
        'country',
        'city',
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
        'price_per_person' => 'decimal:2',
        'rating' => 'decimal:1',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'popularity_score' => 'integer',
    ];

    public function continent(): BelongsTo
    {
        return $this->belongsTo(Continent::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'saved_destinations'
        )->withTimestamps();
    }

    public function getBadgeAttribute(): ?string
    {
        if ($this->popularity_score >= 95) return 'Top Rated';
        if ($this->is_featured) return 'Popular';
        if (in_array('romantic', $this->tags ?? [])) return 'Romantic';
        if (in_array('eco', $this->tags ?? [])) return 'Eco';

        return null;
    }

    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format($this->price_per_person, 0);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByContinent($query, $continentId)
    {
        if ($continentId) {
            return $query->where('continent_id', $continentId);
        }

        return $query;
    }
}
