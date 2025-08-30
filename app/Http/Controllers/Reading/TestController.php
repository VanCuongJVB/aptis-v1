<?php

namespace App\Http\Controllers\Reading;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\Attempt;
use App\Models\AttemptItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{
    /**
     * Display a listing of the tests.
     */
    public function index()
    {
        $tests = Quiz::where('skill', 'reading')
            ->whereHas('questions', function($q) {
                $q->whereNotNull('part');
            })
            ->withCount('questions')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('student.reading.test.index', [
            'tests' => $tests
        ]);
    }

    /**
     * Start a new test.
     */
    public function start(Quiz $quiz = null)
    {
        // If no specific quiz is selected, pick a random one
        if (!$quiz) {
            $quiz = Quiz::where('skill', 'reading')
                ->whereHas('questions', function($q) {
                    $q->whereNotNull('part');
                })
                ->inRandomOrder()
                ->firstOrFail();
        }

        // Create a new attempt
        $attempt = Attempt::create([
            'user_id' => auth()->id(),
            'quiz_id' => $quiz->id,
            'mode' => 'test',
            'status' => 'in_progress'
        ]);

        return view('student.reading.test.start', [
            'quiz' => $quiz,
            'attempt' => $attempt
        ]);
    }

    /**
     * Submit a test.
     */
    public function submit(Request $request)
    {
        $validated = $request->validate([
            'attempt_id' => 'required|exists:attempts,id',
            'answers' => 'required|array'
        ]);

        $attempt = Attempt::findOrFail($validated['attempt_id']);
        
        // Check if all questions are answered
        $totalQuestions = $attempt->quiz->questions()->count();
        if (count($validated['answers']) !== $totalQuestions) {
            return back()->with('error', 'Please answer all questions before submitting.');
        }

        // Process answers
        foreach ($validated['answers'] as $questionId => $answer) {
            $question = Question::findOrFail($questionId);
            
            AttemptItem::create([
                'attempt_id' => $attempt->id,
                'question_id' => $questionId,
                'answer' => $answer,
                'is_correct' => $this->checkAnswer($question, $answer)
            ]);
        }

        // Update attempt status
        $attempt->update([
            'status' => 'completed',
            'completed_at' => now()
        ]);

        return redirect()->route('reading.test.result', $attempt);
    }

    /**
     * Show test results.
     */
    public function result(Attempt $attempt)
    {
        $attempt->load(['quiz', 'items.question']);

        $results = [
            'total_questions' => $attempt->items->count(),
            'correct_answers' => $attempt->items->where('is_correct', true)->count(),
            'time_taken' => $attempt->completed_at->diffInMinutes($attempt->created_at),
            'by_part' => []
        ];

        // Calculate results by part
        foreach (range(1, 4) as $part) {
            $partItems = $attempt->items->filter(function ($item) use ($part) {
                return $item->question->part === $part;
            });

            if ($partItems->isNotEmpty()) {
                $results['by_part'][$part] = [
                    'total' => $partItems->count(),
                    'correct' => $partItems->where('is_correct', true)->count()
                ];
            }
        }

        return view('student.reading.test.result', [
            'attempt' => $attempt,
            'results' => $results
        ]);
    }

    /**
     * Check if an answer is correct.
     */
    private function checkAnswer(Question $question, $answer): bool
    {
        return $question->options()
            ->where('id', $answer)
            ->where('is_correct', true)
            ->exists();
    }
}
