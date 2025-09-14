<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email', 
        'password',
        'role',
        'is_active',
    'access_expires_at',
    'access_starts_at',
    'access_ends_at',
    'last_access_at',
        'active_devices',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    'access_expires_at' => 'datetime',
    'access_starts_at' => 'datetime',
    'access_ends_at' => 'datetime',
    'last_access_at' => 'datetime',
        'active_devices' => 'array',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function attempts()
    {
        return $this->hasMany(Attempt::class);
    }

    public function sessions()
    {
        return $this->hasMany(UserSession::class);
    }

    // Helper methods
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isStudent()
    {
        return $this->role === 'student';
    }
    
    // Accessors & Mutators
    public function getIsAdminAttribute()
    {
        return $this->role === 'admin';
    }
    
    public function setIsAdminAttribute($value)
    {
        $this->attributes['role'] = $value ? 'admin' : 'student';
    }

    public function hasValidAccess()
    {
        return $this->is_active && 
               ($this->access_expires_at === null || $this->access_expires_at->isFuture());
    }

    public function getActiveDeviceCount()
    {
        return $this->sessions()->where('is_active', true)->count();
    }

    public function canAddDevice()
    {
        return $this->getActiveDeviceCount() < 2;
    }
}
