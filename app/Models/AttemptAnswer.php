<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttemptAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'attempt_id',
        'question_id',
        'selected_option_id',
        'is_correct',
        'time_spent_seconds',
        'text_answer',
        'metadata',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'metadata' => 'array',
    ];

    // Relationships
    public function attempt()
    {
        return $this->belongsTo(Attempt::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function selectedOption()
    {
        return $this->belongsTo(Option::class, 'selected_option_id');
    }

    // Helper methods
    public function isCorrect()
    {
        return $this->is_correct;
    }
}
