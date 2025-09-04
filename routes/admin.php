<?php

use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ReadingSetController;
use App\Http\Controllers\Admin\ListeningSetController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\ReadingQuestionController;
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
    
    // Reading Management Routes
    Route::prefix('reading')->name('reading.')->group(function () {
        // Main Reading Set Management
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
    });
    
    // Listening Management Routes
    Route::prefix('listening')->name('listening.')->group(function () {
        // Main Listening Set Management
        Route::controller(ListeningSetController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('part/{part}', 'showPart')->name('sets.part');
            Route::post('sets', 'store')->name('sets.store');
            Route::get('sets/{quiz}/edit', 'edit')->name('sets.edit');
            Route::patch('sets/{quiz}', 'update')->name('sets.update');
            Route::delete('sets/{quiz}', 'destroy')->name('sets.destroy');
            Route::post('sets/{quiz}/publish', 'publish')->name('sets.publish');
            Route::post('sets/{quiz}/unpublish', 'unpublish')->name('sets.unpublish');
            Route::get('sets/{quiz}/create-full', 'createFull')->name('sets.create.full');
            Route::post('sets/{quiz}/reorder', 'reorderQuestions')->name('sets.reorder');
        });
    });
    
    // Question Management Routes
    Route::controller(QuestionController::class)->group(function () {
        Route::get('reading/part/{part}/questions', 'partIndex')->name('questions.part');
        Route::get('quizzes/{quiz}/questions/create', 'create')->name('questions.create');
        Route::post('quizzes/{quiz}/questions', 'store')->name('questions.store');
        Route::get('questions/{question}/edit', 'edit')->name('questions.edit');
        Route::put('questions/{question}', 'update')->name('questions.update');
        Route::delete('questions/{question}', 'destroy')->name('questions.destroy');
        Route::post('questions/{question}/assign-quiz', 'assignQuiz')->name('questions.assign-quiz');
    });
    
    // Reading Question Management Routes
    Route::controller(ReadingQuestionController::class)->group(function () {
        Route::get('quizzes/{quiz}/reading/create', 'create')->name('reading.create');
        Route::post('quizzes/{quiz}/reading', 'store')->name('reading.store');
        Route::get('quizzes/{quiz}/reading/{question}/edit', 'edit')->name('reading.edit');
        Route::put('quizzes/{quiz}/reading/{question}', 'update')->name('reading.update');
    });
});
