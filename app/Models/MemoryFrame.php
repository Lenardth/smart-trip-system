<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemoryFrame extends Model
{
    use HasFactory;

    protected $fillable = [
        'memory_id',
        'user_id',
        'frame_type',
        'frame_settings',
    ];

    protected $casts = [
        'frame_settings' => 'array',
    ];

    public function memory()
    {
        return $this->belongsTo(Memory::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getFrameConfig()
    {
        $frames = [
            'polaroid' => [
                'border' => '10px solid white',
                'shadow' => '0 4px 8px rgba(0,0,0,0.1)',
                'padding' => '15px',
                'background' => 'white',
            ],
            'modern' => [
                'border' => '2px solid #3b1f2b',
                'shadow' => '0 2px 10px rgba(59,31,43,0.1)',
                'padding' => '10px',
                'background' => 'transparent',
            ],
            'vintage' => [
                'border' => '8px solid #f5e6d3',
                'shadow' => 'inset 0 0 20px rgba(0,0,0,0.1)',
                'padding' => '5px',
                'background' => '#f5e6d3',
            ],
        ];

        return $frames[$this->frame_type] ?? $frames['modern'];
    }
}