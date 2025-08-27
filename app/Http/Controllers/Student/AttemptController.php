<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\{Quiz, Attempt, AttemptItem};

class AttemptController extends Controller
{
    public function start(Request $request, Quiz $quiz)
    {
        abort_unless($quiz->is_published, 404);
        Attempt::create(['user_id'=>Auth::id(),'quiz_id'=>$quiz->id,'started_at'=>now()]);
        return redirect()->route('student.quizzes.show', $quiz);
    }

    public function submit(Request $request, Quiz $quiz)
    {
        $answers = $request->input('answers', []);
        $attempt = Attempt::create([
            'user_id'=>$request->user()->id,'quiz_id'=>$quiz->id,'started_at'=>now(),'submitted_at'=>now(),
        ]);

        $quiz->load('questions.options');
        $correctCount = 0;
        foreach ($quiz->questions as $question) {
            $selected = array_map('intval', $answers[$question->id] ?? []); sort($selected);
            $correctIds = $question->options()->where('is_correct', true)->pluck('id')->toArray(); sort($correctIds);

            $isCorrect = $question->type === 'single'
                ? (count($selected) === 1) && ($selected[0] === ($correctIds[0] ?? null))
                : ($selected === $correctIds);

            AttemptItem::create([
                'attempt_id'=>$attempt->id,'question_id'=>$question->id,
                'selected_option_ids'=>$selected,'is_correct'=>$isCorrect,'time_spent_sec'=>0,
            ]);
            if ($isCorrect) $correctCount++;
        }

        $total = max(1, $quiz->questions->count());
        $attempt->update(['score_raw'=>$correctCount,'score_percent'=>round($correctCount*100/$total,2)]);

        return redirect()->route('student.attempts.result', $attempt);
    }

    public function result(Attempt $attempt)
    {
        $attempt->load(['quiz.questions.options','items']);
        $itemsByQid = $attempt->items->keyBy('question_id');
        return view('student.attempts.show', compact('attempt','itemsByQid'));
    }
}
