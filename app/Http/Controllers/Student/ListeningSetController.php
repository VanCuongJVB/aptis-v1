<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ReadingSet;
use Illuminate\Http\Request;

class ListeningSetController extends Controller
{
    /**
     * Display a listing of listening sets.
     */
    public function index(\Illuminate\Http\Request $request)
    {
        $query = ReadingSet::where('skill', 'listening')->with('quiz');

        $quizId = $request->query('quiz');
        if ($quizId) {
            $query->where('quiz_id', $quizId);
        }

        $sets = $query->orderBy('order')->get();

        return view('student.listening.index', compact('sets'));
    }

    /**
     * Display the specified set with its questions.
     */
    public function show(ReadingSet $set)
    {
        if ($set->skill !== 'listening') {
            abort(404);
        }

        $set->load('questions');

        return view('student.listening.sets.index', compact('set'));
    }
}
