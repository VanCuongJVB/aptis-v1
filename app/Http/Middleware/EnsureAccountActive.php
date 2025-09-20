<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsureAccountActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        $now = now();
        // Ưu tiên kiểm tra hết hạn truy cập trước
        if ($user->access_ends_at && $now->gt($user->access_ends_at)) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->with('error', 'Thời hạn truy cập của bạn đã hết. Vui lòng liên hệ quản trị viên để gia hạn.');
        }
       
        // Sau đó mới kiểm tra active
        if (!$user->is_active) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->with('error', 'Tài khoản của bạn đã bị vô hiệu hóa. Vui lòng liên hệ quản trị viên.');
        }
        $now = now();
        if ($user->access_starts_at && $now->lt($user->access_starts_at)) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->with('error', 'Chưa đến thời gian được phép truy cập.');
        }
        if ($user->access_ends_at && $now->gt($user->access_ends_at)) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->with('error', 'Thời hạn truy cập của bạn đã hết. Vui lòng liên hệ quản trị viên để gia hạn.');
        }

        return $next($request);
    }
}
