<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItineraryDestination extends Model
{
    protected $fillable = ['code', 'label', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function dayPlans(): HasMany
    {
        return $this->hasMany(ItineraryDayPlan::class, 'destination_code', 'code')
                    ->orderBy('day');
    }

    public static function resolveLabel(string $code): string
    {
        return static::where('code', $code)->value('label') ?? ucfirst($code);
    }

    public static function allCodes(): array
    {
        return static::where('is_active', true)->pluck('label', 'code')->toArray();
    }
}
