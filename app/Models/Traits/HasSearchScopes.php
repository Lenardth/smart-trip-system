<?php

namespace App\Models\Traits;

trait HasSearchScopes
{
    /**
     * Scope for recent records
     */
    public function scopeRecent($query, $limit = 20)
    {
        return $query->latest()->limit($limit);
    }

    /**
     * Scope for records by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
