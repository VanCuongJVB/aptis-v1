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
    // Student practice routes
    Route::controller(\App\Http\Controllers\Listening\PracticeController::class)->group(function () {
        Route::get('quiz/{quiz}/start', 'startQuiz')->name('quiz.start');
        Route::get('attempt/{attempt}/question/{position}', 'showQuestion')->name('practice.question');
        Route::post('attempt/{attempt}/question/{question}', 'submitAnswer')->name('practice.answer');
        Route::get('attempt/{attempt}/finish', 'finishAttempt')->name('practice.finish');
        Route::get('attempt/{attempt}/result', 'showResult')->name('practice.result');
    });
});

// Student Listening dashboard
Route::middleware(['auth', 'student.access'])->prefix('listening')->name('listening.')->group(function () {
    Route::get('dashboard', function () {
        $quizzes = \App\Models\Quiz::published()->where('skill', 'listening')->orderBy('id', 'desc')->get();
        $recentAttempts = \App\Models\Attempt::where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->whereHas('quiz', function($q) { $q->where('skill','listening'); })
            ->with('quiz')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('student.listening.dashboard', compact('quizzes', 'recentAttempts'));
    })->name('dashboard');
});
