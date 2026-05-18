<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MoodCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'gradient_from',
        'gradient_to',
        'color',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('name');
    }

    /**
     * Returns the CSS background gradient string.
     */
    public function getGradientAttribute(): string
    {
        return "linear-gradient(135deg, {$this->gradient_from}, {$this->gradient_to})";
    }
}
