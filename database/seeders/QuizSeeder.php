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
            ['description' => 'Mẫu bài đọc cơ bản', 'is_published' => true, 'duration_minutes' => 30, 'allow_seek' => false, 'listens_allowed' => 1]
        );

        if ($quiz->wasRecentlyCreated || $quiz->questions()->count() === 0) {
            $q1 = $quiz->questions()->create(['stem' => 'Chọn đáp án đúng: 2 + 2 = ?', 'type' => 'single', 'order' => 1]);
            $q1->options()->createMany([
                ['label' => '3', 'is_correct' => false, 'order' => 1],
                ['label' => '4', 'is_correct' => true, 'order' => 2],
                ['label' => '5', 'is_correct' => false, 'order' => 3],
            ]);

            $q2 = $quiz->questions()->create(['stem' => 'Chọn tất cả số chẵn', 'type' => 'multi', 'order' => 2]);
            $q2->options()->createMany([
                ['label' => '1', 'is_correct' => false, 'order' => 1],
                ['label' => '2', 'is_correct' => true, 'order' => 2],
                ['label' => '3', 'is_correct' => false, 'order' => 3],
                ['label' => '4', 'is_correct' => true, 'order' => 4],
            ]);
        }
    }
}
