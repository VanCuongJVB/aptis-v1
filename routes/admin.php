<?php

use App\Http\Controllers\Admin\QuizController;
use App\Http\Controllers\Admin\ReadingQuestionController;
use App\Http\Controllers\Admin\ReadingSetController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Reading\Part1Controller;
use App\Http\Controllers\Admin\QuestionController;
use Illuminate\Support\Facades\Route;

// Admin routes
Route::middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])->group(function () {
    Route::prefix('admin')->name('admin.')->group(function () {
        // Student Management
        Route::resource('students', StudentController::class);
        Route::get('students/import', [StudentController::class, 'importForm'])
            ->name('students.import.form');
        Route::post('students/import', [StudentController::class, 'importStore'])
            ->name('students.import');
        Route::get('students/{student}/extend', [StudentController::class, 'extend'])
            ->name('students.extend');

        // Reading Management
        Route::prefix('reading')->name('reading.')->group(function () {
            // Sets Management
            Route::controller(ReadingSetController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('part/{part}', 'showPart')->name('sets.part');
                Route::post('sets', 'store')->name('sets.store');
                Route::get('sets/{quiz}/edit', 'edit')->name('sets.edit');
                Route::patch('sets/{quiz}', 'update')->name('sets.update');
                Route::delete('sets/{quiz}', 'destroy')->name('sets.destroy');
                Route::post('sets/{quiz}/publish', 'publish')->name('sets.publish');
                Route::post('sets/{quiz}/unpublish', 'unpublish')->name('sets.unpublish');
                Route::post('sets/{quiz}/reorder', 'reorderQuestions')->name('sets.reorder');
            });

            // Part 1 - Sentence Completion
            Route::prefix('part1')->name('part1.')->controller(Part1Controller::class)->group(function () {
                Route::get('create/{quiz}', 'create')->name('create');
                Route::post('store/{quiz}', 'store')->name('store');
                Route::get('edit/{question}', 'edit')->name('edit');
                Route::put('update/{question}', 'update')->name('update');
                Route::delete('questions/{question}', 'destroy')->name('destroy');
            });

            // Part 2, 3, etc. routes can be added here following the same pattern
        });
    });
});
