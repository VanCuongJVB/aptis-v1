<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'label',
        'content',
        'is_correct',
        'order',
        'metadata',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'metadata' => 'array',
    ];

    // Relationships
    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function attemptAnswers()
    {
        return $this->hasMany(AttemptAnswer::class, 'selected_option_id');
    }

    // Helper methods
    public function isCorrect()
    {
        return $this->is_correct;
    }
}
