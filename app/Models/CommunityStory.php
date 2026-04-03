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
        'likes',
        'comments',
        'published_at',
    ];

    protected $casts = ['published_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
