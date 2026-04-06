<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'preference_key',
        'preference_value',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function getValue($userId, $key, $default = null)
    {
        $pref = self::where('user_id', $userId)
            ->where('preference_key', $key)
            ->first();

        return $pref ? $pref->preference_value : $default;
    }

    public static function setValue($userId, $key, $value)
    {
        return self::updateOrCreate(
            ['user_id' => $userId, 'preference_key' => $key],
            ['preference_value' => $value]
        );
    }
}