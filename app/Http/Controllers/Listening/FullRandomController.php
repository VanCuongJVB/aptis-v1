<?php

namespace App\Http\Controllers\Listening;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ListeningQuizService;

class FullRandomController extends Controller
{
    public function index(ListeningQuizService $service)
    {
        $quiz = $service->generateFullRandomQuiz();
        return view('student.listening.full_random', $quiz);
    }
}
