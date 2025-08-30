<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Quiz, Question, Option};

class QuizSeeder extends Seeder
{
    public function run(): void
    {
        $quiz = Quiz::firstOrCreate(
            ['title' => 'Sample Reading A1', 'skill' => 'reading'],
            [
                'description' => 'Sample Reading Test - A1 Level',
                'is_published' => true,
                'duration_minutes' => 60,
                'allow_seek' => true,
                'listens_allowed' => 0
            ]
        );

        if ($quiz->wasRecentlyCreated || $quiz->questions()->count() === 0) {
            // Part 1: Sentence Completion
            $this->createPart1Questions($quiz);

            // Part 2: Text Completion
            $this->createPart2Questions($quiz);

            // Part 3: Reading for Meaning
            $this->createPart3Questions($quiz);

            // Part 4: Reading for Purpose
            $this->createPart4Questions($quiz);
        }
    }

    private function createPart1Questions(Quiz $quiz)
    {
        $q1 = $quiz->questions()->create([
            'part' => 1,
            'type' => Question::TYPE_READING_SENTENCE_COMPLETION,
            'stem' => 'Choose the word that best fits in the gap.',
            'meta' => [
                'sentence' => 'The new shopping center will _____ next month.',
            ],
            'order' => 1
        ]);

        $q1->options()->createMany([
            ['label' => 'open', 'is_correct' => true, 'order' => 1],
            ['label' => 'opens', 'is_correct' => false, 'order' => 2],
            ['label' => 'opening', 'is_correct' => false, 'order' => 3],
            ['label' => 'opened', 'is_correct' => false, 'order' => 4],
        ]);
    }

    private function createPart2Questions(Quiz $quiz)
    {
        $q2 = $quiz->questions()->create([
            'part' => 2,
            'type' => Question::TYPE_READING_TEXT_COMPLETION,
            'stem' => 'Read the text and choose the best word for each gap.',
            'context_text' => 'The British Museum is one of the (1)_____ famous museums in the world. It (2)_____ in 1753 and was the first national museum to cover all fields of human knowledge. Admission is (3)_____ and visitors can see many fascinating objects from around the globe.',
            'meta' => [
                'gap_positions' => [1, 2, 3],
            ],
            'order' => 2
        ]);

        $q2->options()->createMany([
            ['label' => 'most', 'is_correct' => true, 'order' => 1, 'meta' => ['gap' => 1]],
            ['label' => 'more', 'is_correct' => false, 'order' => 2, 'meta' => ['gap' => 1]],
            ['label' => 'very', 'is_correct' => false, 'order' => 3, 'meta' => ['gap' => 1]],
        ]);
    }

    private function createPart3Questions(Quiz $quiz)
    {
        $q3 = $quiz->questions()->create([
            'part' => 3,
            'type' => Question::TYPE_READING_MATCHING,
            'stem' => 'Read the texts and match each text to its purpose.',
            'context_text' => json_encode([
                'A' => 'For sale: Beautiful house with garden. Perfect for families. Close to schools and shops. Call 555-0123.',
                'B' => 'Warning: Bridge repairs from 1st-14th May. Please use alternative route.',
                'C' => 'Come and try our new summer menu! Fresh salads and cool drinks. Open daily 9am-10pm.',
                'D' => 'Learn English in 3 months! Small groups, experienced teachers. Morning and evening classes available.'
            ]),
            'meta' => [
                'matching_text_id' => 'A'
            ],
            'order' => 3
        ]);

        $q3->options()->createMany([
            ['label' => 'To sell a property', 'is_correct' => true, 'order' => 1],
            ['label' => 'To inform about roadworks', 'is_correct' => false, 'order' => 2],
            ['label' => 'To advertise a restaurant', 'is_correct' => false, 'order' => 3],
            ['label' => 'To promote a language school', 'is_correct' => false, 'order' => 4],
        ]);
    }

    private function createPart4Questions(Quiz $quiz)
    {
        $q4 = $quiz->questions()->create([
            'part' => 4,
            'type' => Question::TYPE_READING_REORDERING,
            'stem' => 'Put these sentences in the correct order to make a story.',
            'meta' => [
                'sentences' => [
                    'First, I woke up early and had breakfast.',
                    'Then, I took the bus to work.',
                    'At lunchtime, I met my friend in the park.',
                    'After work, I went to the gym.',
                    'Finally, I went home and cooked dinner.',
                ],
                'correct_order' => [1, 2, 3, 4, 5]
            ],
            'order' => 4
        ]);
    }
}