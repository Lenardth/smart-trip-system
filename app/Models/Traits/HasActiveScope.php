<?php

namespace App\Models\Traits;

trait HasActiveScope
{
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
