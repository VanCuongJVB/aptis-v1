<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'quiz_id',
        'status',
        'started_at',
        'submitted_at',
        'duration_seconds',
        'total_questions',
        'correct_answers',
        'score_percentage',
        'score_points',
        'device_fingerprint',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'score_percentage' => 'decimal:2',
        'metadata' => 'array',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function answers()
    {
        return $this->hasMany(AttemptAnswer::class);
    }

    // Helper methods
    public function isInProgress()
    {
        return $this->status === 'in_progress';
    }

    public function isSubmitted()
    {
        return $this->status === 'submitted';
    }

    public function calculateScore()
    {
        $totalQuestions = $this->total_questions;
        $correctAnswers = $this->correct_answers;
        
        if ($totalQuestions > 0) {
            $this->score_percentage = ($correctAnswers / $totalQuestions) * 100;
            $this->score_points = $correctAnswers;
        }
        
        return $this->score_percentage;
    }
}
