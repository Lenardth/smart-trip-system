<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'destination_id',
        'title',
        'description',
        'start_date',
        'end_date',
        'status',
        'budget',
        'travelers_count',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'budget' => 'decimal:2',
        'travelers_count' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function destination()
    {
        return $this->belongsTo(Destination::class);
    }


    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }


    public function isActive()
    {
        return $this->status === 'active';
    }


    public function isUpcoming()
    {
        return $this->status === 'planned' && $this->start_date > now();
    }


    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePlanned($query)
    {
        return $query->where('status', 'planned');
    }


    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }


    public function getDurationAttribute()
    {
        if (!$this->start_date || !$this->end_date) {
            return 0;
        }
        return $this->start_date->diffInDays($this->end_date);
    }
}
