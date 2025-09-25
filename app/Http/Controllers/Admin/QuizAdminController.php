<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Quiz;
use App\Models\ReadingSet;
use App\Models\Question;
use Illuminate\Support\Facades\DB;

class QuizAdminController extends Controller
{
    /**
     * Show quizzes overview (placeholder) — return data for inspection.
     */
    public function index(Request $request)
    {
        $data = [
            'quizzes_count' => Quiz::count(),
            'published_quizzes' => Quiz::published()->count(),
            'by_skill' => Quiz::select('skill', DB::raw('count(*) as total'))->groupBy('skill')->get(),
            'top_quizzes_by_questions' => Quiz::withCount('questions')->orderBy('questions_count', 'desc')->limit(10)->get(),
            'sets_count' => ReadingSet::count(),
            'questions_count' => Question::count(),
        ]; 

        return view('admin.quizzes.index', $data);
    }

    /**
     * Show sets management (placeholder) — return data for inspection.
     */
    public function sets(Request $request)
    {
        $data = [
            'sets' => ReadingSet::with('quiz')->orderBy('order')->paginate(20),
        ];

        return view('admin.quizzes.sets', $data);
    }

    /**
     * Show questions management (placeholder) — return data for inspection.
     */
    public function questions(Request $request)
    {
        $query = Question::with(['quiz', 'readingSet']);

        // Filter
        if ($request->filled('quiz_id')) {
            $query->where('quiz_id', $request->quiz_id);
        }
        if ($request->filled('reading_set_id')) {
            $query->where('reading_set_id', $request->reading_set_id);
        }
        if ($request->filled('part')) {
            $query->where('part', $request->part);
        }
        if ($request->filled('skill')) {
            $query->where('skill', $request->skill);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function($sub) use ($q) {
                $sub->where('stem', 'like', "%$q%")
                    ->orWhere('title', 'like', "%$q%")
                    ->orWhere('explanation', 'like', "%$q%") ;
            });
        }

        $questions = $query->orderBy('id', 'desc')->paginate(25)->appends($request->all());

        // Dữ liệu cho filter
        $quizzes = \App\Models\Quiz::orderBy('title')->get();
        $sets = \App\Models\ReadingSet::orderBy('title')->get();
        $types = Question::select('type')->distinct()->pluck('type');
        $parts = Question::select('part')->distinct()->pluck('part');
        $skills = Question::select('skill')->distinct()->pluck('skill');

        return view('admin.quizzes.questions', compact('questions', 'quizzes', 'sets', 'types', 'parts', 'skills'));
    }
}
