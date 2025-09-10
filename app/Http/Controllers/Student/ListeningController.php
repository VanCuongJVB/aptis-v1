<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ListeningController extends Controller
{
    /**
     * Show listening dashboard (quizzes + recent attempts filtered)
     */
    public function dashboard()
    {
        $quizzes = \App\Models\Quiz::published()->where('skill', 'listening')->orderBy('id', 'desc')->get();
        $recentAttempts = \App\Models\Attempt::where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->whereHas('quiz', function($q) { $q->where('skill','listening'); })
            ->with('quiz')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('student.listening.dashboard', compact('quizzes', 'recentAttempts'));
    }
}
