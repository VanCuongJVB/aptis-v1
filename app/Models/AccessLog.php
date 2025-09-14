<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AccessLog extends Model
{
    use HasFactory;

    protected $table = 'access_logs';

    protected $fillable = [
        'user_id',
        'event',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    /**
     * Create a simple access log entry.
     * @param int|null $userId
     * @param string $event
     * @param array|null $meta
     * @return static
     */
    public static function log($userId, string $event, $meta = null)
    {
        return static::create([
            'user_id' => $userId,
            'event' => $event,
            'meta' => $meta ?: null,
        ]);
    }
}
