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

        if (!$user->is_active) {
            return redirect()->route('inactive')->with('reason', 'Tài khoản đã bị tắt.');
        }
        $now = now();
        if ($user->access_starts_at && $now->lt($user->access_starts_at)) {
            return redirect()->route('inactive')->with('reason', 'Chưa đến thời gian được phép truy cập.');
        }
        if ($user->access_ends_at && $now->gt($user->access_ends_at)) {
            return redirect()->route('inactive')->with('reason', 'Tài khoản đã hết hạn truy cập.');
        }

        return $next($request);
    }
}
