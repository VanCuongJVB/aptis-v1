<?php

namespace App\Http\Controllers\Reading;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\Attempt;
use App\Models\AttemptAnswer;
use App\Models\Option;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PracticeController extends Controller
{
    /**
     * Hiển thị danh sách các phần Reading
     */
    public function index()
    {
        $parts = [
            1 => [
                'name' => 'Sentence Comprehension',
                'description' => 'Luyện tập hiểu nghĩa câu và chọn từ phù hợp với ngữ cảnh.',
                'icon' => 'fas fa-align-left',
                'color' => 'blue'
            ],
            2 => [
                'name' => 'Text Cohesion',
                'description' => 'Luyện tập kết nối và sắp xếp các phần của văn bản.',
                'icon' => 'fas fa-puzzle-piece',
                'color' => 'green'
            ],
            3 => [
                'name' => 'Reading Comprehension',
                'description' => 'Luyện tập đọc hiểu đoạn văn ngắn và trả lời câu hỏi.',
                'icon' => 'fas fa-book-open',
                'color' => 'orange'
            ],
            4 => [
                'name' => 'Long Text Reading',
                'description' => 'Luyện tập đọc hiểu bài đọc dài và trả lời câu hỏi.',
                'icon' => 'fas fa-book',
                'color' => 'red'
            ]
        ];
        
        // Thống kê tiến độ của học sinh
        $progress = [];
        foreach ($parts as $part => $info) {
            // Số bộ đề đã làm
            $completedQuizzes = Attempt::where('user_id', Auth::id())
                ->whereHas('quiz', function($query) use ($part) {
                    $query->where('skill', 'reading')
                          ->where('part', $part);
                })
                ->where('status', 'submitted')
                ->distinct('quiz_id')
                ->count('quiz_id');
                
            // Tổng số bộ đề
            $totalQuizzes = Quiz::where('skill', 'reading')
                ->where('part', $part)
                ->where('is_published', true)
                ->count();
                
            // Điểm trung bình
            $avgScore = Attempt::where('user_id', Auth::id())
                ->whereHas('quiz', function($query) use ($part) {
                    $query->where('skill', 'reading')
                          ->where('part', $part);
                })
                ->where('status', 'submitted')
                ->avg('score_percentage');
                
            $progress[$part] = [
                'completed' => $completedQuizzes,
                'total' => $totalQuizzes,
                'percent' => $totalQuizzes > 0 ? round(($completedQuizzes / $totalQuizzes) * 100) : 0,
                'average_score' => $avgScore ? round($avgScore) : 0
            ];
        }
        
    // Dashboard is canonical for student home — redirect there
    return redirect()->route('student.dashboard');
    }
    
    /**
     * Hiển thị danh sách các bộ đề trong một phần
     */
    public function partDetail(int $part)
    {
        // Kiểm tra part hợp lệ
        if ($part < 1 || $part > 4) {
            return redirect()->route('reading.sets.index')
                ->with('error', 'Phần không hợp lệ');
        }
        
        $partNames = [
            1 => 'Sentence Comprehension',
            2 => 'Text Cohesion',
            3 => 'Reading Comprehension',
            4 => 'Long Text Reading'
        ];
        
        // Lấy danh sách các bộ đề đã xuất bản
        $quizzes = Quiz::where('skill', 'reading')
            ->where('part', $part)
            ->where('is_published', true)
            ->withCount('questions')
            ->orderBy('difficulty')
            ->orderBy('created_at', 'desc')
            ->get();
            
        // Lấy thông tin các lượt làm bài của học sinh
        $attempts = Attempt::where('user_id', Auth::id())
            ->whereIn('quiz_id', $quizzes->pluck('id'))
            ->where('status', 'submitted')
            ->get()
            ->groupBy('quiz_id');
            
        return view('student.reading.part', [
            'part' => $part,
            'partName' => $partNames[$part],
            'quizzes' => $quizzes,
            'attempts' => $attempts
        ]);
    }
    
    /**
     * Bắt đầu luyện tập một bộ đề
     */
    public function startQuiz(Request $request, Quiz $quiz)
    {
        // Kiểm tra xem quiz có phải reading quiz đã xuất bản không
        if ($quiz->skill !== 'reading' || !$quiz->is_published) {
            return redirect()->route('reading.sets.index')
                ->with('error', 'Bộ đề không hợp lệ hoặc chưa được xuất bản');
        }
        // Optional: accept a reading set and mode (learning/exam)
    $setId = $request->query('set_id', null);
    $mode = $request->query('mode', 'learning');

        $metadata = ['mode' => $mode];
        $questionOrder = [];

        if ($setId) {
            // validate set belongs to quiz
            $set = \App\Models\ReadingSet::where('id', $setId)->where('quiz_id', $quiz->id)->first();
            if ($set) {
                $metadata['reading_set_id'] = $set->id;
                // take question ids from the set in its order
                $questionOrder = $set->questions()->orderBy('order')->pluck('id')->toArray();
            }
        }

        // Fallback: if no set or set has no questions, use quiz questions ordered by their order
        if (empty($questionOrder)) {
            $questionOrder = $quiz->questions()->orderBy('order')->pluck('id')->toArray();
        }

        $metadata['question_order'] = $questionOrder;

        // Tạo một lượt làm bài mới
        $attempt = Attempt::create([
            'user_id' => Auth::id(),
            'quiz_id' => $quiz->id,
            'started_at' => now(),
            'status' => 'in_progress',
            'metadata' => $metadata
        ]);
        
        // Chuyển hướng đến câu hỏi đầu tiên
        return redirect()->route('reading.practice.question', ['attempt' => $attempt, 'position' => 1]);
    }
    
    /**
     * Hiển thị một câu hỏi trong bộ đề
     */
    public function showQuestion(Attempt $attempt, int $position)
    {
        // Kiểm tra quyền truy cập
        if ($attempt->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền truy cập lượt làm bài này');
        }
        
        // Kiểm tra xem bài làm đã hoàn thành chưa
        if ($attempt->isSubmitted()) {
            return redirect()->route('reading.practice.result', $attempt);
        }
        
        // Resolve question by attempt-specific question_order if present
        $order = $attempt->metadata['question_order'] ?? $attempt->quiz->questions()->orderBy('order')->pluck('id')->toArray();
        $questionId = $order[$position - 1] ?? null;
        if (!$questionId) {
            // no question at this position -> finalize the attempt first
            return redirect()->route('reading.practice.finish', $attempt);
        }

        $question = Question::find($questionId);
        if (!$question) {
            // missing question record -> finalize the attempt
            return redirect()->route('reading.practice.finish', $attempt);
        }
        
        // Lấy câu trả lời trước đó nếu có
        $answer = AttemptAnswer::where('attempt_id', $attempt->id)
            ->where('question_id', $question->id)
            ->first();
        
        // Lấy thông tin tổng quan về bài làm (dựa trên question_order của attempt)
        $totalQuestions = count($order);
        $answeredCount = AttemptAnswer::where('attempt_id', $attempt->id)
            ->whereIn('question_id', $order)
            ->count();
        
        return view('student.reading.question', [
            'attempt' => $attempt,
            'quiz' => $attempt->quiz,
            'question' => $question,
            'position' => $position,
            'total' => $totalQuestions,
            'answered' => $answeredCount,
            'answer' => $answer,
            'previousPosition' => $position > 1 ? $position - 1 : null,
            'nextPosition' => $position < $totalQuestions ? $position + 1 : null
        ]);
    }
    
    /**
     * Lưu câu trả lời cho một câu hỏi
     */
    public function submitAnswer(Request $request, Attempt $attempt, Question $question)
    {
        // Kiểm tra quyền truy cập
        if ($attempt->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền truy cập lượt làm bài này');
        }
        
        // Kiểm tra xem bài làm đã hoàn thành chưa
        if ($attempt->isSubmitted()) {
            return redirect()->route('reading.practice.result', $attempt);
        }
        
    // Trust frontend: FE will send final answer metadata (and optional is_correct/selected_option_id).
    $clientProvided = $request->boolean('client_provided');

    // Accept metadata blob from FE, or build minimal metadata from inputs provided.
    $answerMeta = $request->input('metadata', $request->input('answer_meta', []));
    if (empty($answerMeta)) {
        // try common input shapes for backward compatibility
        $answerMeta = [];
        if ($request->has('part1_choice')) { $answerMeta['selected'] = $request->input('part1_choice'); }
        if ($request->has('part2_order')) { $answerMeta['selected'] = ['order' => $request->input('part2_order')]; }
        if ($request->has('part3_answer')) { $answerMeta['selected'] = $request->input('part3_answer'); }
        if ($request->has('part4_choice')) { $answerMeta['selected'] = $request->input('part4_choice'); }
        if ($request->has('option_id')) { $answerMeta['selected'] = ['option_id' => $request->input('option_id')]; }
    }

    $selOption = $request->input('selected_option_id', $request->input('option_id', null));
    $isCorrect = $request->has('is_correct') ? (bool)$request->input('is_correct') : false;

    // Persist AttemptAnswer exactly as FE sent it (no server-side correctness calculation)
    AttemptAnswer::updateOrCreate(
        [
            'attempt_id' => $attempt->id,
            'question_id' => $question->id
        ],
        [
            'selected_option_id' => $selOption,
            'is_correct' => $isCorrect,
            'metadata' => $answerMeta
        ]
    );
        
        // Nếu là request Ajax, trả về JSON response. Nếu action=finish, hoàn tất bài làm và trả về redirect URL
        if ($request->ajax()) {
            // If student submitted the whole test via AJAX
            if ($request->input('action') === 'finish') {
                // Resolve attempt-specific order and positions
                $order = $attempt->metadata['question_order'] ?? $attempt->quiz->questions()->orderBy('order')->pluck('id')->toArray();
                $totalQuestions = count($order);
                $posIndex = array_search($question->id, $order);
                $posIndex = $posIndex === false ? 0 : $posIndex;
                $nextPosition = $posIndex + 2; // 1-based

                // If frontend didn't provide overall totals and there are remaining questions,
                // treat finish as save+next and redirect to next question
                if (! $request->has('client_totals') && $totalQuestions > 1) {
                    if ($nextPosition <= $totalQuestions) {
                        return response()->json([
                            'success' => true,
                            'message' => 'Đã lưu câu trả lời',
                            'redirect' => route('reading.practice.question', ['attempt' => $attempt, 'position' => $nextPosition])
                        ]);
                    }
                    // otherwise fall through to finalize
                }

                // If the frontend computed correctness (practice mode), accept client totals/answers
                if ($clientProvided && $request->has('client_totals')) {
                    $totals = $request->input('client_totals', []);
                    $totalQuestions = $totals['total_questions'] ?? count($order);
                    $correctAnswers = $totals['correct_answers'] ?? null;
                    $scorePercentage = $totals['score_percentage'] ?? null;
                    $scorePoints = $totals['score_points'] ?? $correctAnswers;

                    // Persist per-question answers if client provided them (optional)
                    if ($request->has('answers') && is_array($request->input('answers'))) {
                        $clientAnswers = $request->input('answers');
                        foreach ($clientAnswers as $qId => $ansData) {
                            // normalize
                            $meta = $ansData['metadata'] ?? ($ansData['meta'] ?? null);
                            $selOption = $ansData['selected_option_id'] ?? ($ansData['option_id'] ?? null);

                            $isCorr = array_key_exists('is_correct', $ansData) ? (bool)$ansData['is_correct'] : false;

                            AttemptAnswer::updateOrCreate(
                                ['attempt_id' => $attempt->id, 'question_id' => $qId],
                                [
                                    'selected_option_id' => $selOption,
                                    'is_correct' => $isCorr,
                                    'metadata' => $meta ?? $ansData
                                ]
                            );
                        }
                    }

                    // Fallback server-side counts if client didn't provide specific fields
                    if ($correctAnswers === null) {
                        $correctAnswers = AttemptAnswer::where('attempt_id', $attempt->id)
                            ->whereIn('question_id', $order)
                            ->where('is_correct', true)
                            ->count();
                    }
                    if ($scorePercentage === null) {
                        $scorePercentage = $totalQuestions > 0 ? round(($correctAnswers / $totalQuestions) * 100, 2) : 0;
                    }

                } else {
                    // Default: server recompute (kept as fallback)
                    $totalQuestions = count($order);
                    $correctAnswers = AttemptAnswer::where('attempt_id', $attempt->id)
                        ->whereIn('question_id', $order)
                        ->where('is_correct', true)
                        ->count();

                    $scorePercentage = $totalQuestions > 0 ? round(($correctAnswers / $totalQuestions) * 100, 2) : 0;
                    $scorePoints = $correctAnswers;
                }

                $attempt->update([
                    'status' => 'submitted',
                    'submitted_at' => now(),
                    'total_questions' => $totalQuestions,
                    'correct_answers' => $correctAnswers,
                    'score_percentage' => $scorePercentage,
                    'score_points' => $scorePoints
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Bài đã nộp',
                    // redirect directly to result so FE lands on final page
                    'redirect' => route('reading.practice.result', $attempt)
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Đã lưu câu trả lời',
                'is_correct' => (bool)$isCorrect
            ]);
        }
        
        // Xác định vị trí hiện tại của câu hỏi
    // Determine current position using attempt.question_order
    $order = $attempt->metadata['question_order'] ?? $attempt->quiz->questions()->orderBy('order')->pluck('id')->toArray();
    $currentPosition = array_search($question->id, $order);
    $currentPosition = $currentPosition === false ? 1 : $currentPosition + 1;
        
        // Chuyển hướng đến câu hỏi tiếp theo hoặc kết quả
        if ($request->input('action') === 'finish' || $currentPosition >= $attempt->quiz->questions()->count()) {
            return redirect()->route('reading.practice.finish', $attempt);
        } else {
            return redirect()->route('reading.practice.question', [
                'attempt' => $attempt,
                'position' => $currentPosition + 1
            ]);
        }
    }
    
    /**
     * Hoàn thành bài làm
     */
    public function finishAttempt(Request $request, Attempt $attempt)
    {
        // Kiểm tra quyền truy cập
        if ($attempt->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền truy cập lượt làm bài này');
        }
        
        // Nếu bài làm đã hoàn thành, chuyển hướng đến trang kết quả
        if ($attempt->isSubmitted()) {
            return redirect()->route('reading.practice.result', $attempt);
        }
        
        // Prefer client-provided totals (FE-trusted). If absent, fallback to server-side recompute.
        $order = $attempt->metadata['question_order'] ?? $attempt->quiz->questions()->orderBy('order')->pluck('id')->toArray();
        $totalQuestions = count($order);

        if ($request->has('client_totals')) {
            $totals = $request->input('client_totals', []);
            $totalQuestions = $totals['total_questions'] ?? $totalQuestions;
            $correctAnswers = $totals['correct_answers'] ?? ($attempt->correct_answers ?? 0);
            $scorePercentage = $totals['score_percentage'] ?? ($totalQuestions > 0 ? round(($correctAnswers / $totalQuestions) * 100, 2) : 0);
            $scorePoints = $totals['score_points'] ?? $correctAnswers;
        } else {
            $correctAnswers = AttemptAnswer::where('attempt_id', $attempt->id)
                ->whereIn('question_id', $order)
                ->where('is_correct', true)
                ->count();

            $scorePercentage = $totalQuestions > 0 ? round(($correctAnswers / $totalQuestions) * 100, 2) : 0;
            $scorePoints = $correctAnswers;
        }

        // Cập nhật lượt làm bài (trusting client totals when provided)
        $attempt->update([
            'status' => 'submitted',
            'submitted_at' => now(),
            'total_questions' => $totalQuestions,
            'correct_answers' => $correctAnswers,
            'score_percentage' => $scorePercentage,
            'score_points' => $scorePoints
        ]);

        return redirect()->route('reading.practice.result', $attempt);
    }
    
    /**
     * Hiển thị kết quả bài làm
     */
    public function showResult(Attempt $attempt)
    {
        // Kiểm tra quyền truy cập
        if ($attempt->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền truy cập lượt làm bài này');
        }
        
        // Lấy tất cả câu hỏi và câu trả lời
        // Prefer attempt-specific question_order if available so result order matches the attempt
        $order = $attempt->metadata['question_order'] ?? $attempt->quiz->questions()->orderBy('order')->pluck('id')->toArray();

        $questionsCollection = Question::whereIn('id', $order)
            ->with(['options' => function($query) {
                $query->orderBy('id');
            }])
            ->get()
            ->keyBy('id');

        // Preserve the original order array and map to question models
        $questions = collect($order)->map(function($id) use ($questionsCollection) {
            return $questionsCollection->get($id);
        })->filter()->values();
            
        $answers = AttemptAnswer::where('attempt_id', $attempt->id)
            ->with('selectedOption')
            ->get()
            ->keyBy('question_id');
            
        // Tính thời gian làm bài
        $duration = null;
        if ($attempt->started_at && $attempt->submitted_at) {
            $duration = $attempt->submitted_at->diffInMinutes($attempt->started_at);
        }
        
        return view('student.reading.result', [
            'attempt' => $attempt,
            'quiz' => $attempt->quiz,
            'questions' => $questions,
            'answers' => $answers,
            'duration' => $duration
        ]);
    }
    
    /**
     * Hiển thị lịch sử luyện tập Reading
     */
    public function history()
    {
        $attempts = Attempt::where('user_id', Auth::id())
            ->whereHas('quiz', function($query) {
                $query->where('skill', 'reading');
            })
            ->where('status', 'submitted')
            ->with('quiz')
            ->orderBy('submitted_at', 'desc')
            ->paginate(10);
            
        return view('student.reading.history', [
            'attempts' => $attempts
        ]);
    }
    
    /**
     * Hiển thị thống kê và tiến độ luyện tập
     */
    public function progress()
    {
        $parts = [
            1 => 'Sentence Comprehension',
            2 => 'Text Cohesion',
            3 => 'Reading Comprehension',
            4 => 'Long Text Reading'
        ];
        
        // Thống kê theo từng phần
        $stats = [];
        foreach ($parts as $part => $name) {
            // Số bộ đề đã hoàn thành
            $completedQuizzes = DB::table('attempts')
                ->join('quizzes', 'attempts.quiz_id', '=', 'quizzes.id')
                ->where('attempts.user_id', Auth::id())
                ->where('quizzes.skill', 'reading')
                ->where('quizzes.part', $part)
                ->where('attempts.status', 'submitted')
                ->distinct('quizzes.id')
                ->count('quizzes.id');
                
            // Tổng số bộ đề
            $totalQuizzes = Quiz::where('skill', 'reading')
                ->where('part', $part)
                ->where('is_published', true)
                ->count();
                
            // Số câu trả lời đúng
            $correctAnswers = AttemptAnswer::whereHas('attempt', function($query) use ($part) {
                    $query->where('user_id', Auth::id())
                          ->whereHas('quiz', function($q) use ($part) {
                              $q->where('skill', 'reading')
                                ->where('part', $part);
                          });
                })
                ->where('is_correct', true)
                ->count();
                
            // Tổng số câu đã trả lời
            $totalAnswers = AttemptAnswer::whereHas('attempt', function($query) use ($part) {
                    $query->where('user_id', Auth::id())
                          ->whereHas('quiz', function($q) use ($part) {
                              $q->where('skill', 'reading')
                                ->where('part', $part);
                          });
                })
                ->count();
                
            // Điểm trung bình
            $avgScore = Attempt::where('user_id', Auth::id())
                ->whereHas('quiz', function($query) use ($part) {
                    $query->where('skill', 'reading')
                          ->where('part', $part);
                })
                ->where('status', 'submitted')
                ->avg('score_percentage');
                
            $stats[$part] = [
                'name' => $name,
                'completed' => $completedQuizzes,
                'total' => $totalQuizzes,
                'progress' => $totalQuizzes > 0 ? round(($completedQuizzes / $totalQuizzes) * 100) : 0,
                'correct' => $correctAnswers,
                'answered' => $totalAnswers,
                'accuracy' => $totalAnswers > 0 ? round(($correctAnswers / $totalAnswers) * 100) : 0,
                'average_score' => $avgScore ? round($avgScore) : 0
            ];
        }
        
        // Dữ liệu tiến trình theo thời gian (10 lượt làm gần nhất)
        $recentAttempts = Attempt::where('user_id', Auth::id())
            ->whereHas('quiz', function($query) {
                $query->where('skill', 'reading');
            })
            ->where('status', 'submitted')
            ->with('quiz')
            ->orderBy('submitted_at', 'desc')
            ->take(10)
            ->get()
            ->reverse();
            
        $chartData = [
            'labels' => $recentAttempts->map(function($attempt) {
                return $attempt->submitted_at->format('d/m/Y');
            })->toArray(),
            'scores' => $recentAttempts->map(function($attempt) {
                return $attempt->score_percentage;
            })->toArray(),
            'parts' => $recentAttempts->map(function($attempt) {
                return 'Part ' . $attempt->quiz->part;
            })->toArray()
        ];
        
        return view('student.reading.progress', [
            'stats' => $stats,
            'chartData' => $chartData
        ]);
    }
}
