<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\Question;
use Illuminate\Http\Request;

class ReadingSetController extends Controller
{
    public function index()
    {
        return view('admin.reading.index');
    }

    public function showPart($part)
    {
        $quizzes = Quiz::where('type', 'reading')
            ->where('part', $part)
            ->withCount('questions')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.reading.part', compact('quizzes', 'part'));
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
            // Create a full set with all parts
            $quiz = Quiz::create([
                'title' => $request->title,
                'type' => $request->type,
                'part' => null, // Full set doesn't have a specific part
                'is_published' => false,
                'is_full_set' => true,
                'metadata' => $metadata
            ]);

            // Redirect to full set creation page
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['quiz' => $quiz], 201);
            }

            return redirect()->route("admin.{$request->type}.sets.create.full", $quiz)
                ->with('success', 'Full set created successfully. Add questions for each part.');
        }

        // Create a single part set
        $quiz = Quiz::create([
            'title' => $request->title,
            'type' => $request->type,
            'part' => $request->part,
            'is_published' => false,
            'is_full_set' => false,
            'metadata' => $metadata
        ]);

        // Redirect to appropriate creation page based on type and part
        $route = match([$request->type, $request->part]) {
            ['reading', 1] => 'admin.reading.part1.create',
            ['reading', 2] => 'admin.reading.part2.create',
            ['reading', 3] => 'admin.reading.part3.create',
            ['reading', 4] => 'admin.reading.part4.create',
            ['listening', 1] => 'admin.listening.part1.create',
            ['listening', 2] => 'admin.listening.part2.create',
            default => 'admin.reading.sets.part'
        };

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'quiz' => $quiz->loadCount('questions')
            ], 201);
        }

        if ($request->type === 'reading' && $request->part == 1) {
            return redirect()->route('admin.reading.part1.create', ['quiz' => $quiz->id])
                ->with('success', 'Reading set created. Now add questions.');
        }

        return redirect()->route('admin.reading.sets.edit', ['quiz' => $quiz, 'part' => $request->part])
            ->with('success', ucfirst($request->type) . ' set created successfully');
    }

    public function edit(Quiz $quiz, Request $request)
    {
        $part = $request->input('part', $quiz->part);
        
        $questions = $quiz->questions()
            ->with('options')
            ->orderBy('order')
            ->get();

        return view('admin.reading.sets.edit', [
            'quiz' => $quiz,
            'questions' => $questions,
            'passage' => $quiz->metadata['passage'] ?? null,
            'part' => $part
        ]);
    }

    public function update(Request $request, Quiz $quiz)
    {
        $request->validate([
            'title' => 'required|string|max:255'
        ]);

        $quiz->update([
            'title' => $request->title
        ]);

        return back()->with('success', 'Reading set updated successfully');
    }

    public function destroy(Quiz $quiz)
    {
        $part = $quiz->part;
        $quiz->delete();
        
        return redirect()->route('admin.reading.sets.part', $part)
            ->with('success', 'Reading set deleted successfully');
    }

    public function publish(Quiz $quiz)
    {
        $quiz->update(['is_published' => true]);
        return back()->with('success', 'Reading set published successfully');
    }

    public function unpublish(Quiz $quiz)
    {
        $quiz->update(['is_published' => false]);
        return back()->with('success', 'Reading set unpublished successfully');
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
