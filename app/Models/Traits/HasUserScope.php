<?php

namespace App\Models\Traits;

trait HasUserScope
{
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
