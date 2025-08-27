<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserSession extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = ['user_id','session_id','device_fingerprint','user_agent','ip_address','last_activity_at','revoked_at'];
    protected $casts = ['last_activity_at'=>'datetime','revoked_at'=>'datetime'];
    public function user(){ return $this->belongsTo(User::class); }
}
