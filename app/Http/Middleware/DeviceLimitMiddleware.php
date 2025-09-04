<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserSession;
use Symfony\Component\HttpFoundation\Response;

class DeviceLimitMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return redirect()->route('login');
        }
        
        // Generate a unique device fingerprint
        $deviceFingerprint = $this->generateDeviceFingerprint($request);
        
        // Get or create session record
        $userSession = UserSession::firstOrCreate(
            ['device_fingerprint' => $deviceFingerprint],
            [
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'device_name' => $this->getDeviceName($request),
                'is_active' => true,
                'last_active_at' => now(),
            ]
        );
        
        // Update last active time
        if ($userSession->last_active_at->diffInMinutes(now()) > 5) {
            $userSession->update(['last_active_at' => now()]);
        }
        
        // Get active sessions for this user (active in last 30 minutes)
        $activeSessions = UserSession::where('user_id', $user->id)
            ->where('is_active', true)
            ->where('last_active_at', '>=', now()->subMinutes(30))
            ->get();
        
        // If this is a different user on this device, update the session with current user
        if ($userSession->user_id !== $user->id) {
            $userSession->update([
                'user_id' => $user->id,
                'last_active_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'device_name' => $this->getDeviceName($request)
            ]);
        }
        
        // If user already has 2 active devices and this is a different device
        if ($activeSessions->count() > 2 && !$activeSessions->contains('device_fingerprint', $deviceFingerprint)) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return redirect()->route('login')
                ->with('error', 'You have reached the maximum number of active devices (2). Please log out from other devices and try again.');
        }
        
        return $next($request);
    }
    
    /**
     * Generate a unique device fingerprint
     *
     * @param Request $request
     * @return string
     */
    private function generateDeviceFingerprint(Request $request): string
    {
        $userAgent = $request->userAgent();
        $ipAddress = $request->ip();
        $sessionId = $request->session()->getId();
        
        // Create a hash combining user agent, IP, and a portion of the session ID
        // This allows the same browser on the same device to be recognized
        return hash('sha256', $userAgent . $ipAddress . substr($sessionId, 0, 8));
    }
    
    /**
     * Get a friendly device name from user agent
     *
     * @param Request $request
     * @return string
     */
    private function getDeviceName(Request $request): string
    {
        $userAgent = $request->userAgent();
        $device = 'Unknown Device';
        
        // Detect mobile devices
        if (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $userAgent)) {
            $device = 'Mobile Device';
        } 
        // Detect tablets
        elseif (preg_match('/android|ipad|playbook|silk/i', $userAgent)) {
            $device = 'Tablet Device';
        } 
        // Detect desktop
        else {
            $device = 'Desktop Device';
        }
        
        // Detect browser
        if (preg_match('/MSIE/i', $userAgent)) {
            $browser = 'Internet Explorer';
        } elseif (preg_match('/Firefox/i', $userAgent)) {
            $browser = 'Firefox';
        } elseif (preg_match('/Chrome/i', $userAgent)) {
            $browser = 'Chrome';
        } elseif (preg_match('/Safari/i', $userAgent)) {
            $browser = 'Safari';
        } elseif (preg_match('/Opera/i', $userAgent)) {
            $browser = 'Opera';
        } else {
            $browser = 'Unknown Browser';
        }
        
        return $device . ' - ' . $browser;
    }
}
