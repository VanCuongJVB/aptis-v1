<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Quiz;
use App\Models\ReadingSet;
use App\Models\Question;

class ListeningSetSeeder extends Seeder
{
    public function run(): void
    {
        // Xóa dữ liệu cũ
        Question::where('skill', 'listening')->delete();
        ReadingSet::where('skill', 'listening')->delete();
        Quiz::where('skill', 'listening')->delete();

        // === QUIZ FULL TEST ===
        $quizFull = Quiz::create([
            'skill' => 'listening',
            'part' => 0,
            'title' => 'Listening Practice - Full Test',
            'description' => 'Full test (seeded)',
            'is_published' => true,
            'duration_minutes' => 40
        ]);

        /**
         * PART 1: Short MCQs
         */
        $set1Full = ReadingSet::create([
            'quiz_id' => $quizFull->id,
            'title' => 'Set 1 (Part 1)',
            'skill' => 'listening',
            'description' => 'Part 1: short multiple choice (~20 items)',
            'is_public' => true,
            'order' => 1
        ]);

        for ($i = 1; $i <= 20; $i++) {
            Question::create([
                'quiz_id' => $quizFull->id,
                'reading_set_id' => $set1Full->id,
                'stem' => "Dummy Part 1 Q{$i}: What is happening in this scenario?",
                'skill' => 'listening',
                'part' => 1,
                'type' => 'listening_mc',
                'order' => $i,
                'metadata' => [
                    'options' => ["Option A{$i}", "Option B{$i}", "Option C{$i}"],
                    'correct_index' => $i % 3
                ]
            ]);
        }

        /**
         * PART 2: Speakers (matching)
         */
        $set2Full = ReadingSet::create([
            'quiz_id' => $quizFull->id,
            'title' => 'Set 2 (Part 2)',
            'skill' => 'listening',
            'description' => 'Part 2: speakers and phrase selection',
            'is_public' => true,
            'order' => 2
        ]);

        for ($i = 1; $i <= 3; $i++) {
            Question::create([
                'quiz_id' => $quizFull->id,
                'reading_set_id' => $set2Full->id,
                'stem' => "Part 2: Four speakers (set {$i}) - choose the correct description.",
                'skill' => 'listening',
                'part' => 2,
                'type' => 'listening_speakers_match',
                'order' => 20 + $i,
                'metadata' => [
                    'speakers' => [
                        ['id' => 'A', 'label' => 'Speaker A'],
                        ['id' => 'B', 'label' => 'Speaker B'],
                        ['id' => 'C', 'label' => 'Speaker C'],
                        ['id' => 'D', 'label' => 'Speaker D'],
                    ],
                    'options' => [
                        "Option 1 for set {$i}",
                        "Option 2 for set {$i}",
                        "Option 3 for set {$i}",
                        "Option 4 for set {$i}",
                        "None of the speakers",
                        "Cannot determine"
                    ],
                    'answers' => [0, 1, 2, 3]
                ]
            ]);
        }

        /**
         * PART 3: Who expresses which opinion?
         */
        $set3Full = ReadingSet::create([
            'quiz_id' => $quizFull->id,
            'title' => 'Set 3 (Part 3)',
            'skill' => 'listening',
            'description' => 'Who expresses which opinion?',
            'is_public' => true,
            'order' => 3
        ]);

        for ($i = 1; $i <= 2; $i++) {
            Question::create([
                'quiz_id' => $quizFull->id,
                'reading_set_id' => $set3Full->id,
                'stem' => "Scenario {$i}: Listen to two people discuss topic {$i}, match opinions to Man/Woman/Both.",
                'skill' => 'listening',
                'part' => 3,
                'type' => 'listening_who_expresses',
                'order' => 23 + $i,
                'metadata' => [
                    'title' => "Who expresses which opinion? (Scenario {$i})",
                    'items' => [
                        "Opinion A{$i}",
                        "Opinion B{$i}",
                        "Opinion C{$i}",
                        "Opinion D{$i}",
                    ],
                    'options' => ['Man', 'Woman', 'Both'],
                    'answers' => [0, 1, 2, 1]
                ]
            ]);
        }

        /**
         * PART 4: Short passages MCQ
         */
        $set4Full = ReadingSet::create([
            'quiz_id' => $quizFull->id,
            'title' => 'Set 4 (Part 4)',
            'skill' => 'listening',
            'description' => 'Part 4: short passages with 2–3 questions each',
            'is_public' => true,
            'order' => 4
        ]);

        for ($i = 1; $i <= 10; $i++) {
            Question::create([
                'quiz_id' => $quizFull->id,
                'reading_set_id' => $set4Full->id,
                'stem' => "Part 4 Passage {$i}: Question about listening detail.",
                'skill' => 'listening',
                'part' => 4,
                'type' => 'listening_mc',
                'order' => 25 + $i,
                'metadata' => [
                    'options' => ["Answer A{$i}", "Answer B{$i}", "Answer C{$i}"],
                    'correct_index' => $i % 3
                ]
            ]);
        }

        /**
         * CLONE to separate quizzes per part
         */
        $parts = [1 => [$set1Full], 2 => [$set2Full], 3 => [$set3Full], 4 => [$set4Full]];

        foreach ($parts as $part => $sets) {
            $quizPart = Quiz::create([
                'skill' => 'listening',
                'part' => $part,
                'title' => "Listening Practice - Part {$part}",
                'description' => "Practice quiz for Part {$part}",
                'is_published' => true,
                'duration_minutes' => 10
            ]);

            foreach ($sets as $setFull) {
                $setClone = ReadingSet::create([
                    'quiz_id' => $quizPart->id,
                    'title' => $setFull->title,
                    'skill' => 'listening',
                    'description' => $setFull->description,
                    'is_public' => true,
                    'order' => $setFull->order
                ]);

                foreach ($setFull->questions as $q) {
                    Question::create([
                        'quiz_id' => $quizPart->id,
                        'reading_set_id' => $setClone->id,
                        'stem' => $q->stem,
                        'skill' => $q->skill,
                        'part' => $quizPart->part,
                        'type' => $q->type,
                        'order' => $q->order,
                        'metadata' => $q->metadata,
                    ]);
                }
            }
        }

        echo "\nListening seed recreated with ~50 questions (parts 1..4).\n";
    }
}
