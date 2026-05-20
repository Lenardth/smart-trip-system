<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'type',
        'value',
        'min_order',
        'max_discount',
        'uses_total',
        'uses_limit',
        'uses_per_user',
        'is_active',
        'expires_at',
        'description',
    ];

    protected $casts = [
        'value'         => 'decimal:2',
        'min_order'     => 'decimal:2',
        'max_discount'  => 'decimal:2',
        'uses_total'    => 'integer',
        'uses_limit'    => 'integer',
        'uses_per_user' => 'integer',
        'is_active'     => 'boolean',
        'expires_at'    => 'datetime',
    ];

    public function uses(): HasMany
    {
        return $this->hasMany(CouponUse::class);
    }

    /**
     * Returns [valid, errorMessage|null].
     *
     * @return array{bool, string|null}
     */
    public function isValid(float $orderTotal, int $userId): array
    {
        if (!$this->is_active) {
            return [false, 'This coupon is inactive.'];
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return [false, 'This coupon has expired.'];
        }

        if ($this->uses_limit !== null && $this->uses_total >= $this->uses_limit) {
            return [false, 'This coupon has reached its usage limit.'];
        }

        if ($orderTotal < (float) $this->min_order) {
            return [false, 'Minimum order of $' . number_format((float) $this->min_order, 2) . ' required.'];
        }

        if ($this->uses_per_user !== null) {
            $userUses = $this->uses()->where('user_id', $userId)->count();
            if ($userUses >= $this->uses_per_user) {
                return [false, 'You have already used this coupon.'];
            }
        }

        return [true, null];
    }

    public function calculateDiscount(float $subtotal): float
    {
        $discount = $this->type === 'percent'
            ? $subtotal * ((float) $this->value / 100)
            : (float) $this->value;

        if ($this->max_discount) {
            $discount = min($discount, (float) $this->max_discount);
        }

        return round(min($discount, $subtotal), 2);
    }

    public static function generateCode(string $prefix = 'SB'): string
    {
        return strtoupper($prefix . Str::random(6));
    }
}
