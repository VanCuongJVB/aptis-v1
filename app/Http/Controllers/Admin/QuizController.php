<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\ReadingSet;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function index()
    {
        $query = Quiz::query();

        // Filter: kỹ năng
        if ($skill = request('skill')) {
            $query->where('skill', $skill);
        }
        // Filter: phần
        if ($part = request('part')) {
            if ($part != 0) {
                $query->where('part', $part);
            }
        }
        // Filter: trạng thái xuất bản
        if (request()->has('published') && in_array(request('published'), ['0', '1'], true)) {
            $query->where('is_published', request('published'));
        }
        // Filter: tìm kiếm tên quiz
        if ($q = trim(request('q', ''))) {
            $query->where('title', 'like', "%$q%");
        }

        $quizzes = $query
            ->where('part', '>', 0) // loại part = 0
            ->orderByDesc('created_at')
            ->paginate(20)
            ->appends(request()->query());

        // Đếm tổng số quiz, set, question toàn hệ thống
        $totalQuizzes = Quiz::count();
        $totalSets = ReadingSet::whereNotNull('quiz_id')->count();
        $totalQuestions = Question::whereNotNull('quiz_id')->count();

        // Đếm số set và question thuộc các quiz trên trang hiện tại
        $currentQuizIds = $quizzes->pluck('id');
        $currentSets = 0;
        $currentQuestions = 0;
        if ($currentQuizIds->count() > 0) {
            $currentSets = ReadingSet::whereIn('quiz_id', $currentQuizIds)->count();
            $currentQuestions = Question::whereIn('quiz_id', $currentQuizIds)->count();
        }

        $data = [
            'quizzes_count' => $totalQuizzes,
            'sets_count' => $totalSets,
            'questions_count' => $totalQuestions,
            'current_sets_count' => $currentSets,
            'current_questions_count' => $currentQuestions,
        ];

        return view('admin.quizzes.index', compact('data', 'quizzes'));
    }

    public function create()
    {
        return view('admin.quizzes.form', ['quiz' => new Quiz()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'skill' => 'required|in:reading,listening',
            'part' => 'required|integer|min:1|max:4',
            'is_published' => 'boolean',
            'duration_minutes' => 'nullable',
            'show_explanation' => 'boolean',
        ]);
        $data['is_published'] = $request->has('is_published');
        $data['show_explanation'] = $request->has('show_explanation');
        Quiz::create($data);
        return redirect()->route('admin.quizzes.index')->with('success', 'Quiz created successfully.');
    }

    public function edit(Quiz $quiz)
    {
        return view('admin.quizzes.form', compact('quiz'));
    }

    public function update(Request $request, Quiz $quiz)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'skill' => 'required|in:reading,listening',
            'part' => 'required|integer|min:1|max:4',
            'is_published' => 'boolean',
            'duration_minutes' => 'nullable',
            'show_explanation' => 'boolean',
        ]);
        $data['is_published'] = $request->has('is_published');
        $data['show_explanation'] = $request->has('show_explanation');
        $quiz->update($data);
        return redirect()->route('admin.quizzes.index')->with('success', 'Quiz updated successfully.');
    }

    public function destroy(Quiz $quiz)
    {
        $quiz->delete();
        return redirect()->route('admin.quizzes.index')->with('success', 'Quiz deleted successfully.');
    }
}
