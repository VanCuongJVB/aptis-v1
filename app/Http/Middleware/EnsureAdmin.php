<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        if (!$user || !$user->is_admin) abort(403, 'Chỉ dành cho quản trị viên');
        return $next($request);
    }
}
