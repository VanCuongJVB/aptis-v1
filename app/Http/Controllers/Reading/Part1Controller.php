<?php

namespace App\Http\Controllers\Reading;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\Option;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Part1Controller extends Controller
{
    public function create(Quiz $quiz)
    {
        return view('admin.reading.part1.create', compact('quiz'));
    }

    public function store(Request $request, Quiz $quiz)
    {
        $request->validate([
            'passage' => 'required|string',
            'questions' => 'required|array',
            'questions.*.stem' => 'required|string',
            'questions.*.options' => 'required|array',
            'questions.*.options.*' => 'required|string',
            'questions.*.correct_option' => 'required|integer|min:0'
        ]);

        try {
            DB::beginTransaction();

            // Store each question
            $maxOrder = $quiz->questions()->where('part', 1)->max('order') ?? 0;

            foreach ($request->questions as $index => $questionData) {
                // Create question
                $question = new Question([
                    'quiz_id' => $quiz->id,
                    'part' => 1,
                    'type' => 'multiple_choice',
                    'stem' => $questionData['stem'],
                    'order' => $maxOrder + $index + 1
                ]);
                $question->save();

                // Create options
                foreach ($questionData['options'] as $i => $optionText) {
                    $question->options()->create([
                        'label' => $optionText,
                        'is_correct' => $i == $questionData['correct_option']
                    ]);
                }
            }

            // Store passage in quiz metadata
            $quiz->update([
                'metadata' => array_merge($quiz->metadata ?? [], [
                    'passage' => $request->passage
                ])
            ]);

            DB::commit();

            return redirect()
                ->route('admin.reading.sets.edit', ['quiz' => $quiz, 'part' => 1])
                ->with('success', 'Questions created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function edit(Question $question)
    {
        $quiz = $question->quiz;
        $questions = $quiz->questions()
                         ->where('part', 1)
                         ->with('options')
                         ->orderBy('order')
                         ->get();

        return view('admin.reading.part1.edit', [
            'quiz' => $quiz,
            'currentQuestion' => $question,
            'questions' => $questions
        ]);
    }

    public function update(Request $request, Question $question)
    {
        $request->validate([
            'stem' => 'required|string',
            'options' => 'required|array',
            'options.*' => 'required|string',
            'correct_option' => 'required|integer|min:0'
        ]);

        try {
            DB::beginTransaction();

            // Update question
            $question->update([
                'stem' => $request->stem
            ]);

            // Update options
            foreach ($request->options as $i => $optionText) {
                if ($i < $question->options->count()) {
                    $question->options[$i]->update([
                        'label' => $optionText,
                        'is_correct' => $i == $request->correct_option
                    ]);
                } else {
                    $question->options()->create([
                        'label' => $optionText,
                        'is_correct' => $i == $request->correct_option
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('admin.reading.sets.edit', ['quiz' => $question->quiz, 'part' => 1])
                ->with('success', 'Question updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
