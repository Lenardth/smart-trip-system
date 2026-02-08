<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Continent extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'icon_class',
        'display_order'
    ];

    public function destinations(): HasMany
    {
        return $this->hasMany(Destination::class);
    }

    public function getIconClassAttribute(): string
    {
        $icons = [
            'Asia' => 'fas fa-globe-asia',
            'Europe' => 'fas fa-globe-europe',
            'Americas' => 'fas fa-globe-americas',
            'Africa' => 'fas fa-globe-africa',
            'Oceania' => 'fas fa-globe-asia',
        ];

        return $icons[$this->name] ?? 'fas fa-globe';
    }
}
