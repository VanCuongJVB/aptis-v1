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
        $data = [
            'questions' => Question::with(['quiz', 'readingSet'])->orderBy('id', 'desc')->paginate(25),
        ];

        return view('admin.quizzes.questions', $data);
    }
}
