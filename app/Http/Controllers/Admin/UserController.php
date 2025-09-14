<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSession;
use App\Models\AccessLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UserController extends Controller
{
    public function index()
    {
        $users = User::paginate(20);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,student',
            'is_active' => 'boolean',
            'access_expires_at' => 'nullable|date',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role' => $validated['role'],
            'is_active' => $request->has('is_active'),
            'access_expires_at' => $validated['access_expires_at'] ?? null,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|in:admin,student',
            'is_active' => 'boolean',
            'access_expires_at' => 'nullable|date',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'is_active' => $request->has('is_active'),
            'access_expires_at' => $validated['access_expires_at'] ?? null,
        ]);

        if (!empty($validated['password'])) {
            $user->update(['password' => bcrypt($validated['password'])]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
    
    /**
     * Hiển thị phiên đăng nhập của người dùng
     */
    public function sessions(User $user)
    {
        $sessions = UserSession::where('user_id', $user->id)
            ->orderBy('last_active_at', 'desc')
            ->get();
            
        return view('admin.users.sessions', compact('user', 'sessions'));
    }
    
    /**
     * Đăng xuất tất cả thiết bị của người dùng
     */
    public function logoutAllDevices(User $user)
    {
        $sessions = UserSession::where('user_id', $user->id)->get();
        foreach ($sessions as $s) {
            $s->update(['revoked_at' => now(), 'is_active' => false]);
            // Delete Laravel session row if using database session driver
            try {
                if ($s->session_id) {
                    if (config('session.driver') === 'database') {
                        DB::table('sessions')->where('id', $s->session_id)->delete();
                    } elseif (config('session.driver') === 'file') {
                        // session files are stored in storage/framework/sessions/<id>
                        try {
                            @unlink(storage_path('framework/sessions/' . $s->session_id));
                        } catch (\Throwable $e) {
                            // ignore
                        }
                    }
                }
            } catch (\Throwable $e) {
                // ignore
            }
            try { AccessLog::log($user->id, 'admin_logout_device', ['session_id' => $s->session_id]); } catch (\Throwable $e) {}
        }

        return redirect()->route('admin.users.sessions', $user)
            ->with('success', 'Đã đăng xuất khỏi tất cả thiết bị.');
    }
    
    /**
     * Đăng xuất khỏi một thiết bị cụ thể
     */
    public function logoutDevice(UserSession $session)
    {
        $user = $session->user;
        $deviceName = $session->device_name ?? 'Unknown device';

    // mark revoked and mark inactive, then delete corresponding Laravel session if possible
    $session->update(['revoked_at' => now(), 'is_active' => false]);
        try {
            if ($session->session_id) {
                if (config('session.driver') === 'database') {
                    DB::table('sessions')->where('id', $session->session_id)->delete();
                } elseif (config('session.driver') === 'file') {
                    try {
                        @unlink(storage_path('framework/sessions/' . $session->session_id));
                    } catch (\Throwable $e) {}
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }
    try { AccessLog::log($user->id, 'admin_logout_device', ['session_id' => $session->session_id]); } catch (\Throwable $e) {}
        return redirect()->route('admin.users.sessions', $user)
            ->with('success', "Đã đăng xuất khỏi thiết bị: {$deviceName}");
    }
}
