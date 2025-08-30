<?php

namespace App\Http\Controllers\Listening;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\Option;
use Illuminate\Http\Request;

class Part1Controller extends Controller
{
    public function create(Quiz $quiz)
    {
        return view('admin.listening.create_part1', compact('quiz'));
    }

    public function store(Request $request, Quiz $quiz)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'audio_url' => 'required|string',
            'transcript' => 'required|string',
            'words.*' => 'required|string',
            'correct_words.*' => 'required|string'
        ]);

        $question = Question::create([
            'quiz_id' => $quiz->id,
            'type' => 'listening_word_selection',
            'stem' => $request->title,
            'metadata' => [
                'audio_url' => $request->audio_url,
                'transcript' => $request->transcript
            ],
            'order' => $quiz->questions()->count() + 1
        ]);

        foreach ($request->words as $index => $word) {
            Option::create([
                'question_id' => $question->id,
                'label' => $word,
                'is_correct' => in_array($word, $request->correct_words)
            ]);
        }

        return redirect()->route('admin.listening.sets.edit', $quiz)
            ->with('success', 'Question added successfully');
    }

    public function edit(Question $question)
    {
        return view('admin.listening.edit_part1', compact('question'));
    }

    public function update(Request $request, Question $question)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'audio_url' => 'required|string',
            'transcript' => 'required|string',
            'words.*' => 'required|string',
            'correct_words.*' => 'required|string'
        ]);

        $question->update([
            'stem' => $request->title,
            'metadata' => [
                'audio_url' => $request->audio_url,
                'transcript' => $request->transcript
            ]
        ]);

        // Delete existing options
        $question->options()->delete();

        // Create new options
        foreach ($request->words as $word) {
            Option::create([
                'question_id' => $question->id,
                'label' => $word,
                'is_correct' => in_array($word, $request->correct_words)
            ]);
        }

        return redirect()->route('admin.listening.sets.edit', $question->quiz)
            ->with('success', 'Question updated successfully');
    }

    public function destroy(Question $question)
    {
        $quiz = $question->quiz;
        $question->delete();

        return redirect()->route('admin.listening.sets.edit', $quiz)
            ->with('success', 'Question deleted successfully');
    }
}
