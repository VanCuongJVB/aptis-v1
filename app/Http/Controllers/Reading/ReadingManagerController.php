<?php

namespace App\Http\Controllers\Reading;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\Option;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ReadingManagerController extends Controller
{
    /**
     * Hiển thị trang tổng quan quản lý Reading
     */
    public function index()
    {
        // Phân chia theo các phần của APTIS Reading
        $parts = [
            1 => 'Sentence Comprehension',
            2 => 'Text Cohesion',
            3 => 'Reading Comprehension',
            4 => 'Long Text Reading'
        ];
        
        // Thống kê số lượng bộ đề và câu hỏi cho từng phần
        $stats = [];
        foreach ($parts as $part => $name) {
            $quizCount = Quiz::where('skill', 'reading')
                          ->where('part', $part)
                          ->count();
                          
            $questionCount = Question::whereHas('quiz', function($query) use ($part) {
                                $query->where('skill', 'reading')
                                      ->where('part', $part);
                            })->count();
                            
            $stats[$part] = [
                'name' => $name,
                'quizzes' => $quizCount,
                'questions' => $questionCount
            ];
        }
        
        return view('admin.reading.index', [
            'parts' => $parts,
            'stats' => $stats
        ]);
    }
    
    /**
     * Hiển thị tất cả các bộ đề thuộc một phần cụ thể
     */
    public function showPart($part, Request $request)
    {
        // Kiểm tra part hợp lệ
        if ($part !== 'all' && ($part < 1 || $part > 4)) {
            return redirect()->route('admin.reading.index')
                ->with('error', 'Phần không hợp lệ');
        }
        
        $partNames = [
            1 => 'Sentence Comprehension',
            2 => 'Text Cohesion',
            3 => 'Reading Comprehension',
            4 => 'Long Text Reading'
        ];
        
        $partDescriptions = [
            1 => 'Hiểu nghĩa câu và chọn từ phù hợp để hoàn thành câu',
            2 => 'Hiểu mối liên kết giữa các câu trong đoạn văn',
            3 => 'Đọc hiểu đoạn văn ngắn và trả lời câu hỏi',
            4 => 'Đọc hiểu văn bản dài và trả lời nhiều câu hỏi'
        ];
        
        // Build query
        $query = Quiz::where('skill', 'reading')
            ->withCount('questions')
            ->withCount(['attempts' => function($q) {
                $q->where('status', 'submitted');
            }]);
            
        if ($part !== 'all') {
            $query->where('part', $part);
        }
        
        // Apply filters
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }
        
        if ($request->filled('status')) {
            if ($request->status === 'published') {
                $query->where('is_published', true);
            } elseif ($request->status === 'draft') {
                $query->where('is_published', false);
            }
        }
        
        // Apply sorting
        $sort = $request->get('sort', 'created_desc');
        switch ($sort) {
            case 'created_asc':
                $query->orderBy('created_at', 'asc');
                break;
            case 'title_asc':
                $query->orderBy('title', 'asc');
                break;
            case 'title_desc':
                $query->orderBy('title', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }
        
        $quizzes = $query->paginate(10);
        
        // Calculate stats
        $statsQuery = Quiz::where('skill', 'reading');
        if ($part !== 'all') {
            $statsQuery->where('part', $part);
        }
        
        $stats = [
            'total_quizzes' => $statsQuery->count(),
            'published_quizzes' => $statsQuery->where('is_published', true)->count(),
            'total_questions' => Question::whereHas('quiz', function($q) use ($part) {
                $q->where('skill', 'reading');
                if ($part !== 'all') {
                    $q->where('part', $part);
                }
            })->count(),
        ];
        $stats['avg_questions'] = $stats['total_quizzes'] > 0 ? round($stats['total_questions'] / $stats['total_quizzes'], 1) : 0;
            
        return view('admin.reading.part', [
            'part' => $part,
            'partName' => $part === 'all' ? 'Tất cả các phần' : $partNames[$part],
            'description' => $part === 'all' ? 'Quản lý toàn bộ các bộ đề Reading' : $partDescriptions[$part],
            'quizzes' => $quizzes,
            'stats' => $stats
        ]);
    }
    
    /**
     * Hiển thị form tạo mới bộ đề Reading
     */
    public function create(int $part)
    {
        // Kiểm tra part hợp lệ
        if ($part < 1 || $part > 4) {
            return redirect()->route('admin.reading.index')
                ->with('error', 'Phần không hợp lệ');
        }
        
        $partNames = [
            1 => 'Sentence Comprehension',
            2 => 'Text Cohesion',
            3 => 'Reading Comprehension',
            4 => 'Long Text Reading'
        ];
        
        return view('admin.reading.create', [
            'part' => $part,
            'partName' => $partNames[$part]
        ]);
    }
    
    /**
     * Lưu bộ đề Reading mới
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'part' => 'required|integer|min:1|max:4',
            'difficulty' => 'required|integer|min:1|max:5',
            'time_limit' => 'nullable|integer|min:0',
            'is_published' => 'boolean'
        ]);
        
        $quiz = Quiz::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? '',
            'skill' => 'reading',
            'part' => $validated['part'],
            'difficulty' => $validated['difficulty'],
            'time_limit' => $validated['time_limit'] ?? 0,
            'is_published' => $request->has('is_published'),
            'created_by' => Auth::id()
        ]);
        
        return redirect()->route('admin.reading.sets.edit', $quiz)
            ->with('success', 'Đã tạo bộ đề thành công. Hãy thêm câu hỏi.');
    }
    
    /**
     * Hiển thị form chỉnh sửa bộ đề
     */
    public function edit(Quiz $quiz)
    {
        // Kiểm tra xem quiz có phải reading quiz không
        if ($quiz->skill !== 'reading') {
            return redirect()->route('admin.reading.index')
                ->with('error', 'Bộ đề không hợp lệ');
        }
        
        $questions = $quiz->questions()
            ->withCount(['options' => function($query) {
                $query->where('is_correct', true);
            }])
            ->orderBy('order')
            ->get();
            
        return view('admin.reading.edit', [
            'quiz' => $quiz,
            'questions' => $questions
        ]);
    }
    
    /**
     * Cập nhật bộ đề
     */
    public function update(Request $request, Quiz $quiz)
    {
        // Kiểm tra xem quiz có phải reading quiz không
        if ($quiz->skill !== 'reading') {
            return redirect()->route('admin.reading.index')
                ->with('error', 'Bộ đề không hợp lệ');
        }
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'difficulty' => 'required|integer|min:1|max:5',
            'time_limit' => 'nullable|integer|min:0',
            'is_published' => 'boolean'
        ]);
        
        $quiz->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? '',
            'difficulty' => $validated['difficulty'],
            'time_limit' => $validated['time_limit'] ?? 0,
            'is_published' => $request->has('is_published')
        ]);
        
        return redirect()->route('admin.reading.sets.edit', $quiz)
            ->with('success', 'Đã cập nhật bộ đề thành công');
    }
    
    /**
     * Xóa bộ đề
     */
    public function destroy(Quiz $quiz)
    {
        // Kiểm tra xem quiz có phải reading quiz không
        if ($quiz->skill !== 'reading') {
            return redirect()->route('admin.reading.index')
                ->with('error', 'Bộ đề không hợp lệ');
        }
        
        // Xóa tất cả câu hỏi và đáp án trước
        foreach ($quiz->questions as $question) {
            $question->options()->delete();
        }
        $quiz->questions()->delete();
        
        // Xóa các lượt làm bài liên quan
        $quiz->attempts()->delete();
        
        // Cuối cùng xóa bộ đề
        $quiz->delete();
        
        return redirect()->route('admin.reading.sets.part', $quiz->part)
            ->with('success', 'Đã xóa bộ đề thành công');
    }
    
    /**
     * Đánh dấu bộ đề là xuất bản/không xuất bản
     */
    public function togglePublish(Quiz $quiz)
    {
        // Kiểm tra xem quiz có phải reading quiz không
        if ($quiz->skill !== 'reading') {
            return redirect()->route('admin.reading.index')
                ->with('error', 'Bộ đề không hợp lệ');
        }
        
        $quiz->update([
            'is_published' => !$quiz->is_published
        ]);
        
        $status = $quiz->is_published ? 'xuất bản' : 'chưa xuất bản';
        
        return redirect()->back()
            ->with('success', "Đã đánh dấu bộ đề là $status");
    }
    
    /**
     * Thay đổi thứ tự câu hỏi trong bộ đề
     */
    public function reorderQuestions(Request $request, Quiz $quiz)
    {
        $validated = $request->validate([
            'question_order' => 'required|array',
            'question_order.*' => 'required|integer|exists:questions,id'
        ]);
        
        // Cập nhật thứ tự cho từng câu hỏi
        foreach ($validated['question_order'] as $order => $questionId) {
            Question::where('id', $questionId)
                ->where('quiz_id', $quiz->id)
                ->update(['order' => $order + 1]); // Thứ tự bắt đầu từ 1
        }
        
        return response()->json(['success' => true]);
    }
}
