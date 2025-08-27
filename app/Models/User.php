<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name','email','is_admin','is_active','access_starts_at','access_ends_at',
    ];

    protected $hidden = ['password','remember_token'];

    protected $casts = [
        'is_admin' => 'boolean',
        'is_active' => 'boolean',
        'email_verified_at' => 'datetime',
        'access_starts_at' => 'datetime',
        'access_ends_at' => 'datetime',
    ];

    public function attempts(){ return $this->hasMany(Attempt::class); }
    public function sessions(){ return $this->hasMany(UserSession::class); }
}
