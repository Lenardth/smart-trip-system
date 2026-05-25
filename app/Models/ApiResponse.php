<?php

namespace App\Models;

use Closure;
use Illuminate\Database\Eloquent\Model;

class ApiResponse extends Model
{
    protected $fillable = [
        'provider',
        'endpoint',
        'cache_key',
        'params',
        'payload',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'params'     => 'array',
        'payload'    => 'array',
        'expires_at' => 'datetime',
    ];

    public static function key(string $provider, string $endpoint, array $params = []): string
    {
        ksort($params);

        return hash('sha256', $provider . '|' . $endpoint . '|' . json_encode($params));
    }

    public static function remember(
        string $provider,
        string $endpoint,
        array $params,
        \DateTimeInterface $expiresAt,
        Closure $callback
    ): mixed {
        $key = self::key($provider, $endpoint, $params);

        $cached = self::where('cache_key', $key)
            ->where('expires_at', '>', now())
            ->first();

        if ($cached) {
            return $cached->payload;
        }

        $payload = $callback();

        self::updateOrCreate(
            ['cache_key' => $key],
            [
                'provider'   => $provider,
                'endpoint'   => $endpoint,
                'params'     => $params,
                'payload'    => $payload,
                'status'     => 'ok',
                'expires_at' => $expiresAt,
            ]
        );

        return $payload;
    }
}
