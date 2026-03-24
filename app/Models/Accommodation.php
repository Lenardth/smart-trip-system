<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Accommodation extends Model
{
    protected $fillable = [
        'name',
        'city',
        'country',
        'style',
        'nightly_rate',
        'currency',
        'rating',
        'description',
        'image_url',
        'is_active',
    ];
}
