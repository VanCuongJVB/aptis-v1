<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Http\Request;

class ReadingQuestionController extends Controller
{
    public function create(Quiz $quiz)
    {
        return view('admin.reading.create', compact('quiz'));
    }

    public function store(Request $request, Quiz $quiz)
    {
        $validated = $request->validate([
            'part' => 'required|integer|between:1,4',
            'type' => 'required|in:reading_sentence_matching,reading_notice_matching,reading_long_text,reading_gap_filling',
            'stem' => 'required|string',
            'explanation' => 'nullable|string',
            'meta' => 'required|array',
            'options' => 'required|array',
            'options.*.label' => 'required|string',
            'options.*.is_correct' => 'required|boolean'
        ]);

        $question = $quiz->questions()->create([
            'part' => $validated['part'],
            'type' => $validated['type'],
            'stem' => $validated['stem'],
            'explanation' => $validated['explanation'],
            'meta' => $validated['meta'],
            'order' => $quiz->questions()->max('order') + 1
        ]);

        foreach ($validated['options'] as $option) {
            $question->options()->create([
                'label' => $option['label'],
                'is_correct' => $option['is_correct']
            ]);
        }

        return redirect()
            ->route('admin.quizzes.edit', $quiz)
            ->with('success', 'Reading question created successfully.');
    }

    public function edit(Quiz $quiz, Question $question)
    {
        $question->load('options');
        return view('admin.reading.edit', compact('quiz', 'question'));
    }

    public function update(Request $request, Quiz $quiz, Question $question)
    {
        $validated = $request->validate([
            'part' => 'required|integer|between:1,4',
            'type' => 'required|in:reading_sentence_matching,reading_notice_matching,reading_long_text,reading_gap_filling',
            'stem' => 'required|string',
            'explanation' => 'nullable|string',
            'meta' => 'required|array',
            'options' => 'required|array',
            'options.*.id' => 'nullable|exists:options,id',
            'options.*.label' => 'required|string',
            'options.*.is_correct' => 'required|boolean'
        ]);

        $question->update([
            'part' => $validated['part'],
            'type' => $validated['type'],
            'stem' => $validated['stem'],
            'explanation' => $validated['explanation'],
            'meta' => $validated['meta']
        ]);

        // Update existing options and create new ones
        foreach ($validated['options'] as $optionData) {
            if (isset($optionData['id'])) {
                $question->options()->where('id', $optionData['id'])->update([
                    'label' => $optionData['label'],
                    'is_correct' => $optionData['is_correct']
                ]);
            } else {
                $question->options()->create([
                    'label' => $optionData['label'],
                    'is_correct' => $optionData['is_correct']
                ]);
            }
        }

        // Delete options not in the request
        $keepOptionIds = array_column(array_filter($validated['options'], fn($o) => isset($o['id'])), 'id');
        $question->options()->whereNotIn('id', $keepOptionIds)->delete();

        return redirect()
            ->route('admin.quizzes.edit', $quiz)
            ->with('success', 'Reading question updated successfully.');
    }
}
