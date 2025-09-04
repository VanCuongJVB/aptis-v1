<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Http\Request;
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
        UserSession::where('user_id', $user->id)->delete();
        
        return redirect()->route('admin.users.sessions', $user)
            ->with('success', 'Đã đăng xuất khỏi tất cả thiết bị.');
    }
    
    /**
     * Đăng xuất khỏi một thiết bị cụ thể
     */
    public function logoutDevice(UserSession $session)
    {
        $user = $session->user;
        $deviceName = $session->device_name;
        
        $session->delete();
        
        return redirect()->route('admin.users.sessions', $user)
            ->with('success', "Đã đăng xuất khỏi thiết bị: {$deviceName}");
    }
}
