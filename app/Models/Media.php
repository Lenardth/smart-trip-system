<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'trip_id',
        'title',
        'description',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'type',
        'location',
        'is_favorite',
    ];

    protected $casts = [
        'is_favorite' => 'boolean',
        'file_size' => 'integer',
    ];

    protected $appends = ['url'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }
}
