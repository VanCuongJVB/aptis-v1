<?php

use App\Http\Controllers\Admin\ReadingQuestionController;
use App\Http\Controllers\Reading\DrillController;
use App\Http\Controllers\Reading\ProgressController;
use App\Http\Controllers\Reading\TestController;
use Illuminate\Support\Facades\Route;

// Student routes for Reading Practice
Route::middleware(['auth'])->group(function () {
    Route::prefix('reading')->name('reading.')->group(function () {
        // Part Drill Routes
        Route::prefix('drill')->name('drill.')->group(function () {
            Route::get('part/{part}', [DrillController::class, 'index'])->name('part');
            Route::get('part/{part}/sets', [DrillController::class, 'listSets'])->name('sets');
            Route::get('set/{quiz}', [DrillController::class, 'startSet'])->name('start');
            Route::post('answer', [DrillController::class, 'submitAnswer'])->name('answer');
            Route::get('next/{quiz}/{currentQuestion}', [DrillController::class, 'nextQuestion'])->name('next');
            Route::get('summary/{quiz}', [DrillController::class, 'summary'])->name('summary');
            Route::post('flag-question', [DrillController::class, 'flagQuestion'])->name('flag');
            
            // Progress tracking for drill mode
            Route::get('wrong-answers/{part}', [DrillController::class, 'wrongAnswers'])->name('wrong-answers');
            Route::post('start-wrong-answers/{part}', [DrillController::class, 'startWrongAnswers'])->name('start-wrong-answers');
        });

        // Test mode routes
        Route::prefix('test')->name('test.')->group(function () {
            Route::get('start', [TestController::class, 'start'])->name('start');
            Route::get('question/{question}', [TestController::class, 'question'])->name('question');
            Route::post('answer', [TestController::class, 'submitAnswer'])->name('answer');
            Route::get('summary', [TestController::class, 'summary'])->name('summary');
        });

        // Progress tracking
        Route::get('progress', [ProgressController::class, 'index'])->name('progress');
        Route::get('progress/stats', [ProgressController::class, 'stats'])->name('progress.stats');
        Route::get('progress/history', [ProgressController::class, 'history'])->name('progress.history');
    });
});

// Admin routes
Route::middleware(['auth', 'admin'])->group(function () {
    Route::prefix('admin')->name('admin.')->group(function () {
        // Reading questions
        Route::get('quizzes/{quiz}/reading/create', [ReadingQuestionController::class, 'create'])
            ->name('reading.create');
        Route::post('quizzes/{quiz}/reading', [ReadingQuestionController::class, 'store'])
            ->name('reading.store');
        Route::get('quizzes/{quiz}/reading/{question}/edit', [ReadingQuestionController::class, 'edit'])
            ->name('reading.edit');
        Route::put('quizzes/{quiz}/reading/{question}', [ReadingQuestionController::class, 'update'])
            ->name('reading.update');
    });
});
