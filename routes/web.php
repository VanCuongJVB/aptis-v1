<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserSessionController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Đây là nơi định nghĩa các route chung cho toàn bộ ứng dụng web.
| 
*/

// Public Routes
Route::get('/', [HomeController::class, 'index']);

// Protected Routes (Requires Authentication)
Route::middleware(['auth', 'verified', 'account.active'])->group(function () {
    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');
    
    // User Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Session Management Routes
    Route::prefix('profile/sessions')->name('profile.sessions')->group(function () {
        Route::get('/', [UserSessionController::class, 'index'])->name('');
        Route::delete('/{session}', [UserSessionController::class, 'destroy'])->name('.destroy');
        Route::post('/logout-others', [UserSessionController::class, 'logoutOtherDevices'])->name('.logout-others');
    });
});

// Storage Routes
Route::get('/storage/{path}', function ($path) {
    return response()->file(storage_path('app/public/' . $path));
})->where('path', '.*')->name('storage.local');

// Include Other Route Files
require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
require __DIR__.'/student.php';
require __DIR__.'/reading.php';
require __DIR__.'/listening.php';
