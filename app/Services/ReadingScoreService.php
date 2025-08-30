<?php

namespace App\Services;

use App\Models\Question;
use App\Models\Attempt;
use App\Models\AttemptItem;

class ReadingScoreService
{
    public function scoreAttempt(Attempt $attempt)
    {
        $totalScore = 0;
        $maxScore = 0;

        foreach ($attempt->items as $item) {
            $score = $this->scoreQuestion($item);
            $totalScore += $score['earned'];
            $maxScore += $score['possible'];

            $item->update([
                'score' => $score['earned'],
                'max_score' => $score['possible']
            ]);
        }

        $attempt->update([
            'score' => $totalScore,
            'max_score' => $maxScore
        ]);

        return [
            'score' => $totalScore,
            'max_score' => $maxScore,
            'percentage' => $maxScore > 0 ? ($totalScore / $maxScore * 100) : 0
        ];
    }

    protected function scoreQuestion(AttemptItem $item)
    {
        $question = $item->question;
        $answers = $item->answers;

        switch ($question->type) {
            case Question::TYPE_READING_SENTENCE_MATCHING:
                return $this->scoreSentenceMatching($question, $answers);

            case Question::TYPE_READING_NOTICE_MATCHING:
                return $this->scoreNoticeMatching($question, $answers);

            case Question::TYPE_READING_LONG_TEXT:
                return $this->scoreLongTextReading($question, $answers);

            case Question::TYPE_READING_GAP_FILLING:
                return $this->scoreGapFilling($question, $answers);

            default:
                return ['earned' => 0, 'possible' => 0];
        }
    }

    protected function scoreSentenceMatching($question, $answers)
    {
        $correctAnswers = $question->options()
            ->where('is_correct', true)
            ->pluck('id')
            ->toArray();

        $earned = count(array_intersect($answers, $correctAnswers));
        $possible = count($correctAnswers);

        return [
            'earned' => $earned,
            'possible' => $possible
        ];
    }

    protected function scoreNoticeMatching($question, $answers)
    {
        $correctMatches = collect($question->meta['correct_matches'] ?? []);
        $earned = 0;
        
        foreach ($answers as $questionIndex => $selectedNotice) {
            if ($correctMatches->get($questionIndex) == $selectedNotice) {
                $earned++;
            }
        }

        return [
            'earned' => $earned,
            'possible' => $correctMatches->count()
        ];
    }

    protected function scoreLongTextReading($question, $answers)
    {
        $correctAnswers = $question->options()
            ->where('is_correct', true)
            ->pluck('id')
            ->toArray();

        $earned = 0;
        foreach ($answers as $answer) {
            if (in_array($answer, $correctAnswers)) {
                $earned++;
            }
        }

        return [
            'earned' => $earned,
            'possible' => count($correctAnswers)
        ];
    }

    protected function scoreGapFilling($question, $answers)
    {
        $correctAnswers = $question->meta['correct_answers'] ?? [];
        $earned = 0;

        foreach ($answers as $index => $answer) {
            if (isset($correctAnswers[$index]) && strtolower($answer) === strtolower($correctAnswers[$index])) {
                $earned++;
            }
        }

        return [
            'earned' => $earned,
            'possible' => count($correctAnswers)
        ];
    }
}
