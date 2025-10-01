<?php

namespace App\Http\Controllers\Reading;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Question;

class FullRandomController extends Controller
{
    public function index()
    {
        // Số lượng câu hỏi mỗi part (có thể điều chỉnh)
        $part1Count = 1;
        $part2Count = 2;
        $part3Count = 1;
        $part4Count = 1;

        $part1 = Question::where('skill', 'reading')->where('part', 1)->inRandomOrder()->limit($part1Count)->get();
        $part2 = Question::where('skill', 'reading')->where('part', 2)->inRandomOrder()->limit($part2Count)->get();
        $part3 = Question::where('skill', 'reading')->where('part', 3)->inRandomOrder()->limit($part3Count)->get();
        $part4 = Question::where('skill', 'reading')->where('part', 4)->inRandomOrder()->limit($part4Count)->get();

        return view('student.reading.full_random', compact('part1', 'part2', 'part3', 'part4'));
    }
}
