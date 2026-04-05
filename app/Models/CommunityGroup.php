<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunityGroup extends Model
{
    protected $fillable = [
        'user_id',
        'organizer',
        'name',
        'destination',
        'date',
        'spots_left',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
