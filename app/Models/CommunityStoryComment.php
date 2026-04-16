<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunityStoryComment extends Model
{
    protected $fillable = [
        'story_id',
        'user_id',
        'author',
        'body',
    ];

    public function story()
    {
        return $this->belongsTo(CommunityStory::class, 'story_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
