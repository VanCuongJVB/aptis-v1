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
        if ($setId) {
            // validate set belongs to quiz
            $set = \App\Models\ReadingSet::where('id', $setId)->where('quiz_id', $quiz->id)->first();
            if ($set) {
                $metadata['reading_set_id'] = $set->id;
            }
        }

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
        
        // Lấy câu hỏi theo vị trí
        $question = $attempt->quiz->questions()
            ->orderBy('order')
            ->skip($position - 1)
            ->first();
            
        if (!$question) {
            return redirect()->route('reading.practice.result', $attempt);
        }
        
        // Lấy câu trả lời trước đó nếu có
        $answer = AttemptAnswer::where('attempt_id', $attempt->id)
            ->where('question_id', $question->id)
            ->first();
        
        // Lấy thông tin tổng quan về bài làm
        $totalQuestions = $attempt->quiz->questions()->count();
        $answeredCount = AttemptAnswer::where('attempt_id', $attempt->id)->count();
        
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
        
        // Validate dữ liệu
        $validated = $request->validate([
            'option_id' => 'required|exists:options,id'
        ]);
        
        // Kiểm tra xem option có thuộc câu hỏi không
        $option = Option::find($validated['option_id']);
        if ($option->question_id !== $question->id) {
            return back()->with('error', 'Lựa chọn không hợp lệ');
        }
        
        // Lưu hoặc cập nhật câu trả lời
        AttemptAnswer::updateOrCreate(
            [
                'attempt_id' => $attempt->id,
                'question_id' => $question->id
            ],
            [
                'option_id' => $validated['option_id'],
                'is_correct' => $option->is_correct
            ]
        );
        
        // Nếu là request Ajax, trả về JSON response
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã lưu câu trả lời',
                'is_correct' => $option->is_correct
            ]);
        }
        
        // Xác định vị trí hiện tại của câu hỏi
        $currentPosition = $attempt->quiz->questions()
            ->orderBy('order')
            ->pluck('id')
            ->search($question->id) + 1;
        
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
    public function finishAttempt(Attempt $attempt)
    {
        // Kiểm tra quyền truy cập
        if ($attempt->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền truy cập lượt làm bài này');
        }
        
        // Nếu bài làm đã hoàn thành, chuyển hướng đến trang kết quả
        if ($attempt->isSubmitted()) {
            return redirect()->route('reading.practice.result', $attempt);
        }
        
        // Tính điểm
        $totalQuestions = $attempt->quiz->questions()->count();
        $correctAnswers = AttemptAnswer::where('attempt_id', $attempt->id)
            ->where('is_correct', true)
            ->count();
        
        $scorePercentage = $totalQuestions > 0 ? round(($correctAnswers / $totalQuestions) * 100, 2) : 0;
        
        // Cập nhật lượt làm bài
        $attempt->update([
            'status' => 'submitted',
            'submitted_at' => now(),
            'total_questions' => $totalQuestions,
            'correct_answers' => $correctAnswers,
            'score_percentage' => $scorePercentage,
            'score_points' => $correctAnswers
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
        $questions = $attempt->quiz->questions()
            ->orderBy('order')
            ->with(['options' => function($query) {
                $query->orderBy('id');
            }])
            ->get();
            
        $answers = AttemptAnswer::where('attempt_id', $attempt->id)
            ->with('option')
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
