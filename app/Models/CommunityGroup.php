<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CommunityGroup extends Model
{
    protected $fillable = ['organizer', 'name', 'destination', 'date', 'spots_left', 'status'];
}
