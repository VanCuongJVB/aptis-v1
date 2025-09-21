<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReadingSet;
use App\Models\Quiz;

class ReadingSetController extends Controller
{
    public function create()
    {
        $quizzes = Quiz::orderBy('title')->get();
        return view('admin.quizzes.sets_form', ['set' => new ReadingSet(), 'quizzes' => $quizzes]);
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            'quiz_id' => 'required|exists:quizzes,id',
            'title' => 'required|string|max:255',
            'skill' => 'required|in:reading,listening',
        ]);
        ReadingSet::create($data);
        return redirect()->route('admin.quizzes.sets')->with('success', 'Set created');
    }

    public function edit(ReadingSet $set)
    {
        $quizzes = Quiz::orderBy('title')->get();
        return view('admin.quizzes.sets_form', ['set' => $set, 'quizzes' => $quizzes]);
    }

    public function update(Request $request, ReadingSet $set)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
        ]);
        $set->update(['title' => $data['title']]);
        return redirect()->route('admin.quizzes.sets')->with('success', 'Set updated');
    }

    public function destroy(ReadingSet $set)
    {
        $set->delete();
        return redirect()->route('admin.quizzes.sets')->with('success', 'Set removed');
    }

    /**
     * Hiển thị danh sách câu hỏi của 1 set (mọi part)
     */
    public function questions($setId)
    {
        $part = request('part');
        $set = ReadingSet::with(['quiz', 'questions' => function($q) use ($part) {
            $q->orderBy('order');
            if ($part) $q->where('part', $part);
        }])->findOrFail($setId);
        return view('admin.quizzes.set_questions', compact('set', 'part'));
    }
}
