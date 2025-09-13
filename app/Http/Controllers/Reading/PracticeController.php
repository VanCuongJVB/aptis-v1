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
use Illuminate\Support\Facades\Log;

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
            return redirect()->route('student.reading.sets.index')
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
            return redirect()->route('student.reading.sets.index')
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
    public function showQuestion(Request $request, Attempt $attempt, int $position)
    {
        // Kiểm tra quyền truy cập
        if ($attempt->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền truy cập lượt làm bài này');
        }
        
        // Kiểm tra xem bài làm đã hoàn thành chưa
        if ($attempt->isSubmitted()) {
            return redirect()->route('reading.practice.result', $attempt);
        }
        
        // Resolve question(s) by attempt-specific question_order if present
        $order = $attempt->metadata['question_order'] ?? $attempt->quiz->questions()->orderBy('order')->pluck('id')->toArray();

        // Build normalized payload of questions (for FE practice mode)
        $questionsCollection = Question::whereIn('id', $order)
            ->with(['options' => function($query) {
                $query->orderBy('id');
            }])->get()->keyBy('id');

        $payloadQuestions = collect($order)->map(function($id) use ($questionsCollection) {
            $q = $questionsCollection->get($id);
            if (! $q) return null;
            $meta = $q->metadata ?? [];

            // normalize part 1
            if (($q->part ?? ($meta['part'] ?? null)) == 1) {
                $meta = array_merge([
                    'paragraphs' => $meta['paragraphs'] ?? [$q->content ?? $q->title],
                    'blank_keys' => $meta['blank_keys'] ?? [],
                    'choices' => $meta['choices'] ?? ($meta['options'] ?? []),
                    'correct_answers' => $meta['correct_answers'] ?? ($meta['answers'] ?? [])
                ], $meta);
            }

            // normalize part 2
            if (($q->part ?? ($meta['part'] ?? null)) == 2) {
                $meta['sentences'] = $meta['sentences'] ?? $meta['items'] ?? [];
                $meta['correct_order'] = $meta['correct_order'] ?? $meta['correct'] ?? [];
            }

            return [
                'id' => $q->id,
                'part' => $q->part ?? ($meta['part'] ?? null),
                'type' => $meta['type'] ?? 'unknown',
                'metadata' => $meta,
            ];
        })->filter()->values()->all();

        $payload = ['questions' => $payloadQuestions];

        // If dev requests a dump, show it for inspection
        if ($request->query('dump')) {
            dd($payload);
        }

    // If attempt requests full-part (opt-in), present the full part (all questions)
    if (!empty($attempt->metadata['full_part']) && $attempt->metadata['full_part']) {
            $questionsCollection = Question::whereIn('id', $order)
                ->with(['options' => function($query) {
                    $query->orderBy('id');
                }])
                ->get()
                ->keyBy('id');

            $questions = collect($order)->map(function($id) use ($questionsCollection) {
                return $questionsCollection->get($id);
            })->filter()->values();

            // load any previously saved answers for all questions in the order
            $answersMap = AttemptAnswer::where('attempt_id', $attempt->id)
                ->whereIn('question_id', $order)
                ->get()
                ->keyBy('question_id');

            $totalQuestions = count($order);
            $answeredCount = $answersMap->count();

            return view('student.reading.question', [
                'attempt' => $attempt,
                'quiz' => $attempt->quiz,
                'allQuestions' => $questions,
                'answersMap' => $answersMap,
                'position' => 1,
                'total' => $totalQuestions,
                'answered' => $answeredCount,
                'previousPosition' => null,
                'nextPosition' => null,
                'batchSubmitUrl' => route('reading.practice.batchSubmit', $attempt->id),
            ])->with('initialPayload', $payload);
        }

        // Fallback: single-question view
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

    // debug logging removed

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
        'nextPosition' => $position < $totalQuestions ? $position + 1 : null,
        'batchSubmitUrl' => route('reading.practice.batchSubmit', $attempt->id),
    ])->with('initialPayload', $payload);
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
    $clientProvided = $request->boolean('client_provided');

    // If this is an AJAX per-question submit, grade server-side, persist and update attempt counters.
    if ($request->ajax() && $request->input('action') === 'submit') {
    // debug logging removed
        // grade using question metadata
        $meta = $question->metadata ?? [];
        $grading = $this->gradeAnswer($question, $answerMeta);
        $isCorrect = $grading['is_correct'];
        $correctData = $grading['correct_data'];

        // atomic update: persist AttemptAnswer and adjust attempt counters idempotently
        DB::transaction(function() use ($attempt, $question, $selOption, $answerMeta, $isCorrect) {
            $existing = AttemptAnswer::where('attempt_id', $attempt->id)->where('question_id', $question->id)->first();

            // create or update the AttemptAnswer
            AttemptAnswer::updateOrCreate(
                ['attempt_id' => $attempt->id, 'question_id' => $question->id],
                ['selected_option_id' => $selOption, 'is_correct' => $isCorrect, 'metadata' => $answerMeta]
            );

            // maintain simple counts on attempt metadata/columns
            // Use columns total_questions and correct_answers to reflect progress
            $total = (int)($attempt->total_questions ?? 0);
            $correct = (int)($attempt->correct_answers ?? 0);

            if (! $existing) {
                $total += 1;
                if ($isCorrect) $correct += 1;
            } else {
                // if existed, adjust correct count if correctness changed
                $prevCorrect = (bool)$existing->is_correct;
                if ($prevCorrect !== $isCorrect) {
                    $correct += $isCorrect ? 1 : -1;
                    if ($correct < 0) $correct = 0;
                }
            }

            // update attempt counters (do not finalize here yet)
            $attempt->total_questions = $total;
            $attempt->correct_answers = $correct;
            $attempt->save();
        });

        // determine if attempt is complete (all questions answered)
        $order = $attempt->metadata['question_order'] ?? $attempt->quiz->questions()->orderBy('order')->pluck('id')->toArray();
        $totalQuestions = count($order);
        $answeredCount = AttemptAnswer::where('attempt_id', $attempt->id)
            ->whereIn('question_id', $order)
            ->count();

    $response = ['success' => true, 'is_correct' => $isCorrect, 'correct' => $correctData];

        if ($answeredCount >= $totalQuestions) {
            // finalize attempt
            $correctAnswers = AttemptAnswer::where('attempt_id', $attempt->id)
                ->whereIn('question_id', $order)
                ->where('is_correct', true)
                ->count();

            $scorePercentage = $totalQuestions > 0 ? round(($correctAnswers / $totalQuestions) * 100, 2) : 0;

            $attempt->update([
                'status' => 'submitted',
                'submitted_at' => now(),
                'total_questions' => $totalQuestions,
                'correct_answers' => $correctAnswers,
                'score_percentage' => $scorePercentage,
                'score_points' => $correctAnswers
            ]);

            $response['submitted'] = true;
            $response['redirect'] = route('reading.practice.result', $attempt);
        } else {
            // return next position for UI convenience
            $posIndex = array_search($question->id, $order);
            $nextPosition = $posIndex === false ? null : $posIndex + 2; // 1-based
            $response['next_position'] = $nextPosition;
        }

        return response()->json($response);
    }
        
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
                    // Ensure scorePoints is set (use client-provided or fallback to correctAnswers)
                    if (!isset($scorePoints) || $scorePoints === null) {
                        $scorePoints = $correctAnswers ?? 0;
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
                'message' => 'Đã lưu câu trả lời'
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
            if ($scorePoints === null) $scorePoints = $correctAnswers ?? 0;
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

        // Recompute attempt totals: measure per-item correctness across parts (so header shows item-level accuracy)
        try {
            $totalItems = 0;
            $correctItems = 0;

            foreach ($questions as $q) {
                $meta = $q->metadata ?? [];
                $part = $q->part ?? ($meta['part'] ?? null);

                // normalize meta for part1
                if ($part == 1) {
                    $meta = array_merge([
                        'paragraphs' => $meta['paragraphs'] ?? [$q->content ?? $q->title],
                        'blank_keys' => $meta['blank_keys'] ?? [],
                        'choices' => $meta['choices'] ?? ($meta['options'] ?? []),
                        'correct_answers' => $meta['correct_answers'] ?? ($meta['answers'] ?? [])
                    ], $meta);
                    $correct = $meta['correct_answers'] ?? [];
                    $ans = $answers->get($q->id);
                    $ansMeta = $ans && isset($ans->metadata) ? $ans->metadata : null;
                    // try to extract selected array
                    $selected = [];
                    if (is_array($ansMeta) && isset($ansMeta['selected']) && is_array($ansMeta['selected'])) $selected = array_values($ansMeta['selected']);
                    elseif (is_array($ansMeta)) $selected = array_values($ansMeta);

                    $count = max(count($correct), count($selected));
                    for ($i = 0; $i < $count; $i++) {
                        $totalItems++;
                        $u = isset($selected[$i]) ? trim((string)$selected[$i]) : '';
                        $e = isset($correct[$i]) ? (is_array($correct[$i]) ? (string)($correct[$i]['text'] ?? $correct[$i]['value'] ?? '') : (string)$correct[$i]) : '';
                        if ($e !== '') {
                            if (mb_strtolower($u) === mb_strtolower(trim($e))) $correctItems++;
                        }
                    }
                    continue;
                }

                if ($part == 2) {
                    $sentences = $meta['sentences'] ?? $meta['items'] ?? [];
                    $correctOrder = $meta['correct_order'] ?? $meta['correct'] ?? [];
                    $ans = $answers->get($q->id);
                    $ansMeta = $ans && isset($ans->metadata) ? $ans->metadata : null;
                    // extract selected order
                    $selectedOrder = null;
                    if (is_array($ansMeta)) {
                        if (isset($ansMeta['selected']['order'])) $selectedOrder = $ansMeta['selected']['order'];
                        elseif (isset($ansMeta['order'])) $selectedOrder = $ansMeta['order'];
                        elseif (isset($ansMeta['selected'])) $selectedOrder = $ansMeta['selected'];
                    }
                    if (is_string($selectedOrder)) {
                        $dec = json_decode($selectedOrder, true);
                        if (is_array($dec)) $selectedOrder = $dec;
                    }
                    $selectedOrder = is_array($selectedOrder) ? array_values($selectedOrder) : [];
                    $count = max(count($selectedOrder), count($correctOrder));
                    for ($i = 0; $i < $count; $i++) {
                        $totalItems++;
                        $userIdx = isset($selectedOrder[$i]) && $selectedOrder[$i] !== null && $selectedOrder[$i] !== '' ? (int)$selectedOrder[$i] : null;
                        $corrIdx = isset($correctOrder[$i]) && $correctOrder[$i] !== null && $correctOrder[$i] !== '' ? (int)$correctOrder[$i] : null;
                        $userText = $userIdx !== null ? ($sentences[$userIdx] ?? '') : '';
                        $corrText = $corrIdx !== null ? ($sentences[$corrIdx] ?? '') : '';
                        if ($userText !== '' && $corrText !== '' && mb_strtolower(trim($userText)) === mb_strtolower(trim($corrText))) $correctItems++;
                    }
                    continue;
                }

                if ($part == 3) {
                    $items = $meta['items'] ?? [];
                    $options = $meta['options'] ?? [];
                    $answersKey = $meta['answers'] ?? $meta['correct'] ?? [];
                    $ans = $answers->get($q->id);
                    $ansMeta = $ans && isset($ans->metadata) ? $ans->metadata : null;
                    $userArr = [];
                    if (is_array($ansMeta)) {
                        if (isset($ansMeta['selected']) && is_array($ansMeta['selected'])) $userArr = array_values($ansMeta['selected']);
                        elseif (isset($ansMeta['values']) && is_array($ansMeta['values'])) $userArr = array_values($ansMeta['values']);
                        elseif (isset($ansMeta['value']) && is_array($ansMeta['value'])) $userArr = array_values($ansMeta['value']);
                        else $userArr = array_values($ansMeta);
                    } elseif (is_string($ansMeta)) {
                        $dec = json_decode($ansMeta, true);
                        if (is_array($dec)) $userArr = array_values($dec);
                    }
                    $count = max(count($items), count($userArr), count($answersKey));
                    for ($i = 0; $i < $count; $i++) {
                        $totalItems++;
                        $raw = isset($userArr[$i]) ? $userArr[$i] : null;
                        $userText = ($raw !== null && isset($options[$raw])) ? $options[$raw] : ($raw !== null ? (string)$raw : '');
                        $corrRaw = isset($answersKey[$i]) ? $answersKey[$i] : null;
                        $corrText = ($corrRaw !== null && isset($options[$corrRaw])) ? $options[$corrRaw] : ($corrRaw !== null ? (string)$corrRaw : '');
                        if ($userText !== '' && $corrText !== '' && mb_strtolower(trim($userText)) === mb_strtolower(trim($corrText))) $correctItems++;
                    }
                    continue;
                }

                if ($part == 4) {
                    $options = $meta['options'] ?? [];
                    $paragraphs = $meta['paragraphs'] ?? [];
                    $correct = $meta['correct'] ?? [];
                    $ans = $answers->get($q->id);
                    $ansMeta = $ans && isset($ans->metadata) ? $ans->metadata : null;

                    // Build option lookup maps for index -> text and id -> text (when option items are objects)
                    $optByIndex = [];
                    $optById = [];
                    if (is_array($options)) {
                        foreach ($options as $k => $v) {
                            if (is_array($v)) {
                                $text = $v['text'] ?? $v['label'] ?? $v['content'] ?? $v['value'] ?? json_encode($v);
                                if (isset($v['id'])) $optById[(string)$v['id']] = $text;
                            } elseif (is_object($v)) {
                                $text = $v->text ?? $v->label ?? $v->content ?? $v->value ?? json_encode((array)$v);
                                if (isset($v->id)) $optById[(string)$v->id] = $text;
                            } else {
                                $text = (string)$v;
                            }
                            $optByIndex[(string)$k] = $text;
                        }
                    }

                    // normalize user values from stored metadata (support ['selected'], ['value'], or direct numeric array)
                    $userVals = [];
                    if (is_array($ansMeta)) {
                        if (isset($ansMeta['selected']) && is_array($ansMeta['selected'])) $userVals = array_values($ansMeta['selected']);
                        elseif (isset($ansMeta['value']) && is_array($ansMeta['value'])) $userVals = array_values($ansMeta['value']);
                        else {
                            // handle case where metadata is ['value' => [...]] or numeric keys
                            $maybe = array_values($ansMeta);
                            if (count($maybe) === 1 && is_array($maybe[0])) $userVals = array_values($maybe[0]);
                            else $userVals = $maybe;
                        }
                    } elseif (is_string($ansMeta)) {
                        $dec = json_decode($ansMeta, true);
                        if (is_array($dec)) $userVals = array_values($dec);
                    }

                    $count = max(count($paragraphs), count($userVals), count($correct));
                    for ($i = 0; $i < $count; $i++) {
                        $totalItems++;
                        $raw = $userVals[$i] ?? null;

                        // Resolve user text: support numeric index, option id, or raw text
                        $userText = '';
                        if ($raw !== null && trim((string)$raw) !== '') {
                            $sKey = (string)$raw;
                            // prefer lookup by index
                            if (isset($optByIndex[$sKey])) {
                                $userText = $optByIndex[$sKey];
                            } elseif (isset($optById[$sKey])) {
                                $userText = $optById[$sKey];
                            } elseif (isset($optByIndex[(int)$sKey])) {
                                $userText = $optByIndex[(int)$sKey] ?? '';
                            } else {
                                // fallback to raw string
                                $userText = (string)$raw;
                            }
                        }

                        $corrRaw = $correct[$i] ?? null;
                        $corrText = '';
                        if ($corrRaw !== null && trim((string)$corrRaw) !== '') {
                            $cKey = (string)$corrRaw;
                            if (isset($optByIndex[$cKey])) $corrText = $optByIndex[$cKey];
                            elseif (isset($optById[$cKey])) $corrText = $optById[$cKey];
                            elseif (isset($optByIndex[(int)$cKey])) $corrText = $optByIndex[(int)$cKey] ?? '';
                            else $corrText = (string)$corrRaw;
                        }

                        if ($userText !== '' && $corrText !== '' && mb_strtolower(trim($userText)) === mb_strtolower(trim($corrText))) $correctItems++;
                    }
                    continue;
                }

                // default: treat as single-item question
                $totalItems++;
                $ans = $answers->get($q->id);
                if ($ans && ($ans->is_correct ?? false)) $correctItems++;
            }

            $scorePercentage = $totalItems > 0 ? round(($correctItems / $totalItems) * 100, 2) : 0;
            $attempt->update([
                'total_questions' => $totalItems,
                'correct_answers' => $correctItems,
                'score_percentage' => $scorePercentage
            ]);
        } catch (\Throwable $e) {
            Log::error('recompute totals failed: ' . $e->getMessage());
        }

    // debug logging removed

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

    /**
     * Lấy danh sách câu hỏi trong một phần của bộ đề
     */
    public function partQuestions(Attempt $attempt)
    {
        // chỉ dành cho chế độ học hoặc khi được yêu cầu là toàn bộ phần
        if (($attempt->metadata['mode'] ?? 'learning') !== 'learning') {
            abort(403);
        }

        $order = $attempt->metadata['question_order'] ?? [];
        $questions = \App\Models\Question::whereIn('id', $order)
            ->orderByRaw("FIELD(id, " . implode(',', $order) . ")")
            ->get();

        $payload = $questions->map(function($q){
            $meta = $q->metadata ?? [];

            // chuẩn hóa cho phần 1
            if (($q->part ?? ($meta['part'] ?? null)) == 1) {
                $meta = array_merge([
                    'paragraphs' => $meta['paragraphs'] ?? [$q->content ?? $q->title],
                    'blank_keys' => $meta['blank_keys'] ?? ($meta['blank_keys'] ?? []),
                    'choices' => $meta['choices'] ?? ($meta['options'] ?? []),
                    'correct_answers' => $meta['correct_answers'] ?? ($meta['answers'] ?? [])
                ], $meta);
            }

            // đảm bảo các phần khác có các khóa mong đợi (ví dụ)
            if (($q->part ?? ($meta['part'] ?? null)) == 2) {
                $meta['sentences'] = $meta['sentences'] ?? $meta['items'] ?? [];
                $meta['correct_order'] = $meta['correct_order'] ?? $meta['correct'] ?? [];
            }

            return [
                'id' => $q->id,
                'part' => $q->part ?? ($meta['part'] ?? null),
                'type' => $meta['type'] ?? 'unknown',
                'metadata' => $meta,
            ];
        });

        return response()->json(['questions' => $payload]);
    }

    /**
     * Persist a batch of answers for a reading attempt and optionally finalize scoring.
     * Expects JSON: { answers: { questionId: { selected: <index|value>, is_correct: bool } }, final: bool }
     */
    public function batchSubmit(Request $request, Attempt $attempt)
    {
        if ($attempt->user_id !== Auth::id()) abort(403);

        $payload = $request->input('answers', []);
        $final = $request->boolean('final', false);

        // initialize totals in case of early failure
        $total = 0;
        $correct = 0;

        // defensive: ensure payload is an array
        if (!is_array($payload)) {
            return response()->json(['success' => false, 'message' => 'Invalid payload'], 422);
        }

        try {
            DB::transaction(function() use ($attempt, $payload, $final, &$total, &$correct) {
                $total = 0;
                $correct = 0;

                foreach ($payload as $questionId => $entry) {
                    $questionId = (int)$questionId;
                    // normalize entry
                    $entry = is_array($entry) ? $entry : ['selected' => $entry];
                    $selected = $entry['selected'] ?? null;
                    $isCorrect = array_key_exists('is_correct', $entry) ? (bool)$entry['is_correct'] : null;

                    if (is_null($isCorrect)) {
                        try {
                            $q = Question::find($questionId);
                            $g = $this->gradeAnswer($q, ['selected' => $selected]);
                            $isCorrect = $g['is_correct'] ?? false;
                        } catch (\Exception $e) {
                            $isCorrect = false;
                        }
                    }

                    // selected_option_id should be integer or null; store other selected data inside metadata
                    $selectedOptionId = null;
                    if (is_scalar($selected) && is_numeric($selected)) {
                        $selectedOptionId = (int)$selected;
                    }

                    $metaToStore = $entry;
                    // ensure metadata does not include big objects for DB int fields
                    AttemptAnswer::updateOrCreate(
                        ['attempt_id' => $attempt->id, 'question_id' => $questionId],
                        ['selected_option_id' => $selectedOptionId, 'is_correct' => $isCorrect, 'metadata' => $metaToStore]
                    );

                    $total += 1;
                    if ($isCorrect) $correct += 1;
                }

                $scorePercentage = $total > 0 ? round(($correct / $total) * 100, 2) : 0;

                $updateData = [
                    'total_questions' => $total,
                    'correct_answers' => $correct,
                    'score_percentage' => $scorePercentage,
                    'score_points' => $correct
                ];

                if ($final) {
                    $updateData['status'] = 'submitted';
                    $updateData['submitted_at'] = now();
                }

                $attempt->update($updateData);
            });
        } catch (\Throwable $e) {
            // Log and return JSON error to avoid HTML error page
            Log::error('batchSubmit failed for attempt ' . $attempt->id . ': ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['success' => false, 'message' => 'Server error while saving answers'], 500);
        }

        $response = ['success' => true, 'message' => 'Batch saved', 'submitted' => (bool)$final, 'total' => $total ?? 0, 'correct' => $correct ?? 0];
        if ($final) {
            $response['redirect'] = route('reading.practice.result', $attempt);
        }

        return response()->json($response);
    }

    /**
     * Grade an answer using question metadata. Returns ['is_correct' => bool, 'correct_data' => mixed]
     */
    private function gradeAnswer(Question $question, $answerMeta)
    {
        $meta = $question->metadata ?? [];
        $part = $question->part ?? ($meta['part'] ?? null);
        $result = ['is_correct' => false, 'correct_data' => null];

        try {
            if ($part == 1) {
                $correct = $meta['correct_answers'] ?? ($meta['answers'] ?? []);
                $selected = $answerMeta['selected'] ?? $answerMeta ?? [];
                $vals = is_array($selected) ? array_values($selected) : [$selected];
                $result['is_correct'] = json_encode(array_values($vals)) === json_encode(array_values($correct));
                $result['correct_data'] = $correct;
                return $result;
            }

            if ($part == 2) {
                $correctOrder = $meta['correct_order'] ?? $meta['correct'] ?? [];
                $selected = $answerMeta['selected']['order'] ?? $answerMeta['selected'] ?? $answerMeta ?? [];
                $result['is_correct'] = json_encode(array_values($selected)) === json_encode(array_values($correctOrder));
                $result['correct_data'] = $correctOrder;
                return $result;
            }

            if ($part == 3) {
                $correct = $meta['answers'] ?? $meta['correct'] ?? [];
                $selected = $answerMeta['selected'] ?? $answerMeta ?? [];
                $vals = is_array($selected) ? array_values($selected) : [$selected];
                $result['is_correct'] = json_encode(array_values($vals)) === json_encode(array_values($correct));
                $result['correct_data'] = $correct;
                return $result;
            }

            if ($part == 4) {
                $correct = $meta['correct'] ?? [];
                $selected = $answerMeta['selected'] ?? $answerMeta ?? [];
                $vals = is_array($selected) ? array_values($selected) : [$selected];
                sort($vals);
                $expected = is_array($correct) ? $correct : [$correct];
                sort($expected);
                $result['is_correct'] = json_encode($vals) === json_encode($expected);
                $result['correct_data'] = $correct;
                return $result;
            }
        } catch (\Throwable $e) {
            // ignore and return false
        }

        return $result;
    }

}
