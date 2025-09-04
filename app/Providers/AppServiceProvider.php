<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\Models\UserSession;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Không cần đăng ký thêm route ở đây vì đã được xử lý trong web.php
        
        // Set up scheduled cleanup of inactive sessions
        if (Schema::hasTable('user_sessions')) {
            // Clean up inactive sessions older than 30 days
            UserSession::where('last_active_at', '<', Carbon::now()->subDays(30))
                ->orWhere('is_active', false)
                ->delete();
        }
    }
}
