<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class StudentAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        // If not authenticated, redirect to login
        if (!$user) {
            return redirect()->route('login');
        }

        // Allow admin users to access everything
        if ($user->role === 'admin') {
            return $next($request);
        }
        
        // Check if user is active
        if (!$user->is_active) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return redirect()->route('login')
                ->with('error', 'Tài khoản của bạn đã bị vô hiệu hóa. Vui lòng liên hệ quản trị viên.');
        }
        
        // Check if access has expired
        if ($user->access_expires_at && $user->access_expires_at->isPast()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return redirect()->route('login')
                ->with('error', 'Thời hạn truy cập của bạn đã hết. Vui lòng liên hệ quản trị viên để gia hạn.');
        }

        // Students should only access test/practice related routes.
        // If a student tries to open other pages, send them to their dashboard.
        if ($user->role === 'student') {
            $route = $request->route();
            $routeName = $route ? $route->getName() : null;

            if (!$routeName || (!preg_match('/(practice|attempt|reading|listening|quiz)/', $routeName) && strpos($routeName, 'student.') !== 0)) {
                return redirect()->route('student.dashboard');
            }
        }
        
        return $next($request);
    }
}
