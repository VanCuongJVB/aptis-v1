<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Quiz;

class QuizController extends Controller
{
    public function index()
    {
        $quizzes = Quiz::orderBy('id', 'desc')->paginate(20);
        return view('admin.quizzes.index', compact('quizzes'));
    }
    public function create()
    {
        return view('admin.quizzes.create');
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'skill' => 'required|in:reading,listening',
            'description' => 'nullable|string',
            'is_published' => 'boolean',
            'duration_minutes' => 'required|integer|min:1|max:300',
            'allow_seek' => 'boolean',
            'listens_allowed' => 'integer|min:1|max:10',
        ]);
        $data['is_published'] = $request->boolean('is_published');
        $data['allow_seek'] = $request->boolean('allow_seek');
        $quiz = Quiz::create($data);
        return redirect()->route('admin.quizzes.edit', $quiz)->with('ok', 'Đã tạo bài.');
    }
    // public function edit(Quiz $quiz){ $quiz->load('questions.options'); return view('admin.quizzes.edit', compact('quiz')); }
    public function edit(Quiz $quiz)
    {
        $quiz->load('questions.options');
        $parts = $quiz->partsConfig(); // mảng 1..4
        return view('admin.quizzes.edit', compact('quiz', 'parts'));
    }
    public function update(Request $request, Quiz $quiz)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'skill' => 'required|in:reading,listening',
            'description' => 'nullable|string',
            'is_published' => 'boolean',
            'duration_minutes' => 'required|integer|min:1|max:300',
            'allow_seek' => 'boolean',
            'listens_allowed' => 'integer|min:1|max:10',
        ]);
        $data['is_published'] = $request->boolean('is_published');
        $data['allow_seek'] = $request->boolean('allow_seek');
        $quiz->update($data);
        return back()->with('ok', 'Đã lưu.');
    }
    public function destroy(Quiz $quiz)
    {
        $quiz->delete();
        return redirect()->route('admin.quizzes.index')->with('ok', 'Đã xoá.');
    }
}
