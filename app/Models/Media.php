<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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

    protected $appends = [
        'url',
        'thumbnail',
    ];

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

    public function getThumbnailAttribute(): string
    {
        if ($this->type === 'video') {
            return $this->url;
        }

        $thumbPath = str_replace('.', '_thumb.', $this->file_path);

        if (Storage::disk('public')->exists($thumbPath)) {
            return asset('storage/' . $thumbPath);
        }

        return $this->url;
    }

    public function scopeImages($query)
    {
        return $query->where('type', 'image');
    }

    public function scopeVideos($query)
    {
        return $query->where('type', 'video');
    }

    public function scopeFavorites($query)
    {
        return $query->where('is_favorite', true);
    }

    public function scopeByTrip($query, $tripId)
    {
        return $query->where('trip_id', $tripId);
    }
}
