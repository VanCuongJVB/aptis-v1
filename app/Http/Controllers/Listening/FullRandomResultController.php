<?php
namespace App\Http\Controllers\Listening;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Attempt;
use App\Models\AttemptAnswer;
use App\Models\Question;
use Illuminate\Support\Facades\DB;

class FullRandomResultController extends Controller
{
    public function store(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'answers' => 'required|array',
            'answers.*.qid' => 'required|integer|exists:questions,id',
            'answers.*.userAnswer' => 'nullable',
            'answers.*.correctAnswer' => 'nullable',
            'answers.*.part' => 'nullable',
            'answers.*.correct' => 'boolean|nullable',
        ]);

        // Get or create the virtual full random listening quiz
        $quiz = DB::table('quizzes')
            ->where('title', 'Full Random Listening')
            ->where('skill', 'listening')
            ->where('part', 0)
            ->first();
        
        if (!$quiz) {
            $quizId = DB::table('quizzes')->insertGetId([
                'title' => 'Full Random Listening',
                'description' => 'Auto-generated full listening test with random questions',
                'skill' => 'listening',
                'part' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $quizId = $quiz->id;
        }

        $attempt = Attempt::create([
            'user_id' => $user->id,
            'quiz_id' => $quizId,
            'status' => 'submitted',
            'started_at' => now(),
            'submitted_at' => now(),
            'total_questions' => count($data['answers']),
            'metadata' => [
                'full_random' => true,
                'client_ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ],
        ]);

        $correctCount = 0;
        $totalQuestions = 0;

        try {
            foreach ($data['answers'] as $ans) {
                $question = Question::find($ans['qid']);
                $userAnswer = $ans['userAnswer'];
                $correctAnswer = $ans['correctAnswer'];
                $isCorrect = $ans['correct'] ?? false;
                $part = $ans['part'] ?? null;

                // Handle multi-part questions (Parts 2, 3, 4)
                if (($part == 2 || $part == 3 || $part == 4) && is_array($userAnswer) && is_array($correctAnswer)) {
                    $this->createSubAnswers($attempt, $question, $userAnswer, $correctAnswer, $part, $correctCount, $totalQuestions);
                } else {
                    // Handle single-answer questions (Part 1)
                    $this->createSingleAnswer($attempt, $question, $userAnswer, $correctAnswer, $part, $isCorrect, $correctCount, $totalQuestions);
                }
            }

            $attempt->update([
                'correct_answers' => $correctCount,
                'total_questions' => $totalQuestions,
                'score_percentage' => round($correctCount / max(1, $totalQuestions) * 100, 2),
            ]);

        } catch (\Exception $e) {
            Log::error('FullRandomResultController store error', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi khi lưu kết quả: ' . $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Lưu kết quả thành công!',
            'attempt' => $attempt->id,
            'redirect' => route('listening.full-random.result', $attempt)
        ]);
    }

    private function createSubAnswers($attempt, $question, $userAnswer, $correctAnswer, $part, &$correctCount, &$totalQuestions)
    {
        foreach ($userAnswer as $index => $subUserAnswer) {
            $subCorrectAnswer = $correctAnswer[$index] ?? null;
            $subIsCorrect = $subUserAnswer !== null && $subCorrectAnswer !== null && 
                           intval($subUserAnswer) === intval($subCorrectAnswer);
            
            if ($subIsCorrect) {
                $correctCount++;
            }
            $totalQuestions++;

            AttemptAnswer::create([
                'attempt_id' => $attempt->id,
                'question_id' => $question ? $question->id : null,
                'sub_index' => $index,
                'selected_option_id' => null,
                'is_correct' => $subIsCorrect,
                'text_answer' => (string)$subUserAnswer,
                'metadata' => [
                    'userAnswer' => $subUserAnswer,
                    'correct' => $subCorrectAnswer,
                    'part' => $part,
                    'sub_index' => $index,
                    'full_user_answer' => $userAnswer,
                    'full_correct_answer' => $correctAnswer,
                ],
            ]);
        }
    }

    private function createSingleAnswer($attempt, $question, $userAnswer, $correctAnswer, $part, $isCorrect, &$correctCount, &$totalQuestions)
    {
        if ($isCorrect) {
            $correctCount++;
        }
        $totalQuestions++;

        AttemptAnswer::create([
            'attempt_id' => $attempt->id,
            'question_id' => $question ? $question->id : null,
            'sub_index' => 0,
            'selected_option_id' => null,
            'is_correct' => $isCorrect,
            'text_answer' => is_array($userAnswer) ? json_encode($userAnswer, JSON_UNESCAPED_UNICODE) : (string)$userAnswer,
            'metadata' => [
                'userAnswer' => $userAnswer,
                'correct' => $correctAnswer,
                'part' => $part,
            ],
        ]);
    }

    public function result($attempt)
    {
        try {
            $attempt = Attempt::with(['answers.question'])->findOrFail($attempt);
            
            // Group answers by question_id for parts 2,3,4
            $groupedAnswers = $attempt->answers->groupBy('question_id');
            
            $answers = $groupedAnswers->map(function($questionAnswers) {
                $firstAnswer = $questionAnswers->first();
                $metadata = $firstAnswer->metadata;
                $question = $firstAnswer->question;
                $part = $metadata['part'] ?? null;
                
                // For parts 2,3,4 with multiple sub-answers, reconstruct the arrays
                if (($part == 2 || $part == 3 || $part == 4) && $questionAnswers->count() > 1) {
                    return $this->reconstructMultiPartAnswer($questionAnswers, $firstAnswer, $question, $part);
                } else {
                    // For Part 1 and single answers
                    return $this->reconstructSingleAnswer($firstAnswer, $metadata, $question, $part);
                }
            })->values();

            return view('student.listening.full_random_result', [
                'attempt' => $attempt,
                'answers' => $answers,
                'quiz' => (object)[
                    'title' => 'Full Random Listening', 
                    'part' => null
                ],
            ]);
            
        } catch (\Exception $e) {
            Log::error('FullRandomResultController result error', [
                'attempt_id' => $attempt,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->view('errors.500', [
                'message' => 'Có lỗi khi hiển thị kết quả: ' . $e->getMessage()
            ], 500);
        }
    }

    private function reconstructMultiPartAnswer($questionAnswers, $firstAnswer, $question, $part)
{
    $userAnswers = [];
    $correctAnswers = [];
    $userAnswerTexts = [];
    $correctAnswerTexts = [];
    $isCorrect = true;
    
    foreach ($questionAnswers->sortBy('sub_index') as $subAnswer) {
        $subMeta = $subAnswer->metadata;
        $userAns = $subMeta['userAnswer'] ?? null;
        $correctAns = $subMeta['correct'] ?? null;
        
        $userAnswers[] = $userAns;
        $correctAnswers[] = $correctAns;
        
        // Convert to text for display only
        $userText = $userAns;
        $correctText = $correctAns;
        
        if ($question && isset($question->metadata['options'])) {
            $options = $question->metadata['options'];
            
            // Convert indices to text for display
            if (($part == 2 || $part == 3) && is_numeric($userAns) && isset($options[$userAns])) {
                $userText = $options[$userAns];
            }
            if (($part == 2 || $part == 3) && is_numeric($correctAns) && isset($options[$correctAns])) {
                $correctText = $options[$correctAns];
            }
        }
        
        $userAnswerTexts[] = $userText;
        $correctAnswerTexts[] = $correctText;
        
        // Check correctness using raw values
        if ($userAns !== null && $correctAns !== null) {
            $subIsCorrect = false;
            if (is_numeric($userAns) && is_numeric($correctAns)) {
                $subIsCorrect = intval($userAns) === intval($correctAns);
            } else {
                $subIsCorrect = strtoupper(trim($userAns)) === strtoupper(trim($correctAns));
            }
            
            if (!$subIsCorrect) {
                $isCorrect = false;
            }
        } else {
            $isCorrect = false;
        }
    }
    
    return [
        'qid' => $firstAnswer->question_id,
        'part' => $part,
        'correct' => $isCorrect,
        'userAnswer' => $userAnswers, // Keep raw indices for correct checking
        'correctAnswer' => $correctAnswers, // Keep raw indices for correct checking
        'userAnswerText' => $userAnswerTexts, // Use for display only
        'correctAnswerText' => $correctAnswerTexts, // Use for display only
        'question' => $question ? [
            'stem' => $question->stem,
            'content' => $question->content,
            'order_no' => $question->order_no,
            'metadata' => $question->metadata
        ] : null
    ];
}

    private function reconstructSingleAnswer($firstAnswer, $metadata, $question, $part)
    {
        $userAnswer = $metadata['userAnswer'] ?? null;
        $correctAnswer = $metadata['correct'] ?? null;
        
        // For Part 1, convert numeric indices to actual option text
        $userAnswerText = $userAnswer;
        $correctAnswerText = $correctAnswer;
        
        if ($part == 1 && $question && isset($question->metadata['options'])) {
            $options = $question->metadata['options'];
            
            // Convert user answer index to text
            if (is_numeric($userAnswer) && isset($options[$userAnswer])) {
                $userAnswerText = $options[$userAnswer];
            }
            
            // Convert correct answer index to text
            if (is_numeric($correctAnswer) && isset($options[$correctAnswer])) {
                $correctAnswerText = $options[$correctAnswer];
            }
        }
        
        return [
            'qid' => $firstAnswer->question_id,
            'part' => $part,
            'correct' => $firstAnswer->is_correct,
            'userAnswer' => $userAnswerText,
            'correctAnswer' => $correctAnswerText,
            'question' => $question ? [
                'stem' => $question->stem,
                'content' => $question->content,
                'order_no' => $question->order_no,
                'metadata' => $question->metadata
            ] : null
        ];
    }
}
