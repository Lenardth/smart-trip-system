<?php

namespace App\Models;

use App\Models\Traits\HasActiveScope;
use App\Models\Traits\HasDisplayOrder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Destination extends Model
{
    use HasFactory, HasActiveScope, HasDisplayOrder;

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
}
