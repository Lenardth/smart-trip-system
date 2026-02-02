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
        'profile_picture',
        'bio',
        'phone',
        'location',
        'preferences',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'preferences' => 'array',
    ];

    // Relationships
    public function flights()
    {
        return $this->hasMany(Flight::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function memories()
    {
        return $this->hasMany(Memory::class);
    }

    public function agencyProfile()
    {
        return $this->hasOne(AgencyProfile::class);
    }

    public function preferences()
    {
        return $this->hasMany(UserPreference::class);
    }

    // Check if user is an agency
    public function isAgency()
    {
        return $this->user_type === 'agency';
    }

    // Check if user is a regular traveler
    public function isTraveler()
    {
        return $this->user_type === 'user';
    }

    // Get user's profile picture URL
    public function getProfilePictureUrlAttribute()
    {
        if ($this->profile_picture) {
            return asset('storage/' . $this->profile_picture);
        }
        return asset('img/default-avatar.png');
    }
}
