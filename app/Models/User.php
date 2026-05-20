<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type',
        'agency_name',
        'profile_picture',
        'bio',
        'location',
        'phone',
        'last_login_at',
        'last_login_ip',
        'is_premium',
        'premium_until',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = [
        'profile_picture_url',
        'display_name',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at'     => 'datetime',
            'premium_until'     => 'datetime',
            'is_premium'        => 'boolean',
            'password'          => 'hashed',
        ];
    }

    public function isAgency(): bool
    {
        return $this->user_type === 'agency';
    }

    public function isTraveler(): bool
    {
        return $this->user_type === 'user';
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function getProfilePictureUrlAttribute(): ?string
    {
        if ($this->profile_picture) {
            return asset('storage/' . $this->profile_picture);
        }

        return null;
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->agency_name ?: $this->name;
    }

    public function getAvatarAttribute(): ?string
    {
        return $this->profile_picture_url;
    }
}
