<?php

use App\Http\Controllers\Student\DashboardController;
use App\Http\Controllers\Student\AttemptController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Student Routes
|--------------------------------------------------------------------------
|
| Đây là nơi định nghĩa các route dành cho học sinh.
| Tất cả các route đều sử dụng middleware 'student.access'
|
*/

Route::middleware(['auth', 'student.access'])->group(function () {
    // Dashboard
    Route::prefix('student')->name('student.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        
        // Quizzes
        Route::prefix('quizzes')->name('quizzes.')->group(function () {
            Route::get('/', [DashboardController::class, 'index'])->name('index');
            Route::get('/{quiz}', [DashboardController::class, 'show'])->name('show');
        });
        
        // Attempts
        Route::prefix('attempts')->name('attempts.')->group(function () {
            Route::post('/{quiz}', [AttemptController::class, 'store'])->name('store');
            Route::get('/{attempt}/results', [AttemptController::class, 'results'])->name('results');
            Route::get('/history', [AttemptController::class, 'history'])->name('history');
        });

    // Backward compatibility: alias route name used in views
    Route::post('/quizzes/{quiz}/submit', [AttemptController::class, 'submit'])->name('quizzes.submit');
    });
});
