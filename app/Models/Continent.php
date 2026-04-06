<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Continent extends Model
{
    protected $fillable = [
        'name',
        'code',
        'emoji',
    ];

    public function destinations()
    {
        return $this->hasMany(Destination::class);
    }
}