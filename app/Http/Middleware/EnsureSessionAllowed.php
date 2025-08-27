<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\UserSession;
use App\Models\AccessLog;

class EnsureSessionAllowed
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        if (!$user) return $next($request);

        $sessionId = $request->session()->getId();
        $fingerprint = sha1(($request->userAgent() ?? 'ua'). '|' . ($request->ip() ?? 'ip'));

        UserSession::updateOrCreate(
            ['session_id' => $sessionId],
            [
                'user_id' => $user->id,
                'device_fingerprint' => $fingerprint,
                'user_agent' => substr($request->userAgent() ?? '', 0, 1024),
                'ip_address' => $request->ip(),
                'last_activity_at' => now(),
                'revoked_at' => null,
            ]
        );

        $fps = UserSession::where('user_id', $user->id)
            ->whereNull('revoked_at')
            ->select('device_fingerprint', DB::raw('MAX(last_activity_at) as la'))
            ->groupBy('device_fingerprint')
            ->orderByDesc('la')
            ->pluck('device_fingerprint')
            ->toArray();

        if (count($fps) > 2) {
            $extras = array_slice($fps, 2);
            $toRevoke = UserSession::where('user_id', $user->id)
                ->whereIn('device_fingerprint', $extras)
                ->whereNull('revoked_at')
                ->get();

            foreach ($toRevoke as $s) {
                $s->update(['revoked_at' => now()]);
                AccessLog::log($user->id, 'session_revoked', ['session_id' => $s->session_id, 'reason' => 'over_device_limit']);
                if (config('session.driver') === 'database') {
                    DB::table('sessions')->where('id', $s->session_id)->delete();
                }
            }
        }

        $isRevoked = UserSession::where('session_id', $sessionId)->whereNotNull('revoked_at')->exists();
        if ($isRevoked) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->withErrors(['email' => 'Phiên đăng nhập của bạn đã bị thu hồi do vượt quá giới hạn thiết bị.']);
        }
        return $next($request);
    }
}
