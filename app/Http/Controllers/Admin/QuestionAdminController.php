<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\ReadingSet;

class QuestionAdminController extends Controller
{
    public function create()
    {
        $quizzes = Quiz::orderBy('title')->get();
        $sets = ReadingSet::orderBy('title')->get();
        return view('admin.quizzes.question_form', ['question' => new Question(), 'quizzes' => $quizzes, 'sets' => $sets]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'quiz_id' => 'nullable|exists:quizzes,id',
            'reading_set_id' => 'nullable|exists:sets,id',
            'title' => 'nullable|string|max:255',
            'stem' => 'nullable|string',
            'type' => 'nullable|string|max:50',
            'order' => 'nullable|integer',
        ]);

        Question::create($data);

        return redirect()->route('admin.quizzes.questions')->with('success', 'Question created');
    }

    public function edit(Question $question)
    {
        $quizzes = Quiz::orderBy('title')->get();
        $sets = ReadingSet::orderBy('title')->get();
        return view('admin.quizzes.question_form', ['question' => $question, 'quizzes' => $quizzes, 'sets' => $sets]);
    }

    public function update(Request $request, Question $question)
    {
        $data = $request->validate([
            'quiz_id' => 'nullable|exists:quizzes,id',
            'reading_set_id' => 'nullable|exists:sets,id',
            'title' => 'nullable|string|max:255',
            'stem' => 'nullable|string',
            'type' => 'nullable|string|max:50',
            'order' => 'nullable|integer',
        ]);

        $question->update($data);

        return redirect()->route('admin.quizzes.questions')->with('success', 'Question updated');
    }

    public function destroy(Question $question)
    {
        $question->delete();
        return redirect()->route('admin.quizzes.questions')->with('success', 'Question removed');
    }
}
