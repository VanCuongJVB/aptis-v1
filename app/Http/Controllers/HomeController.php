<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\User;
use App\Models\Attempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Hiển thị trang chủ chính của ứng dụng
     */
    public function index()
    {
        // Kiểm tra role của người dùng để chuyển hướng
        if (Auth::check()) {
            if (Auth::user()->role === 'admin') {
                return $this->adminDashboard();
            } else {
                return $this->studentDashboard();
            }
        }
        
        // Hiển thị trang chào mừng mặc định cho khách
        return view('welcome');
    }
    
    /**
     * Hiển thị dashboard cho admin
     */
    private function adminDashboard()
    {
        // Thống kê cơ bản cho admin
        $stats = [
            'totalStudents' => User::where('role', 'student')->count(),
            'totalQuizzes' => Quiz::count(),
            'totalAttempts' => Attempt::count(),
            'recentAttempts' => Attempt::with(['user', 'quiz'])
                ->latest()
                ->take(5)
                ->get(),
        ];
        
        return view('admin.dashboard', ['stats' => $stats]);
    }
    
    /**
     * Chuyển hướng học sinh đến dashboard học sinh
     */
    private function studentDashboard()
    {
        return redirect()->route('student.dashboard');
    }
}
