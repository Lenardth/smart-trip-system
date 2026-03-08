<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunityReply extends Model
{
    protected $fillable = ['topic_id', 'author', 'body'];

    public function topic()
    {
        return $this->belongsTo(CommunityTopic::class);
    }
}
