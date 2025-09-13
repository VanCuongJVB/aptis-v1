<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\Attempt;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Hiển thị dashboard cho học viên
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $quizzes = Quiz::published()->orderBy('id', 'desc')->get();

        // Lấy thông tin gói truy cập
        $expiresAt = $user->access_expires_at ? Carbon::parse($user->access_expires_at) : null;

        // compute integer days and remaining hours until expiry
        $now = now();
        $daysLeft = null;
        $hoursLeft = null;

        if ($expiresAt) {
            // use diffInDays/diffInHours to get integer values
            $daysLeft = $now->diffInDays($expiresAt, false);

            if ($daysLeft <= 0) {
                // expired or less than 1 day remaining -> show 0
                $daysLeft = 0;
                $hoursLeft = 0;
            } else {
                $totalHours = $now->diffInHours($expiresAt, false);
                $hoursLeft = $totalHours - ($daysLeft * 24);
                if ($hoursLeft < 0) $hoursLeft = 0;
            }
        }

        $accessInfo = [
            'status'     => $user->is_active ? 'active' : 'inactive',
            'expires_at' => $expiresAt ? $expiresAt->format('d/m/Y H:i') : null,
            'days_left'  => is_null($daysLeft) ? null : (int)$daysLeft,
            'hours_left' => is_null($hoursLeft) ? null : (int)$hoursLeft,
            'percentage' => $this->calculateAccessPercentage($user),
        ];

        // Lấy lịch sử làm bài gần đây
        $recentAttempts = Attempt::where('user_id', $user->id)
            ->with('quiz')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Thống kê số lượng bài làm theo kỹ năng
        $attemptStats = Attempt::where('user_id', $user->id)
            ->selectRaw('quiz_id, COUNT(*) as attempt_count')
            ->with('quiz')
            ->groupBy('quiz_id')
            ->get()
            ->groupBy(function ($item) {
                return $item->quiz->skill;
            });

        return view('student.dashboard', compact('user', 'quizzes', 'accessInfo', 'recentAttempts', 'attemptStats'));
    }

    public function show(Quiz $quiz)
    {
        abort_unless($quiz->is_published, 404);
        $quiz->load(['questions.options']);
        return view('student.quizzes.take', compact('quiz'));
    }

    /**
     * Tính phần trăm thời gian truy cập đã sử dụng
     */
    private function calculateAccessPercentage($user)
    {
        if (!$user->access_expires_at) {
            return null; // Không giới hạn thời gian
        }

        // Giả sử rằng gói đăng ký có thời hạn 30, 90 hoặc 180 ngày
        $createdAt = $user->created_at;
        $expiresAt = $user->access_expires_at;

        // Tính tổng số ngày của gói
        $totalDays = $createdAt->diffInDays($expiresAt);

        // Tính số ngày đã sử dụng
        $usedDays = $createdAt->diffInDays(now());

        // Tính phần trăm đã sử dụng
        $percentage = min(100, round(($usedDays / $totalDays) * 100));

        return $percentage;
    }
}
