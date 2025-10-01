<?php

use App\Http\Controllers\Reading\QuestionController;
use App\Http\Controllers\Reading\PracticeController;
use App\Http\Controllers\Student\ReadingController;
use App\Http\Controllers\Student\ReadingSetController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Reading Practice Routes
|--------------------------------------------------------------------------
|
| Đây là nơi định nghĩa các route cho phần luyện tập Reading.
| 
*/

// Admin Reading Management Routes removed (reading admin UI moved/removed)

// Student Reading Practice Routes
Route::middleware(['auth', 'student.access'])->prefix('reading')->name('reading.')->group(function () {
    // Full Test Reading
    Route::get('full-random', [App\Http\Controllers\Reading\FullRandomController::class, 'index'])->name('full-random');
    Route::post('full-random/result', [App\Http\Controllers\Reading\FullRandomResultController::class, 'store'])->name('full_random_result.store');
    Route::get('full-random/result/{attempt}', [App\Http\Controllers\Reading\FullRandomResultController::class, 'show'])->name('full_random_result.show');
    Route::get('/', [PracticeController::class, 'index'])->name('practice.index');
    Route::get('dashboard', [ReadingSetController::class, 'index'])->name('dashboard');
    Route::get('part/{part}', [PracticeController::class, 'partDetail'])->name('practice.part');
    Route::get('quiz/{quiz}/start', [PracticeController::class, 'startQuiz'])->name('quiz.start');

    // Luyện tập bài đọc
    Route::prefix('practice')->name('practice.')->group(function () {
        Route::get('quiz/{quiz}/start', [PracticeController::class, 'startQuiz'])->name('start');
        Route::get('attempt/{attempt}/question/{position}', [PracticeController::class, 'showQuestion'])->name('question');
        Route::get('attempt/{attempt}/part-questions', [PracticeController::class, 'partQuestions'])->name('partQuestions');
        Route::post('attempt/{attempt}/question/{question}', [PracticeController::class, 'submitAnswer'])->name('answer');
        Route::post('attempt/{attempt}/batch-submit', [PracticeController::class, 'batchSubmit'])->name('batchSubmit');
        Route::get('attempt/{attempt}/finish', [PracticeController::class, 'finishAttempt'])->name('finish');
        Route::get('attempt/{attempt}/result', [PracticeController::class, 'showResult'])->name('result');
    });

    // Thống kê và lịch sử
    Route::get('history', [PracticeController::class, 'history'])->name('history');
    Route::get('progress', [PracticeController::class, 'progress'])->name('progress');

    Route::get('sets', [ReadingSetController::class, 'index'])->name('sets.index');
    Route::get('sets/{set}', [ReadingSetController::class, 'show'])->name('sets.show');
});

Route::middleware(['auth', 'student.access'])->prefix('reading')->name('student.reading.')->group(function () {
    Route::get('dashboard', [ReadingController::class, 'dashboard'])->name('dashboard');
    Route::get('sets', [ReadingSetController::class, 'index'])->name('sets.index');
    Route::get('sets/{set}', [ReadingSetController::class, 'show'])->name('sets.show');
});
