<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserSession;
use App\Models\AccessLog;

class EnsureSessionNotRevoked
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $user = $request->user();

        // Build fingerprint the same way as the login controller (device_id cookie or ip + userAgent)
        $deviceId = $request->cookie('device_id');
        $fingerprint = sha1(($request->userAgent() ?? 'ua') . '|' . ($deviceId ?? $request->ip()));

        // Try to find corresponding UserSession. Prefer matching by Laravel session_id if available.
        $sessionRecord = null;
        $laravelSessionId = $request->session()->getId();
        if ($laravelSessionId) {
            $sessionRecord = UserSession::where('session_id', $laravelSessionId)->first();
        }

        if (!$sessionRecord) {
            $sessionRecord = UserSession::where('user_id', $user->id)
                ->where('device_fingerprint', $fingerprint)
                ->orderBy('last_active_at', 'desc')
                ->first();
        }

        if ($sessionRecord) {
            // If revoked or marked inactive, forcibly log out this user on this request.
            if ($sessionRecord->revoked_at || !$sessionRecord->is_active) {
                try { AccessLog::log($user->id, 'forced_logout_middleware', ['session_id' => $sessionRecord->session_id]); } catch (\Throwable $e) {}

                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->withErrors(['email' => 'Phiên đăng nhập đã bị thu hồi. Vui lòng đăng nhập lại.']);
            }

            // Update last_active and ensure session_id is recorded
            $sessionRecord->update([
                'last_active_at' => now(),
                'is_active' => true,
                'session_id' => $laravelSessionId ?? $sessionRecord->session_id,
            ]);
        }

        return $next($request);
    }
}
