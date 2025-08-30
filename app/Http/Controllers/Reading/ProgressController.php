<?php

namespace App\Http\Controllers\Reading;

use App\Http\Controllers\Controller;
use App\Models\Attempt;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProgressController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Show progress overview.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get overall statistics
        $attempts = Attempt::where('user_id', $user->id)
            ->with(['quiz', 'items'])
            ->get();

        $stats = [
            'total_attempts' => $attempts->count(),
            'average_score' => round($attempts->avg('score') * 100, 1),
            'total_time' => $attempts->sum(function ($attempt) {
                return $attempt->completed_at?->diffInMinutes($attempt->created_at) ?? 0;
            }),
            'by_part' => []
        ];

        // Calculate statistics for each part
        foreach (range(1, 4) as $part) {
            $partAttempts = $attempts->filter(function ($attempt) use ($part) {
                return $attempt->items->first()?->question->part === $part;
            });

            if ($partAttempts->isNotEmpty()) {
                $stats['by_part'][$part] = [
                    'attempts' => $partAttempts->count(),
                    'average_score' => round($partAttempts->avg('score') * 100, 1),
                    'completed_sets' => $partAttempts->where('status', 'completed')->count()
                ];
            }
        }

        return view('student.reading.progress.index', [
            'stats' => $stats,
            'recentAttempts' => $attempts->take(5)
        ]);
    }

    /**
     * Get detailed statistics.
     */
    public function stats()
    {
        $user = Auth::user();
        
        // Get statistics for last 30 days
        $dailyStats = Attempt::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('AVG(score) * 100 as average_score'),
                DB::raw('COUNT(*) as attempts'),
                DB::raw('SUM(TIMESTAMPDIFF(MINUTE, created_at, completed_at)) as total_time')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Get statistics by question type
        $typeStats = DB::table('attempt_items')
            ->join('attempts', 'attempts.id', '=', 'attempt_items.attempt_id')
            ->join('questions', 'questions.id', '=', 'attempt_items.question_id')
            ->where('attempts.user_id', $user->id)
            ->select(
                'questions.type',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(attempt_items.is_correct) as correct')
            )
            ->groupBy('questions.type')
            ->get();

        return view('student.reading.progress.stats', [
            'dailyStats' => $dailyStats,
            'typeStats' => $typeStats
        ]);
    }

    /**
     * Show attempt history.
     */
    public function history(Request $request)
    {
        $user = Auth::user();
        
        $attempts = Attempt::where('user_id', $user->id)
            ->when($request->part, function ($query, $part) {
                return $query->whereHas('items.question', function ($q) use ($part) {
                    $q->where('part', $part);
                });
            })
            ->when($request->type, function ($query, $type) {
                return $query->where('mode', $type);
            })
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->with(['quiz', 'items.question'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('student.reading.progress.history', [
            'attempts' => $attempts
        ]);
    }
}
