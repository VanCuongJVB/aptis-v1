<?php

use App\Http\Controllers\Reading\ReadingManagerController;
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

// Admin Reading Management Routes
Route::middleware(['auth', 'admin.role'])->prefix('admin/reading')->name('admin.reading.')->group(function () {
    // Quản lý Reading
    Route::get('/', [ReadingManagerController::class, 'index'])->name('index');

    // Quản lý bộ đề Reading
    Route::prefix('sets')->name('sets.')->group(function () {
        Route::get('part/{part}', [ReadingManagerController::class, 'showPart'])->name('part');
        Route::get('create/{part}', [ReadingManagerController::class, 'create'])->name('create');
        Route::post('/', [ReadingManagerController::class, 'store'])->name('store');
        Route::get('{quiz}/edit', [ReadingManagerController::class, 'edit'])->name('edit');
        Route::put('{quiz}', [ReadingManagerController::class, 'update'])->name('update');
        Route::delete('{quiz}', [ReadingManagerController::class, 'destroy'])->name('destroy');
        Route::post('{quiz}/toggle-publish', [ReadingManagerController::class, 'togglePublish'])->name('toggle-publish');
        Route::post('{quiz}/reorder', [ReadingManagerController::class, 'reorderQuestions'])->name('reorder');
    });

    // Quản lý câu hỏi Reading
    Route::prefix('questions')->name('questions.')->group(function () {
        Route::get('quiz/{quiz}/create', [QuestionController::class, 'create'])->name('create');
        Route::post('quiz/{quiz}', [QuestionController::class, 'store'])->name('store');
        Route::get('{question}/edit', [QuestionController::class, 'edit'])->name('edit');
        Route::put('{question}', [QuestionController::class, 'update'])->name('update');
        Route::delete('{question}', [QuestionController::class, 'destroy'])->name('destroy');
    });
});

// Student Reading Practice Routes
Route::middleware(['auth', 'student.access'])->prefix('reading')->name('reading.')->group(function () {
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
