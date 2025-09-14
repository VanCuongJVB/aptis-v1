<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserSession;
use App\Models\AccessLog;
use Illuminate\Support\Str;
use Illuminate\Http\Cookie\CookieJar;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cookie;

class PasswordLoginController extends Controller
{
    public function show(){ return view('auth.login'); }

    public function login(Request $request)
    {
        $credentials = $request->validate(['email'=>['required','email'],'password'=>['required']]);

        if (Auth::attempt($credentials, true)) {
            $request->session()->regenerate();
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

            // If this is a new device (no existing device cookie or fingerprint not in active list)
            // and the user already has max active devices, revoke the oldest distinct device sessions
            // so the new device can take its place. This is friendlier than blocking login.
            $isNewDevice = (!$existingDeviceId || !in_array($fingerprintCandidate, $activeFingerprints));
            if ($isNewDevice && count($activeFingerprints) >= $max) {
                // Determine how many distinct fingerprints we need to revoke to make room (usually 1)
                $toRevoke = count($activeFingerprints) - $max + 1;

                // Get distinct active fingerprints ordered by oldest last_active_at
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
                    // Load the actual session rows to be revoked so we can also delete
                    // their corresponding Laravel session rows (if using database driver)
                    $toRevokeSessions = UserSession::where('user_id', Auth::id())
                        ->whereIn('device_fingerprint', $revokeList)
                        ->whereNull('revoked_at')
                        ->get();

                    foreach ($toRevokeSessions as $rs) {
                        try {
                            if ($rs->session_id) {
                                if (config('session.driver') === 'database') {
                                    DB::table('sessions')->where('id', $rs->session_id)->delete();
                                } elseif (config('session.driver') === 'file') {
                                    try { @unlink(storage_path('framework/sessions/' . $rs->session_id)); } catch (\Throwable $e) {}
                                }
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

            return redirect()->intended(route('student.quizzes.index'))->withCookie($cookie);
        }
        return back()->withErrors(['email'=>'Email hoặc mật khẩu không đúng.'])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        $sessionId = $request->session()->getId();

        // Mark user session revoked/inactive
        try {
            if ($sessionId) {
                $us = UserSession::where('session_id', $sessionId)->first();
                if ($us) {
                    $us->update(['revoked_at' => now(), 'is_active' => false]);
                    try {
                        if (config('session.driver') === 'database' && $us->session_id) {
                            DB::table('sessions')->where('id', $us->session_id)->delete();
                        }
                    } catch (\Throwable $e) {
                        // ignore
                    }
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }

        try { AccessLog::log(optional($user)->id, 'logout', ['session_id' => $sessionId]); } catch (\Throwable $e) {}

        // Clear remember token and device cookie
        try {
            if ($user) {
                $user->setRememberToken(null);
                $user->save();
            }
        } catch (\Throwable $e) {}

        Cookie::queue(Cookie::forget('device_id'));

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
