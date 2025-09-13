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
        // Clear existing listening data
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

        // --- Part 1: 13 short MC ---
        $set1Full = ReadingSet::create([
            'quiz_id' => $quizFull->id,
            'title' => 'Set 1 (Part 1)',
            'skill' => 'listening',
            'description' => 'Part 1: short multiple choice (13 items)',
            'is_public' => true,
            'order' => 1
        ]);

        $part1Stems = [
            "A doctor’s secretary calls about a change to an appointment. What is changing?",
            "A man asks where to return a rented DVD.",
            "A tourist asks for directions to the museum.",
            "A customer calls about a delayed delivery.",
            "Someone inquires about a refund policy at a store.",
            "A passenger asks the ticket agent about seat availability.",
            "A caller requests information about gym opening hours.",
            "A student asks the librarian where to find a book.",
            "A listener reports a broken streetlight to the council.",
            "A customer asks how to change their subscription plan.",
            "An employee calls in sick and arranges cover for a shift.",
            "A resident enquires about recycling pickup days.",
            "A guest asks the hotel about breakfast times."
        ];

        for ($i = 1; $i <= 13; $i++) {
            Question::create([
                'quiz_id' => $quizFull->id,
                'reading_set_id' => $set1Full->id,
                'stem' => $part1Stems[$i - 1] ?? "Part 1 example question {$i}",
                'skill' => 'listening',
                'part' => 1,
                'type' => 'listening_mc',
                'order' => $i,
                'metadata' => [
                    'options' => ['Option A', 'Option B', 'Option C'],
                    'correct_index' => 1
                ]
            ]);
        }

        // --- Part 2: speakers ---
        $set2Full = ReadingSet::create([
            'quiz_id' => $quizFull->id,
            'title' => 'Set 2 (Part 2)',
            'skill' => 'listening',
            'description' => 'Part 2: speakers and phrase selection',
            'is_public' => true,
            'order' => 2
        ]);

        Question::create([
            'quiz_id' => $quizFull->id,
            'reading_set_id' => $set2Full->id,
            'stem' => 'Four speakers: select the phrase each speaker says (choose up to 6 phrases).',
            'skill' => 'listening',
            'part' => 2,
            'type' => 'listening_speakers_complete',
            'order' => 14,
            'metadata' => [
                'speakers' => [
                    ['id' => 'A', 'label' => 'Speaker A'],
                    ['id' => 'B', 'label' => 'Speaker B'],
                    ['id' => 'C', 'label' => 'Speaker C'],
                    ['id' => 'D', 'label' => 'Speaker D'],
                ],
                'items' => ['Sentence 1', 'Sentence 2', 'Sentence 3', 'Sentence 4', 'Sentence 5', 'Sentence 6'],
                'options' => ['Phrase 1', 'Phrase 2', 'Phrase 3', 'Phrase 4', 'Phrase 5', 'Phrase 6'],
                // 4 đáp án tương ứng 4 speaker (index trong options)
                'answers' => [0, 1, 2, 3]
            ]
        ]);

        // --- Part 3: who expresses which opinion ---
        $set3Full = ReadingSet::create([
            'quiz_id' => $quizFull->id,
            'title' => 'Set 3 (Part 3)',
            'skill' => 'listening',
            'description' => 'Who expresses which opinion?',
            'is_public' => true,
            'order' => 3
        ]);

        Question::create([
            'quiz_id' => $quizFull->id,
            'reading_set_id' => $set3Full->id,
            'stem' => "Listen to two parents discussing the issue of children's health. Read the opinions below and decide whose opinion matches the statements: the man, the woman, or both. You can listen to the discussion twice.",
            'skill' => 'listening',
            'part' => 3,
            'type' => 'listening_who_expresses',
            'order' => 15,
            'metadata' => [
                'title' => 'Who expresses which opinion?',
                'items' => [
                    'Children need more sleep',
                    'Parents should support sports',
                    'Diet is very important',
                    'Screen time is harmful'
                ],
                'options' => ['Man', 'Woman', 'Both'],
                'answers' => [0, 1, 2, 1]
            ]
        ]);

        // --- Part 4: MCQ short passages ---
        $set4Full = ReadingSet::create([
            'quiz_id' => $quizFull->id,
            'title' => 'Set 4 (Part 4)',
            'skill' => 'listening',
            'description' => 'Part 4: short multiple choice',
            'is_public' => true,
            'order' => 4
        ]);

        Question::create([
            'quiz_id' => $quizFull->id,
            'reading_set_id' => $set4Full->id,
            'stem' => 'Listen to a city planner talk at a press conference about a new transport plan and answer the questions below. Q1: What is his opinion of the plan overall?',
            'skill' => 'listening',
            'part' => 4,
            'type' => 'listening_mc',
            'order' => 16,
            'metadata' => [
                'options' => ['Similar', 'No consultation', 'Not representative'],
                'correct_index' => 1
            ]
        ]);

        Question::create([
            'quiz_id' => $quizFull->id,
            'reading_set_id' => $set4Full->id,
            'stem' => 'Q2: What does he think about the proposed routes?',
            'skill' => 'listening',
            'part' => 4,
            'type' => 'listening_mc',
            'order' => 17,
            'metadata' => [
                'options' => ['Genuine', 'Prepared', 'Embarrassing'],
                'correct_index' => 0
            ]
        ]);

        // === CLONE to quizzes per part ===
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

        echo "\nListening seed cleared and recreated (17 items, parts 1..4).\n";
    }
}
