<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
    'title',
    'reading_set_id',
    'stem',
        'explanation',
        'skill',
        'part',
        'type',
        'order',
    'point',
        'audio_path',
        'image_path',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    // Relationships
    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function readingSet()
    {
        return $this->belongsTo(ReadingSet::class, 'reading_set_id');
    }

    public function options()
    {
        return $this->hasMany(Option::class)->orderBy('order');
    }

    public function attemptAnswers()
    {
        return $this->hasMany(AttemptAnswer::class);
    }

    // Helper methods
    public function getCorrectOption()
    {
        return $this->options()->where('is_correct', true)->first();
    }

    public function hasAudio()
    {
        return !empty($this->audio_path);
    }

    public function hasImage()
    {
        return !empty($this->image_path);
    }

    // Scopes
    public function scopeBySkillPart($query, $skill, $part)
    {
        return $query->where('skill', $skill)->where('part', $part);
    }
}