<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Listening Practice Routes
|--------------------------------------------------------------------------
|
| Đây là nơi định nghĩa các route cho phần luyện tập Listening.
|
*/

// Admin Listening Part Routes removed (admin listening UI removed)

// Student listing of listening sets and per-set questions
Route::middleware(['auth', 'student.access'])->prefix('student/listening')->name('student.listening.')->controller(\App\Http\Controllers\Student\ListeningSetController::class)->group(function () {
    Route::get('sets', 'index')->name('sets.index');
    Route::get('sets/{set}', 'show')->name('sets.show');
});

// Also expose the same student-facing sets pages under the public 'listening' prefix
// so route patterns match Reading (`listening/sets`), keep controller reuse for parity.
Route::middleware(['auth', 'student.access'])->prefix('listening')->name('listening.')->controller(\App\Http\Controllers\Student\ListeningSetController::class)->group(function () {
    Route::get('sets', 'index')->name('sets.index');
    Route::get('sets/{set}', 'show')->name('sets.show');
});

// Student Listening Practice Routes
Route::middleware(['auth', 'student.access'])->prefix('listening')->name('listening.')->group(function () {
    // Index route (placeholder)
    Route::get('/', function() {
        return view('coming-soon', ['feature' => 'Listening Practice']);
    })->name('index');
    
    // Add more student listening routes here as they are developed
    // Student practice routes
    Route::controller(\App\Http\Controllers\Listening\PracticeController::class)->group(function () {
        Route::get('quiz/{quiz}/start', 'startQuiz')->name('quiz.start');
        Route::get('attempt/{attempt}/question/{position}', 'showQuestion')->name('practice.question');
        	Route::post('attempt/{attempt}/question/{question}', 'submitAnswer')->name('practice.answer');
            Route::post('attempt/{attempt}/batch-submit', 'batchSubmit')->name('practice.batchSubmit');
        Route::get('attempt/{attempt}/finish', 'finishAttempt')->name('practice.finish');
        Route::get('attempt/{attempt}/result', 'showResult')->name('practice.result');
    });
});

// Student Listening dashboard
Route::middleware(['auth', 'student.access'])->prefix('listening')->name('student.listening.')->group(function () {
    Route::get('dashboard', [\App\Http\Controllers\Student\ListeningController::class, 'dashboard'])->name('dashboard');
});
