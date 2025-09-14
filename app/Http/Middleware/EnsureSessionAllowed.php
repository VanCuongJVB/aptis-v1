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
    $deviceId = $request->cookie('device_id');
    $fingerprint = sha1(($request->userAgent() ?? 'ua') . '|' . ($deviceId ?? $request->ip()));

        // Record or update the user session. Use session_id as primary matching key.
        UserSession::updateOrCreate(
            ['session_id' => $sessionId],
            [
                'user_id' => $user->id,
                'session_id' => $sessionId,
                'device_fingerprint' => $fingerprint,
                'user_agent' => substr($request->userAgent() ?? '', 0, 1024),
                'ip_address' => $request->ip(),
                'last_active_at' => now(),
                'revoked_at' => null,
            ]
        );

        $fps = UserSession::where('user_id', $user->id)
            ->whereNull('revoked_at')
                ->select('device_fingerprint', DB::raw('MAX(last_active_at) as la'))
            ->groupBy('device_fingerprint')
            ->orderByDesc('la')
            ->pluck('device_fingerprint')
            ->toArray();

        $max = (int) config('aptis.sessions.max_devices', 2);
        if ($max < 1) $max = 2;

        if (count($fps) > $max) {
            $extras = array_slice($fps, $max);
            $toRevoke = UserSession::where('user_id', $user->id)
                ->whereIn('device_fingerprint', $extras)
                ->whereNull('revoked_at')
                ->get();

            foreach ($toRevoke as $s) {
                $s->update(['revoked_at' => now()]);
                // mark the user as warned about device limit
                try {
                    $user->device_warning = true;
                    $user->save();
                } catch (\Throwable $e) {
                    // ignore save failures here
                }
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
