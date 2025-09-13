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
            'quiz_id' => 'nullable|exists:quizzes,id',
            'title' => 'required|string|max:255',
            'skill' => 'nullable|string|max:50',
            'order' => 'nullable|integer',
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
            'quiz_id' => 'nullable|exists:quizzes,id',
            'title' => 'required|string|max:255',
            'skill' => 'nullable|string|max:50',
            'order' => 'nullable|integer',
        ]);

        $set->update($data);

        return redirect()->route('admin.quizzes.sets')->with('success', 'Set updated');
    }

    public function destroy(ReadingSet $set)
    {
        $set->delete();
        return redirect()->route('admin.quizzes.sets')->with('success', 'Set removed');
    }
}
