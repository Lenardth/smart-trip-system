<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripMood extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'label',
        'label_normalized',
        'use_count',
        'created_by',
    ];

    protected $casts = [
        'use_count'  => 'integer',
        'created_by' => 'integer',
    ];

    

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    

    
    public function scopePopular($query)
    {
        return $query->orderByDesc('use_count')->orderByDesc('created_at');
    }

    

    
    public static function normalize(string $label): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/', ' ', $label)));
    }

    
    public static function findOrCreateByLabel(string $rawLabel, ?int $userId = null): static
    {
        $normalized = static::normalize($rawLabel);

        $mood = static::where('label_normalized', $normalized)->first();

        if ($mood) {
            $mood->increment('use_count');
            return $mood->fresh();
        }

        return static::create([
            'label'            => trim($rawLabel),
            'label_normalized' => $normalized,
            'use_count'        => 1,
            'created_by'       => $userId,
        ]);
    }
}