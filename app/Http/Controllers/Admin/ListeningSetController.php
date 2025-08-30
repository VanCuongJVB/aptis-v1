<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\Question;
use Illuminate\Http\Request;

class ListeningSetController extends Controller
{
    public function index()
    {
        return view('admin.listening.index');
    }

    public function showPart($part)
    {
        $quizzes = Quiz::where('type', 'listening')
            ->where('part', $part)
            ->withCount('questions')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.listening.part', compact('quizzes', 'part'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:reading,listening',
            'part' => 'required_unless:is_full_set,true|integer|between:1,7',
            'description' => 'nullable|string',
            'is_full_set' => 'required|boolean'
        ]);

        $metadata = [
            'description' => $request->description
        ];

        if ($request->is_full_set) {
            $quiz = Quiz::create([
                'title' => $request->title,
                'type' => $request->type,
                'part' => null,
                'is_published' => false,
                'is_full_set' => true,
                'metadata' => $metadata
            ]);

            return redirect()->route('admin.listening.sets.create.full', $quiz)
                ->with('success', 'Full listening set created successfully. Add questions for each part.');
        }

        $quiz = Quiz::create([
            'title' => $request->title,
            'type' => $request->type,
            'part' => $request->part,
            'is_published' => false,
            'is_full_set' => false,
            'metadata' => $metadata
        ]);

        return redirect()->route('admin.listening.part1.create', $quiz)
            ->with('success', 'Listening set created successfully');
    }

    public function edit(Quiz $quiz)
    {
        $questions = $quiz->questions()
            ->with('options')
            ->orderBy('order')
            ->get();

        return view('admin.listening.edit', [
            'quiz' => $quiz,
            'questions' => $questions
        ]);
    }

    public function createFull(Quiz $quiz)
    {
        return view('admin.listening.create_full', [
            'quiz' => $quiz
        ]);
    }

    public function update(Request $request, Quiz $quiz)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        $metadata = $quiz->metadata;
        $metadata['description'] = $request->description;

        $quiz->update([
            'title' => $request->title,
            'metadata' => $metadata
        ]);

        return back()->with('success', 'Listening set updated successfully');
    }

    public function destroy(Quiz $quiz)
    {
        $part = $quiz->part;
        $quiz->delete();
        
        return redirect()->route('admin.listening.sets.part', $part)
            ->with('success', 'Listening set deleted successfully');
    }

    public function publish(Quiz $quiz)
    {
        $quiz->update(['is_published' => true]);
        return back()->with('success', 'Listening set published successfully');
    }

    public function unpublish(Quiz $quiz)
    {
        $quiz->update(['is_published' => false]);
        return back()->with('success', 'Listening set unpublished successfully');
    }

    public function reorderQuestions(Request $request, Quiz $quiz)
    {
        $request->validate([
            'orders' => 'required|array',
            'orders.*' => 'required|integer|exists:questions,id'
        ]);

        foreach ($request->orders as $index => $questionId) {
            $quiz->questions()->where('id', $questionId)->update([
                'order' => $index + 1
            ]);
        }

        return response()->json(['message' => 'Questions reordered successfully']);
    }
}
