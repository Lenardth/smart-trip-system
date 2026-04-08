<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
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
        'last_login_at',
        'last_login_ip',
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

    public function trips()
    {
        return $this->hasMany(Trip::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function savedDestinations()
    {
        return $this->belongsToMany(
            Destination::class,
            'saved_destinations'
        )->withTimestamps();
    }

    public function getProfilePictureUrlAttribute(): string
    {
        if ($this->profile_picture) {
            return asset('storage/' . $this->profile_picture);
        }
        return '';
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->agency_name ?: $this->name;
    }

    // avatar is an alias for profile_picture_url for API consistency
    public function getAvatarAttribute(): string
    {
        return $this->profile_picture_url;
    }

}
