<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Memory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'type',
        'file_path',
        'thumbnail_path',
        'metadata',
        'is_public',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_public' => 'boolean',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function frames()
    {
        return $this->hasMany(MemoryFrame::class);
    }

    // Get file URL
    public function getFileUrlAttribute()
    {
        return asset('storage/' . $this->file_path);
    }

    // Get thumbnail URL
    public function getThumbnailUrlAttribute()
    {
        if ($this->thumbnail_path) {
            return asset('storage/' . $this->thumbnail_path);
        }
        return $this->file_url;
    }

    // Check if memory is a video
    public function isVideo()
    {
        return $this->type === 'video';
    }

    // Check if memory is a photo
    public function isPhoto()
    {
        return $this->type === 'photo';
    }
}
