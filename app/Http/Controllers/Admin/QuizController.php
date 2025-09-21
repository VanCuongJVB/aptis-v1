<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function index()
    {
        $quizzes = Quiz::orderByDesc('created_at')->paginate(20);
        return view('admin.quizzes.index', compact('quizzes'));
    }

    public function create()
    {
        return view('admin.quizzes.form', ['quiz' => new Quiz()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'skill' => 'required|in:reading,listening',
            'part' => 'required|integer|min:1|max:4',
            'is_published' => 'boolean',
            'duration_minutes' => 'required|integer|min:1',
            'show_explanation' => 'boolean',
        ]);
        $data['is_published'] = $request->has('is_published');
        $data['show_explanation'] = $request->has('show_explanation');
        Quiz::create($data);
        return redirect()->route('admin.quizzes.index')->with('success', 'Quiz created successfully.');
    }

    public function edit(Quiz $quiz)
    {
        return view('admin.quizzes.form', compact('quiz'));
    }

    public function update(Request $request, Quiz $quiz)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'skill' => 'required|in:reading,listening',
            'part' => 'required|integer|min:1|max:4',
            'is_published' => 'boolean',
            'duration_minutes' => 'required|integer|min:1',
            'show_explanation' => 'boolean',
        ]);
        $data['is_published'] = $request->has('is_published');
        $data['show_explanation'] = $request->has('show_explanation');
        $quiz->update($data);
        return redirect()->route('admin.quizzes.index')->with('success', 'Quiz updated successfully.');
    }

    public function destroy(Quiz $quiz)
    {
        $quiz->delete();
        return redirect()->route('admin.quizzes.index')->with('success', 'Quiz deleted successfully.');
    }
}
