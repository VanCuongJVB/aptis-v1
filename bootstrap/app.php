<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin.role' => \App\Http\Middleware\AdminRoleMiddleware::class,
            'student.access' => \App\Http\Middleware\StudentAccessMiddleware::class,
            'device.limit' => \App\Http\Middleware\DeviceLimitMiddleware::class,
            'account.active' => \App\Http\Middleware\EnsureAccountActive::class, // Đăng ký alias cho middleware kiểm tra hết hạn
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
