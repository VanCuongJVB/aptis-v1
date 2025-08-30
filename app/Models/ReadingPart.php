<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReadingPart extends Model
{
    protected $fillable = [
        'quiz_id',
        'part_number',
        'title',
        'instructions',
        'question_count',
        'time_limit',
        'settings'
    ];

    protected $casts = [
        'settings' => 'array',
        'question_count' => 'integer',
        'time_limit' => 'integer',
        'part_number' => 'integer'
    ];

    // Default settings for each part
    const DEFAULT_SETTINGS = [
        1 => [
            'title' => 'Part 1 – Sentence Completion',
            'instructions' => 'Choose the word that best fits in the gap.',
            'question_count' => 5,
            'options_per_question' => 4,
            'settings' => [
                'show_example' => true,
                'randomize_options' => true
            ]
        ],
        2 => [
            'title' => 'Part 2 – Text Completion',
            'instructions' => 'Read the text and fill in each gap with the best option.',
            'question_count' => 7,
            'options_per_question' => 3,
            'settings' => [
                'show_word_bank' => false,
                'text_word_limit' => 100
            ]
        ],
        3 => [
            'title' => 'Part 3 – Reading for Meaning',
            'instructions' => 'Read the texts and answer the questions by choosing A, B, C or D.',
            'question_count' => 8,
            'passage_count' => 4,
            'settings' => [
                'show_passage_labels' => true
            ]
        ],
        4 => [
            'title' => 'Part 4 – Reading for Purpose',
            'instructions' => 'Put the sentences in the correct order to make a coherent text.',
            'sentence_count' => 7,
            'settings' => [
                'allow_drag_drop' => true,
                'show_sentence_numbers' => true
            ]
        ]
    ];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class, 'part', 'part_number')
                    ->where('quiz_id', $this->quiz_id)
                    ->orderBy('sequence');
    }

    public function getDefaultSettings(): array
    {
        return self::DEFAULT_SETTINGS[$this->part_number] ?? [];
    }

    public function validateQuestionCount(): bool
    {
        $currentCount = $this->questions()->count();
        $requiredCount = $this->question_count;

        return $currentCount === $requiredCount;
    }

    public function validateQuestions(): array
    {
        $errors = [];
        $questions = $this->questions;

        foreach ($questions as $question) {
            $validationResult = $this->validateQuestion($question);
            if (!empty($validationResult)) {
                $errors[] = [
                    'question_id' => $question->id,
                    'errors' => $validationResult
                ];
            }
        }

        return $errors;
    }

    protected function validateQuestion(Question $question): array
    {
        $errors = [];

        switch ($this->part_number) {
            case 1:
                if ($question->options()->count() !== 4) {
                    $errors[] = 'Part 1 questions must have exactly 4 options.';
                }
                if (!$question->passage || strlen($question->passage) < 10) {
                    $errors[] = 'Question must have a valid sentence with a gap.';
                }
                break;

            case 2:
                if ($question->options()->count() < 3) {
                    $errors[] = 'Part 2 questions must have at least 3 options.';
                }
                if (!isset($question->meta['gap_position'])) {
                    $errors[] = 'Gap position must be specified.';
                }
                break;

            case 3:
                if (!isset($question->meta['matching_text_id'])) {
                    $errors[] = 'Question must be linked to a passage.';
                }
                if ($question->options()->count() !== 4) {
                    $errors[] = 'Part 3 questions must have exactly 4 options.';
                }
                break;

            case 4:
                if (!isset($question->meta['correct_position'])) {
                    $errors[] = 'Correct position must be specified.';
                }
                break;
        }

        return $errors;
    }
}
