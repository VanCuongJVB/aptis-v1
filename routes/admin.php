<?php

use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\UserController;
// Reading/Listening admin controllers removed
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Đây là nơi định nghĩa các route cho phần quản trị.
| Tất cả các route đều có tiền tố 'admin' và sử dụng middleware 'admin'
|
*/

Route::middleware(['auth', 'admin.role'])->prefix('admin')->name('admin.')->group(function () {
    
    // User Management Routes
    Route::resource('users', UserController::class);
    Route::get('users/{user}/sessions', [UserController::class, 'sessions'])->name('users.sessions');
    Route::post('users/{user}/logout-all-devices', [UserController::class, 'logoutAllDevices'])->name('users.logout-all');
    Route::delete('sessions/{session}', [UserController::class, 'logoutDevice'])->name('sessions.destroy');
    
    // Student Management Routes
    Route::resource('students', StudentController::class);
    Route::get('students/import', [StudentController::class, 'importForm'])->name('students.import.form');
    Route::post('students/import', [StudentController::class, 'importStore'])->name('students.import');
    Route::get('students/{student}/extend', [StudentController::class, 'extend'])->name('students.extend');
    
    // Admin reading/listening/question routes removed
});
