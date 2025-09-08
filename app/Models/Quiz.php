<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'skill',
        'part',
        'is_published',
        'duration_minutes',
        'show_explanation',
        'metadata',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'show_explanation' => 'boolean',
        'metadata' => 'array',
    ];

    // Relationships
    public function questions()
    {
        return $this->hasMany(Question::class)->orderBy('order');
    }

    public function sets()
    {
        return $this->hasMany(\App\Models\ReadingSet::class)->orderBy('order');
    }

    public function attempts()
    {
        return $this->hasMany(Attempt::class);
    }

    // Helper methods
    public function isPublished()
    {
        return $this->is_published;
    }

    public function getSkillPartTitle()
    {
        return ucfirst($this->skill) . ' Part ' . $this->part;
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeBySkill($query, $skill)
    {
        return $query->where('skill', $skill);
    }

    public function scopeByPart($query, $part)
    {
        return $query->where('part', $part);
    }
}