<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AccessLog extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = ['user_id','event','ip_address','user_agent','meta','created_at'];
    protected $casts = ['meta'=>'array','created_at'=>'datetime'];
    public static function log($userId, $event, array $meta = []): void
    {
        static::create([
            'user_id' => $userId,
            'event' => $event,
            'ip_address' => request()?->ip(),
            'user_agent' => substr(request()?->userAgent() ?? '', 0, 1024),
            'meta' => $meta,
            'created_at' => now(),
        ]);
    }
}
