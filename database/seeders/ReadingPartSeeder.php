<?php

namespace Database\Seeders;

use App\Models\Quiz;
use App\Models\ReadingPart;
use Illuminate\Database\Seeder;

class ReadingPartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all reading quizzes
        $readingQuizzes = Quiz::where('skill', 'reading')->get();

        foreach ($readingQuizzes as $quiz) {
            foreach (ReadingPart::DEFAULT_SETTINGS as $partNumber => $settings) {
                ReadingPart::create([
                    'quiz_id' => $quiz->id,
                    'part_number' => $partNumber,
                    'title' => $settings['title'],
                    'instructions' => $settings['instructions'],
                    'question_count' => $settings['question_count'] ?? 0,
                    'time_limit' => 0, // Will be set by quiz admin
                    'settings' => $settings['settings'] ?? []
                ]);
            }
        }
    }
}
