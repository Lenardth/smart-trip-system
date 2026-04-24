<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunityStory extends Model
{
    protected $fillable = [
        'user_id',
        'author',
        'title',
        'excerpt',
        'image_url',
        'media_type',
        'video_url',
        'thumbnail_url',
        'duration',
        'likes',
        'comments',
        'views',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'duration' => 'integer',
        'likes' => 'integer',
        'comments' => 'integer',
        'views' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(CommunityStoryComment::class, 'story_id');
    }

    public function likes()
    {
        return $this->morphMany(CommunityLike::class, 'likeable');
    }

    public function isLikedBy($userId)
    {
        return $this->likes()->where('user_id', $userId)->exists();
    }

    public function isVideo()
    {
        return $this->media_type === 'video';
    }

    public function getMediaUrl()
    {
        return $this->isVideo() ? $this->video_url : $this->image_url;
    }

    public function getThumbnail()
    {
        return $this->isVideo() ? $this->thumbnail_url : $this->image_url;
    }
}