<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunityReply extends Model
{
    protected $fillable = [
        'topic_id',
        'user_id',
        'author',
        'body',
    ];

    public function topic(): BelongsTo
    {
        return $this->belongsTo(CommunityTopic::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}