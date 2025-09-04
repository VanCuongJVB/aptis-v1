<?php

use App\Http\Controllers\Listening\Part1Controller;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Listening Practice Routes
|--------------------------------------------------------------------------
|
| Đây là nơi định nghĩa các route cho phần luyện tập Listening.
|
*/

// Admin Listening Part Routes
Route::middleware(['auth', 'admin.role'])->group(function () {
    Route::prefix('admin/listening/part1')->name('admin.listening.part1.')->controller(Part1Controller::class)->group(function () {
        Route::get('create/{quiz}', 'create')->name('create');
        Route::post('store/{quiz}', 'store')->name('store'); 
        Route::get('edit/{question}', 'edit')->name('edit');
        Route::put('update/{question}', 'update')->name('update');
        Route::delete('questions/{question}', 'destroy')->name('destroy');
    });
});

// Student Listening Practice Routes
Route::middleware(['auth', 'student.access'])->prefix('listening')->name('listening.')->group(function () {
    // Index route (placeholder)
    Route::get('/', function() {
        return view('coming-soon', ['feature' => 'Listening Practice']);
    })->name('index');
    
    // Add more student listening routes here as they are developed
});
