<?php

namespace App\Http\Controllers\Reading;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\Question;
use Illuminate\Support\Facades\Auth;
use App\Models\Attempt;
use App\Models\AttemptItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DrillController extends Controller
{
    /**
     * Show the part drill index page.
     */
    public function index(int $part)
    {
        $user = Auth::user();
        $stats = [];
        
        // Get stats for each reading part
        for ($i = 1; $i <= 4; $i++) {
            $attempts = Attempt::where('user_id', $user->id)
                ->whereHas('quiz.questions', function($q) use ($i) {
                    $q->where('part', $i);
                })
                ->get();

            $total = Quiz::whereHas('questions', function($q) use ($i) {
                $q->where('part', $i);
            })->count();

            $completed = $attempts->where('status', 'completed')->count();
            $accuracy = $attempts->avg('score') * 100 ?? 0;

            $hasWrongAnswers = AttemptItem::whereHas('attempt', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->whereHas('question', function($q) use ($i) {
                    $q->where('part', $i);
                })
                ->where('is_correct', false)
                ->exists();

            $stats['part' . $i] = [
                'completed' => $completed,
                'total' => $total,
                'accuracy' => $accuracy,
                'hasWrongAnswers' => $hasWrongAnswers
            ];
        }

        return view('student.reading.drill.index', [
            'part' => $part,
            'partLabel' => Question::READING_PART_LABELS[$part] ?? 'Unknown Part',
            'partType' => Question::READING_TYPES[$part] ?? null,
            'stats' => $stats
        ]);
    }

    /**
     * List all available sets for a specific part.
     */
    public function listSets(int $part)
    {
        $user = Auth::user();
        
        $quizzes = Quiz::where('skill', 'reading')
            ->whereHas('questions', function($q) use ($part) {
                $q->where('part', $part);
            })
            ->with(['attempts' => function($q) use ($user) {
                $q->where('user_id', $user->id);
            }])
            ->get()
            ->map(function($quiz) {
                $lastAttempt = $quiz->attempts->last();
                return [
                    'id' => $quiz->id,
                    'title' => $quiz->title,
                    'status' => $this->calculateSetStatus($lastAttempt),
                    'accuracy' => $lastAttempt ? round($lastAttempt->score * 100) : null,
                    'last_attempt' => $lastAttempt ? $lastAttempt->created_at->diffForHumans() : null
                ];
            });

        return view('student.reading.drill.sets', [
            'part' => $part,
            'partLabel' => Question::READING_PART_LABELS[$part],
            'quizzes' => $quizzes
        ]);
    }

    /**
     * Start a practice set.
     */
    public function startSet(Quiz $quiz)
    {
        $attempt = Attempt::create([
            'user_id' => Auth::id(),
            'quiz_id' => $quiz->id,
            'mode' => 'drill',
            'status' => 'in_progress'
        ]);

        $firstQuestion = $quiz->questions()->orderBy('order')->first();

        return view('student.reading.drill.practice', [
            'quiz' => $quiz,
            'attempt' => $attempt,
            'question' => $firstQuestion,
            'totalQuestions' => $quiz->questions->count(),
            'currentNumber' => 1
        ]);
    }

    /**
     * Submit an answer for a question.
     */
    public function submitAnswer(Request $request)
    {
        $validated = $request->validate([
            'attempt_id' => 'required|exists:attempts,id',
            'question_id' => 'required|exists:questions,id',
            'answer' => 'required'
        ]);

        $attempt = Attempt::findOrFail($validated['attempt_id']);
        $question = Question::findOrFail($validated['question_id']);
        
        // Create or update attempt item
        $attemptItem = AttemptItem::updateOrCreate(
            [
                'attempt_id' => $attempt->id,
                'question_id' => $question->id
            ],
            [
                'answer' => $validated['answer'],
                'is_correct' => $this->checkAnswer($question, $validated['answer'])
            ]
        );

        // Update attempt statistics
        $this->updateAttemptStats($attempt);

        return response()->json([
            'is_correct' => $attemptItem->is_correct,
            'explanation' => $question->explanation,
            'correct_answer' => $question->correct_answer
        ]);
    }

    /**
     * Get the next question in the set.
     */
    public function nextQuestion(Quiz $quiz, Question $currentQuestion)
    {
        $nextQuestion = $quiz->questions()
            ->where('order', '>', $currentQuestion->order)
            ->orderBy('order')
            ->first();

        if (!$nextQuestion) {
            return redirect()->route('reading.drill.summary', $quiz);
        }

        return view('student.reading.drill.question', [
            'question' => $nextQuestion,
            'currentNumber' => $currentQuestion->order + 1,
            'totalQuestions' => $quiz->questions->count()
        ]);
    }

    /**
     * Show summary of the practice session.
     */
    public function summary(Quiz $quiz)
    {
        $attempt = $quiz->attempts()
            ->where('user_id', Auth::id())
            ->latest()
            ->firstOrFail();

        $attemptItems = $attempt->items()->with('question')->get();
        
        return view('student.reading.drill.summary', [
            'quiz' => $quiz,
            'attempt' => $attempt,
            'items' => $attemptItems,
            'accuracy' => round($attempt->score * 100),
            'timeSpent' => $attempt->created_at->diffInMinutes($attempt->updated_at)
        ]);
    }

    /**
     * Flag a question for review.
     */
    public function flagQuestion(Request $request)
    {
        $validated = $request->validate([
            'question_id' => 'required|exists:questions,id',
            'is_flagged' => 'required|boolean'
        ]);

        $attemptItem = AttemptItem::where('question_id', $validated['question_id'])
            ->where('attempt_id', $request->attempt_id)
            ->firstOrFail();

        $attemptItem->update(['is_flagged' => $validated['is_flagged']]);

        return response()->json(['status' => 'success']);
    }

    /**
     * Show progress for all parts or a specific part.
     */
    public function progress(?int $part = null)
    {
        $user = Auth::user();
        $query = Attempt::where('user_id', $user->id)
            ->where('mode', 'drill')
            ->with('quiz.questions');

        if ($part) {
            $query->whereHas('quiz.questions', function($q) use ($part) {
                $q->where('part', $part);
            });
        }

        $attempts = $query->get();
        
        // Calculate statistics
        $stats = $this->calculateProgressStats($attempts, $part);

        return view('student.reading.drill.progress', [
            'part' => $part,
            'stats' => $stats,
            'recentAttempts' => $attempts->take(5)
        ]);
    }

    /**
     * Show wrong answers for a specific part.
     */
    public function wrongAnswers(int $part)
    {
        $wrongAnswers = AttemptItem::whereHas('attempt', function($q) {
                $q->where('user_id', Auth::id())
                    ->where('mode', 'drill');
            })
            ->whereHas('question', function($q) use ($part) {
                $q->where('part', $part);
            })
            ->where('is_correct', false)
            ->with(['question', 'attempt'])
            ->get()
            ->groupBy('question_id');

        return view('student.reading.drill.wrong-answers', [
            'part' => $part,
            'wrongAnswers' => $wrongAnswers
        ]);
    }

    /**
     * Start practicing wrong answers.
     */
    public function startWrongAnswers(Request $request, int $part)
    {
        $questionIds = $request->input('question_ids');
        
        // Create a temporary quiz for wrong answers practice
        $quiz = Quiz::create([
            'title' => 'Wrong Answers Practice - Part ' . $part,
            'skill' => 'reading',
            'is_published' => false
        ]);

        // Attach selected questions to the quiz
        Question::whereIn('id', $questionIds)->update([
            'quiz_id' => $quiz->id
        ]);

        return $this->startSet($quiz);
    }

    /**
     * Calculate the status of a set based on the last attempt.
     */
    private function calculateSetStatus(?Attempt $attempt): string
    {
        if (!$attempt) {
            return 'not_started';
        }

        if ($attempt->status === 'in_progress') {
            return 'in_progress';
        }

        $score = $attempt->score * 100;
        if ($score >= 80) {
            return 'completed';
        }

        return 'needs_review';
    }

    /**
     * Check if an answer is correct.
     */
    private function checkAnswer(Question $question, $answer): bool
    {
        // Implementation depends on question type
        return $question->correct_answer === $answer;
    }

    /**
     * Update attempt statistics.
     */
    private function updateAttemptStats(Attempt $attempt): void
    {
        $stats = $attempt->items()
            ->select(DB::raw('COUNT(*) as total, SUM(CASE WHEN is_correct THEN 1 ELSE 0 END) as correct'))
            ->first();

        $attempt->update([
            'score' => $stats->total > 0 ? $stats->correct / $stats->total : 0
        ]);
    }

    /**
     * Calculate progress statistics.
     */
    private function calculateProgressStats($attempts, ?int $part): array
    {
        $stats = [
            'total_attempts' => $attempts->count(),
            'average_accuracy' => $attempts->avg('score') * 100,
            'completed_sets' => $attempts->where('status', 'completed')->count(),
            'needs_review' => $attempts->where('status', 'needs_review')->count(),
            'recent_trend' => $this->calculateRecentTrend($attempts)
        ];

        if ($part) {
            $stats['part_specific'] = [
                'common_mistakes' => $this->analyzeCommonMistakes($attempts, $part),
                'improvement_rate' => $this->calculateImprovementRate($attempts)
            ];
        }

        return $stats;
    }

    private function calculateRecentTrend($attempts): array
    {
        return $attempts->take(7)
            ->map(fn($attempt) => [
                'date' => $attempt->created_at->format('Y-m-d'),
                'score' => round($attempt->score * 100)
            ])
            ->toArray();
    }

    private function analyzeCommonMistakes($attempts, int $part): array
    {
        // Group wrong answers by question type or specific error patterns
        // Implementation depends on part-specific requirements
        return [];
    }

    private function calculateImprovementRate($attempts): float
    {
        if ($attempts->count() < 2) {
            return 0;
        }

        $firstScore = $attempts->last()->score;
        $lastScore = $attempts->first()->score;

        return round(($lastScore - $firstScore) * 100, 2);
    }
}
