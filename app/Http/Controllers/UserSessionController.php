<?php

namespace App\Http\Controllers;

use App\Models\UserSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserSessionController extends Controller
{
    /**
     * Hiển thị danh sách các phiên đăng nhập của người dùng hiện tại
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $sessions = UserSession::where('user_id', $user->id)
            ->orderBy('last_active_at', 'desc')
            ->get();
            
        // Đánh dấu phiên hiện tại
        $currentSessionId = $this->getCurrentDeviceFingerprint($request);
        
        return view('profile.sessions', compact('sessions', 'currentSessionId'));
    }
    
    /**
     * Đăng xuất khỏi một thiết bị cụ thể
     */
    public function destroy(Request $request, UserSession $session)
    {
        // Xác thực rằng phiên này thuộc về người dùng hiện tại
        if ($session->user_id !== $request->user()->id) {
            return redirect()->route('profile.sessions')
                ->with('error', 'Bạn không có quyền đăng xuất khỏi thiết bị này.');
        }
        
        // Kiểm tra xem đây có phải là phiên hiện tại không
        $currentSessionId = $this->getCurrentDeviceFingerprint($request);
        if ($session->device_fingerprint === $currentSessionId) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return redirect()->route('login')
                ->with('status', 'Bạn đã đăng xuất khỏi thiết bị này.');
        }
        
        // Đăng xuất khỏi thiết bị khác
        $deviceName = $session->device_name;
        $session->delete();
        
        return redirect()->route('profile.sessions')
            ->with('status', "Đã đăng xuất khỏi thiết bị: {$deviceName}");
    }
    
    /**
     * Đăng xuất khỏi tất cả các thiết bị khác
     */
    public function logoutOtherDevices(Request $request)
    {
        $user = $request->user();
        $currentSessionId = $this->getCurrentDeviceFingerprint($request);
        
        UserSession::where('user_id', $user->id)
            ->where('device_fingerprint', '!=', $currentSessionId)
            ->delete();
            
        return redirect()->route('profile.sessions')
            ->with('status', 'Đã đăng xuất khỏi tất cả các thiết bị khác.');
    }
    
    /**
     * Lấy fingerprint của thiết bị hiện tại
     */
    private function getCurrentDeviceFingerprint(Request $request)
    {
        $userAgent = $request->userAgent();
        $ipAddress = $request->ip();
        $sessionId = $request->session()->getId();
        
        return hash('sha256', $userAgent . $ipAddress . substr($sessionId, 0, 8));
    }
}
