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

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAgency(): bool
    {
        return $this->user_type === 'agency';
    }

    public function isUser(): bool
    {
        return $this->user_type === 'user';
    }

    public function flights()
    {
        return $this->hasMany(Flight::class, 'agency_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function memories()
    {
        return $this->hasMany(Memory::class);
    }

    public function preferences()
    {
        return $this->hasMany(UserPreference::class);
    }

    public function getProfilePictureUrlAttribute()
    {
        return $this->profile_picture 
            ? asset('storage/' . $this->profile_picture)
            : asset('img/default-avatar.png');
    }

    public function getDisplayNameAttribute()
    {
        return $this->isAgency() && $this->agency_name 
            ? $this->agency_name 
            : $this->name;
    }
}
