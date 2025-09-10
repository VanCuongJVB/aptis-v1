<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReadingController extends Controller
{
    /**
     * Show reading dashboard (quizzes + recent attempts filtered)
     */
    public function dashboard()
    {
        $quizzes = \App\Models\Quiz::published()->where('skill', 'reading')->orderBy('id', 'desc')->get();
        $recentAttempts = \App\Models\Attempt::where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->whereHas('quiz', function($q) { $q->where('skill','reading'); })
            ->with('quiz')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('student.reading.dashboard', compact('quizzes', 'recentAttempts'));
    }
}
