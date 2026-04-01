<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunityStory extends Model
{
    protected $fillable = ['author', 'title', 'excerpt', 'image_url', 'likes', 'comments', 'published_at'];

    protected $casts = ['published_at' => 'datetime'];
}
