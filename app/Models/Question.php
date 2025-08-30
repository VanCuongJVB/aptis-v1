<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Question extends Model
{
    use HasFactory;

    const TYPE_SINGLE = 'single';
    const TYPE_MULTI = 'multi';
    
    // Reading Test Types
    const TYPE_READING_SENTENCE_COMPLETION = 'reading_sentence_completion';  // Part 1
    const TYPE_READING_TEXT_COMPLETION = 'reading_text_completion';         // Part 2
    const TYPE_READING_MATCHING = 'reading_matching';                      // Part 3
    const TYPE_READING_REORDERING = 'reading_reordering';                 // Part 4

    // Reading Part Types as Array
    const READING_TYPES = [
        1 => self::TYPE_READING_SENTENCE_COMPLETION,
        2 => self::TYPE_READING_TEXT_COMPLETION,
        3 => self::TYPE_READING_MATCHING,
        4 => self::TYPE_READING_REORDERING
    ];

    // Part Labels
    const READING_PART_LABELS = [
        1 => 'Part 1 – Sentence Completion',
        2 => 'Part 2 – Text Completion',
        3 => 'Part 3 – Reading for Meaning',
        4 => 'Part 4 – Reading for Purpose'
    ];

    protected $fillable = [
        'quiz_id',
        'order',
        'part',
        'type',
        'stem',
        'explanation',
        'meta',
        'context_text',
        'audio_url'
    ];

    protected $casts = [
        'meta' => 'array',
    ];
    
    // Reading specific methods
    public function isReadingQuestion(): bool
    {
        return in_array($this->type, array_values(self::READING_TYPES));
    }

    public function getReadingPartLabel(): string
    {
        return self::READING_PART_LABELS[$this->part] ?? 'Unknown Part';
    }

    public function getReadingType(): ?string
    {
        return self::READING_TYPES[$this->part] ?? null;
    }

    // Part 1 Methods
    public function getSentenceWithGap(): ?string
    {
        return $this->meta['sentence'] ?? null;
    }

    public function getChoices(): array
    {
        return $this->meta['choices'] ?? [];
    }

    // Part 2 Methods
    public function getShortText(): ?string
    {
        return $this->meta['text'] ?? null;
    }

    public function getGaps(): array
    {
        return $this->meta['gaps'] ?? [];
    }

    // Part 3 Methods
    public function getPeople(): array
    {
        return $this->meta['people'] ?? [];
    }

    public function getMatchingQuestions(): array
    {
        return $this->meta['questions'] ?? [];
    }

    // Part 4 Methods
    public function getDisorderedSentences(): array
    {
        return $this->meta['sentences'] ?? [];
    }

    public function getCorrectOrder(): array
    {
        return $this->meta['correct_order'] ?? [];
    }

    // Helper methods for validation
    public function validatePart1(): bool
    {
        return !empty($this->getSentenceWithGap()) && 
               count($this->getChoices()) === 4;
    }

    public function validatePart2(): bool
    {
        return !empty($this->getShortText()) && 
               count($this->getGaps()) >= 5 &&
               count($this->getGaps()) <= 7;
    }

    public function validatePart3(): bool
    {
        return count($this->getPeople()) >= 4 &&
               count($this->getMatchingQuestions()) >= 7 &&
               count($this->getMatchingQuestions()) <= 8;
    }

    public function validatePart4(): bool
    {
        return count($this->getDisorderedSentences()) >= 6 &&
               count($this->getDisorderedSentences()) <= 7 &&
               count($this->getDisorderedSentences()) === count($this->getCorrectOrder());
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function readingPart()
    {
        return $this->belongsTo(ReadingPart::class, 'part', 'part_number')
                    ->where('quiz_id', $this->quiz_id);
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
