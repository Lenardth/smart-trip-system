<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgencyProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'agency_name',
        'business_registration',
        'website',
        'phone',
        'description',
        'social_links',
        'rating',
        'total_reviews',
    ];

    protected $casts = [
        'social_links' => 'array',
        'rating'       => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function updateRating(float $newRating): void
    {
        $total = $this->rating * $this->total_reviews + $newRating;
        $this->total_reviews++;
        $this->rating = $total / $this->total_reviews;
        $this->save();
    }
}