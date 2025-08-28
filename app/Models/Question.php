<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'order',
        'part',
        'type',
        'stem',
        'explanation',
        'meta',
        'audio_url'
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }
    public function options()
    {
        return $this->hasMany(Option::class)->orderBy('id');
    }

    // Scopes
    public function scopePart($q, int $part)
    {
        return $q->where('part', $part);
    }

    // Helpers
    public function isType(string $t): bool
    {
        return $this->type === $t;
    }
}
