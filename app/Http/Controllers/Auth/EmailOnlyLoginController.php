<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Str;

class EmailOnlyLoginController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required','email']
        ]);

        $user = User::firstOrCreate(
            ['email' => strtolower($data['email'])],
            [
                'name' => explode('@', $data['email'])[0],
                'password' => bcrypt(Str::random(16)),
                'is_active' => true,
            ]
        );

        Auth::login($user, true);
        return redirect()->intended(route('student.quizzes.index'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
