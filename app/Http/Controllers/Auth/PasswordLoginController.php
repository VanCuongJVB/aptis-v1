<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserSession;
use App\Models\AccessLog;

class PasswordLoginController extends Controller
{
    public function show(){ return view('auth.login'); }

    public function login(Request $request)
    {
        $credentials = $request->validate(['email'=>['required','email'],'password'=>['required']]);

        if (Auth::attempt($credentials, true)) {
            $request->session()->regenerate();
            $sessionId = $request->session()->getId();
            $fingerprint = sha1(($request->userAgent() ?? 'ua').'|'.($request->ip() ?? 'ip'));
            UserSession::updateOrCreate(
                ['session_id'=>$sessionId],
                [
                    'user_id'=>Auth::id(),
                    'device_fingerprint'=>$fingerprint,
                    'user_agent'=>substr($request->userAgent() ?? '', 0, 1024),
                    'ip_address'=>$request->ip(),
                    'last_activity_at'=>now(),
                    'revoked_at'=>null,
                ]
            );
            AccessLog::log(Auth::id(), 'login', ['session_id'=>$sessionId]);
            return redirect()->intended(route('student.quizzes.index'));
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
