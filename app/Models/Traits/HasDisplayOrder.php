<?php

namespace App\Models\Traits;

trait HasDisplayOrder
{
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('name');
    }
}
