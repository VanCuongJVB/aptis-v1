<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\UserSession;
use App\Models\AccessLog;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Record a user session similar to PasswordLoginController
        try {
            $sessionId = $request->session()->getId();

            // Determine if this request already has a persistent device id cookie.
            $existingDeviceId = $request->cookie('device_id');
            $fingerprintCandidate = sha1(($request->userAgent() ?? 'ua') . '|' . ($existingDeviceId ?? $request->ip()));

            // Count currently active distinct device fingerprints for this user
            $max = (int) config('aptis.sessions.max_devices', 2);
            if ($max < 1) $max = 2;

            $activeFingerprints = UserSession::where('user_id', Auth::id())
                ->whereNull('revoked_at')
                ->where('last_active_at', '>=', now()->subMinutes(30))
                ->select('device_fingerprint')
                ->groupBy('device_fingerprint')
                ->pluck('device_fingerprint')
                ->toArray();

            $isNewDevice = (!$existingDeviceId || !in_array($fingerprintCandidate, $activeFingerprints));
            if ($isNewDevice && count($activeFingerprints) >= $max) {
                $toRevoke = count($activeFingerprints) - $max + 1;
                $oldFingerprints = UserSession::where('user_id', Auth::id())
                    ->whereNull('revoked_at')
                    ->where('last_active_at', '>=', now()->subMinutes(30))
                    ->select('device_fingerprint', DB::raw('MIN(last_active_at) as last_active'))
                    ->groupBy('device_fingerprint')
                    ->orderBy('last_active', 'asc')
                    ->pluck('device_fingerprint')
                    ->toArray();

                $revokeList = array_slice($oldFingerprints, 0, $toRevoke);
                if (!empty($revokeList)) {
                    $toRevokeSessions = UserSession::where('user_id', Auth::id())
                        ->whereIn('device_fingerprint', $revokeList)
                        ->whereNull('revoked_at')
                        ->get();

                    foreach ($toRevokeSessions as $rs) {
                        try {
                            if (config('session.driver') === 'database' && $rs->session_id) {
                                DB::table('sessions')->where('id', $rs->session_id)->delete();
                            }
                        } catch (\Throwable $e) {
                            // ignore deletion errors
                        }

                        $rs->update(['revoked_at' => now(), 'is_active' => false]);
                        try { AccessLog::log(Auth::id(), 'session_revoked', ['device_fingerprint' => $rs->device_fingerprint, 'session_id' => $rs->session_id]); } catch (\Throwable $e) {}
                    }
                }
            }

            // Use or create a persistent device id for this client
            $deviceId = $existingDeviceId ?: Str::uuid()->toString();
            $fingerprint = sha1(($request->userAgent() ?? 'ua') . '|' . ($deviceId ?? $request->ip()));

            UserSession::updateOrCreate(
                ['session_id' => $sessionId],
                [
                    'user_id' => Auth::id(),
                    'device_fingerprint' => $fingerprint,
                    'user_agent' => substr($request->userAgent() ?? '', 0, 1024),
                    'ip_address' => $request->ip(),
                    'last_active_at' => now(),
                    'revoked_at' => null,
                ]
            );

            AccessLog::log(Auth::id(), 'login', ['session_id' => $sessionId]);

            // Persist the device id cookie so subsequent requests produce the same fingerprint.
            $cookie = cookie()->forever('device_id', $deviceId);

            return redirect()->intended(route('dashboard', absolute: false))->withCookie($cookie);
        } catch (\Throwable $e) {
            // If any error occurs during session recording, proceed with the normal redirect
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();
        $sessionId = $request->session()->getId();

        // Try to find the user_session record for this Laravel session id
        try {
            if ($sessionId) {
                $us = UserSession::where('session_id', $sessionId)->first();
            } else {
                $us = null;
            }

            // Fallback to device fingerprint if session row not found
            if (!isset($us) || !$us) {
                $deviceId = $request->cookie('device_id');
                $fingerprint = sha1(($request->userAgent() ?? 'ua') . '|' . ($deviceId ?? $request->ip()));
                $us = UserSession::where('user_id', optional($user)->id)
                    ->where('device_fingerprint', $fingerprint)
                    ->orderBy('last_active_at', 'desc')
                    ->first();
            }

            if ($us) {
                $us->update(['revoked_at' => now(), 'is_active' => false]);

                // If using database session driver, delete Laravel session row so client is invalidated immediately
                try {
                    if (config('session.driver') === 'database' && $us->session_id) {
                        DB::table('sessions')->where('id', $us->session_id)->delete();
                    }
                } catch (\Throwable $e) {
                    // ignore
                }
            }
        } catch (\Throwable $e) {
            // ignore errors while trying to update session record
        }

        // Clear remember token and device cookie
        try {
            if ($user) {
                $user->setRememberToken(null);
                $user->save();
            }
        } catch (\Throwable $e) {}

        try { AccessLog::log(optional($user)->id, 'logout', ['session_id' => $sessionId]); } catch (\Throwable $e) {}

        Cookie::queue(Cookie::forget('device_id'));

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
