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
            // and the user already has max active devices, deny login (strict policy).
            if ((!$existingDeviceId || !in_array($fingerprintCandidate, $activeFingerprints)) && count($activeFingerprints) >= $max) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->withErrors(['email' => "Tài khoản chỉ được đăng nhập đồng thời tối đa {$max} thiết bị. Vui lòng đăng xuất các thiết bị khác hoặc liên hệ quản trị."])->onlyInput('email');
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
        AccessLog::log(optional(Auth::user())->id, 'logout', ['session_id'=>$request->session()->getId()]);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
