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
    $fullPath = storage_path('app/public/' . $path);
    if (!file_exists($fullPath)) {
        abort(404);
    }

    $size = filesize($fullPath);
    $fmimetype = @mime_content_type($fullPath) ?: 'application/octet-stream';

    $start = 0;
    $end = $size - 1;
    $status = 200;
    $length = $size;

    $headers = [
        'Content-Type' => $fmimetype,
        'Accept-Ranges' => 'bytes',
    ];

    $range = request()->header('Range');
    if ($range) {
        // Parse the range header, e.g. "bytes=0-1023"
        if (preg_match('/bytes=([0-9]*)-([0-9]*)/', $range, $matches)) {
            if ($matches[1] !== '') $start = intval($matches[1]);
            if ($matches[2] !== '') $end = intval($matches[2]);
            if ($start > $end) {
                return response('', 416);
            }
            $length = $end - $start + 1;
            $status = 206; // Partial Content
            $headers['Content-Range'] = "bytes {$start}-{$end}/{$size}";
            $headers['Content-Length'] = $length;
        }
    } else {
        $headers['Content-Length'] = $size;
    }

    return response()->stream(function() use ($fullPath, $start, $length) {
        $fp = fopen($fullPath, 'rb');
        if ($start > 0) fseek($fp, $start);
        $bytesRemaining = $length;
        $buffer = 1024 * 8;
        while ($bytesRemaining > 0 && !feof($fp)) {
            $read = ($bytesRemaining > $buffer) ? $buffer : $bytesRemaining;
            echo fread($fp, $read);
            flush();
            $bytesRemaining -= $read;
        }
        fclose($fp);
    }, $status, $headers);
})->where('path', '.*')->name('storage.local');

// Include Other Route Files
require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
require __DIR__.'/student.php';
require __DIR__.'/reading.php';
require __DIR__.'/listening.php';
