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
            'answers.*.correct' => 'nullable',
            'answers.*.part' => 'nullable',
        ]);

        // Lấy quiz_id của quiz ảo full random listening
        $quizId = DB::table('quizzes')
            ->where('title', 'Full Random Listening')
            ->where('skill', 'listening')
            ->where('part', 0)
            ->value('id');
        if (!$quizId) {
            return response()->json(['success' => false, 'message' => 'Quiz full random listening chưa được seed!'], 500);
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
                // Nếu là part4, tách từng câu con thành answer riêng
                if ($ans['part'] == 4 && is_array($ans['userAnswer']) && is_array($ans['correct'])) {
                    // Lấy danh sách id các câu con từ metadata
                    $subIds = [];
                    if (isset($ans['metadata']) && is_array($ans['metadata'])) {
                        $subIds = array_map(function($q){ return $q['id'] ?? null; }, $ans['metadata']['questions'] ?? []);
                    }
                    foreach ($ans['userAnswer'] as $idx => $ua) {
                        // Nếu có id câu con, dùng đúng id, nếu không fallback về qid gốc
                        $subQid = isset($subIds[$idx]) ? $subIds[$idx] : (isset($ans['qid']) ? $ans['qid'] : null);
                        $question = Question::find($subQid);
                        $correct = isset($ans['correct'][$idx]) ? $ans['correct'][$idx] : null;
                        $isCorrect = (string)$ua === (string)$correct;
                        Log::info('CHECK PART4 SUB', [
                            'qid' => $subQid,
                            'sub_idx' => $idx,
                            'userAnswer' => $ua,
                            'correct' => $correct,
                            'isCorrect' => $isCorrect
                        ]);
                        if ($isCorrect) $correctCount++;
                        $totalQuestions++;
                        AttemptAnswer::create([
                            'attempt_id' => $attempt->id,
                            'question_id' => $question ? $question->id : null,
                            'selected_option_id' => null,
                            'is_correct' => $isCorrect,
                            'text_answer' => (string)$ua,
                            'metadata' => [
                                'userAnswer' => $ua,
                                'correct' => $correct,
                                'part' => 4,
                                'sub_index' => $idx,
                            ],
                        ]);
                    }
                } else {
                    $question = Question::find($ans['qid']);
                    $userAnswer = $ans['userAnswer'];
                    $correct = $ans['correct'];
                    if (is_array($userAnswer) && is_array($correct)) {
                        $ua = array_map('strval', $userAnswer);
                        $ca = array_map('strval', $correct);
                        $isCorrect = ($ua == $ca);
                    } elseif (!is_array($userAnswer) && !is_array($correct)) {
                        $isCorrect = (string)$userAnswer === (string)$correct;
                    } else {
                        $isCorrect = false;
                    }
                    Log::info('CHECK ANSWER', [
                        'qid' => $ans['qid'],
                        'userAnswer' => $userAnswer,
                        'correct' => $correct,
                        'isCorrect' => $isCorrect
                    ]);
                    if ($isCorrect) $correctCount++;
                    $totalQuestions++;
                    AttemptAnswer::create([
                        'attempt_id' => $attempt->id,
                        'question_id' => $question ? $question->id : null,
                        'selected_option_id' => null,
                        'is_correct' => $isCorrect,
                        'text_answer' => (is_array($userAnswer) || is_array($correct)) ? json_encode($userAnswer, JSON_UNESCAPED_UNICODE) : (string)$userAnswer,
                        'metadata' => [
                            'userAnswer' => $userAnswer,
                            'correct' => $correct,
                            'part' => $ans['part'] ?? null,
                        ],
                    ]);
                }
            }
            $attempt->update([
                'correct_answers' => $correctCount,
                'total_questions' => $totalQuestions,
                'score_percentage' => round($correctCount / max(1, $totalQuestions) * 100, 2),
            ]);
        } catch (\Exception $e) {
            Log::error('FullRandomResultController error', [
                'userId' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi khi lưu kết quả: ' . $e->getMessage(),
            ], 500);
        }
    }
    public function result($attempt)
    {
        $attempt = Attempt::with('answers.question')->findOrFail($attempt);
        // Sort lại thứ tự câu hỏi theo order
        $questions = $attempt->answers->map(function($a){ return $a->question; })->sortBy('order')->values();
        $answers = $attempt->answers->keyBy('question_id');
        return view('student.listening.result', [
            'attempt' => $attempt,
            'questions' => $questions,
            'answers' => $answers,
            'quiz' => (object)['title' => 'Full Random Listening', 'part' => null],
            'computedTotals' => [
                'total' => $attempt->total_questions,
                'correct' => $attempt->correct_answers,
                'score' => $attempt->score_percentage,
            ],
        ]);
    }
}
