<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CommunityTopic extends Model
{
    protected $fillable = ['author', 'title', 'tags', 'body', 'replies'];
}
