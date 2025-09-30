<?php

namespace App\Services;

use App\Models\Question;

class ListeningQuizService
{
    public function generateFullRandomQuiz()
    {
        // Số lượng câu hỏi mỗi part (có thể điều chỉnh)
        $part1Count = 13;
        $part2Count = 1;
        $part3Count = 1;
        $part4Count = 2;

        $part1 = Question::where('skill', 'listening')->where('part', 1)->inRandomOrder()->limit($part1Count)->get();
        $part2 = Question::where('skill', 'listening')->where('part', 2)->inRandomOrder()->limit($part2Count)->get();
        $part3 = Question::where('skill', 'listening')->where('part', 3)->inRandomOrder()->limit($part3Count)->get();
        $part4 = Question::where('skill', 'listening')->where('part', 4)->inRandomOrder()->limit($part4Count)->get();

        return [
            'part1' => $part1,
            'part2' => $part2,
            'part3' => $part3,
            'part4' => $part4,
        ];
    }
}
