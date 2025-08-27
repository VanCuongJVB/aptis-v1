<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Quiz;

class DashboardController extends Controller
{
    public function index()
    {
        $quizzes = Quiz::published()->orderBy('id','desc')->get();
        return view('student.quizzes.index', compact('quizzes'));
    }

    public function show(Quiz $quiz)
    {
        abort_unless($quiz->is_published, 404);
        $quiz->load(['questions.options']);
        return view('student.quizzes.take', compact('quiz'));
    }
}
