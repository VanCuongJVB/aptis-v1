<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Quiz;
use App\Models\ReadingSet;

class ReadingSetsSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure 4 quizzes for reading parts exist (create if not)
        for ($part = 1; $part <= 4; $part++) {
            $quiz = Quiz::firstOrCreate(
                ['skill' => 'reading', 'part' => $part],
                ['title' => "Reading Part {$part} - Default", 'description' => "Seeded quiz for Part {$part}", 'is_published' => false, 'duration_minutes' => 15]
            );

            // Create 2 sample sets per quiz
            for ($s = 1; $s <= 2; $s++) {
                ReadingSet::firstOrCreate([
                    'quiz_id' => $quiz->id,
                    'title' => "Set {$s} for Part {$part}"
                ], [
                    'description' => "Auto seeded set {$s} for Part {$part}",
                    'is_public' => false,
                    'question_limit' => 5,
                    'order' => $s
                ]);
            }
        }

        // Optional: create a full quiz record (part 0)
        Quiz::firstOrCreate(['skill' => 'reading', 'part' => 0], ['title' => 'Reading Full Test', 'description' => 'Full reading test (composite)', 'is_published' => false, 'duration_minutes' => 60]);
    }
}
