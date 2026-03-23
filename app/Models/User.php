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
        'avatar',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
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

    public function getProfilePictureUrlAttribute()
    {
        return $this->profile_picture
            ? asset('storage/' . $this->profile_picture)
            : asset('img/default-avatar.png');
    }

    public function getDisplayNameAttribute()
    {
        return $this->agency_name ?: $this->name;
    }

    public function getAvatarAttribute(): string
    {
        return $this->profile_picture_url;
    }
}
