<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AudioMediaHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Kiểm tra nếu response là audio file
        $contentType = $response->headers->get('Content-Type', '');
        
        if (str_contains($contentType, 'audio/')) {
            // Thêm các headers cần thiết cho Safari iOS
            $response->header('Accept-Ranges', 'bytes');
            $response->header('Cache-Control', 'public, max-age=31536000, immutable');
            
            // CORS headers
            $response->header('Access-Control-Allow-Origin', '*');
            $response->header('Access-Control-Allow-Methods', 'GET, OPTIONS');
            $response->header('Access-Control-Allow-Headers', 'Content-Type, Range');
            
            // iOS compatibility
            $response->header('X-Content-Type-Options', 'nosniff');
        }

        return $response;
    }
}
