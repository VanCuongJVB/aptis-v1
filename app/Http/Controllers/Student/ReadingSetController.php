<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ReadingSet;
use Illuminate\Http\Request;

class ReadingSetController extends Controller
{
    /**
     * Display a listing of reading sets.
     */
    public function index(Request $request)
    {
        $query = ReadingSet::where('skill', 'reading')->with('quiz');

        $quizId = $request->query('quiz');
        if ($quizId) {
            $query->where('quiz_id', $quizId);
        }

        $sets = $query->orderBy('order')->get();

        return view('student.reading.index', compact('sets'));
    }

    /**
     * Display the specified set with its questions.
     */
    public function show(ReadingSet $set)
    {
        if ($set->skill !== 'reading') {
            abort(404);
        }

        $set->load('questions');

        return view('student.reading.sets.show', compact('set'));
    }
}
