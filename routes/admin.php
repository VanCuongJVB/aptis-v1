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

    // Quizzes admin UI (placeholder views)
    Route::get('quizzes', [\App\Http\Controllers\Admin\QuizAdminController::class, 'index'])->name('quizzes.index');
    Route::get('quizzes/sets', [\App\Http\Controllers\Admin\QuizAdminController::class, 'sets'])->name('quizzes.sets');
    Route::get('quizzes/questions', [\App\Http\Controllers\Admin\QuizAdminController::class, 'questions'])->name('quizzes.questions');

    // Coming soon page for quizzes features
    Route::get('quizzes/coming-soon', function () {
        return view('admin.quizzes.coming_soon');
    })->name('quizzes.coming');

    // Question management (basic CRUD)
    Route::get('quizzes/questions/create', [\App\Http\Controllers\Admin\QuestionAdminController::class, 'create'])->name('questions.create');
    Route::post('quizzes/questions', [\App\Http\Controllers\Admin\QuestionAdminController::class, 'store'])->name('questions.store');
    Route::get('quizzes/questions/{question}/edit', [\App\Http\Controllers\Admin\QuestionAdminController::class, 'edit'])->name('questions.edit');
    Route::put('quizzes/questions/{question}', [\App\Http\Controllers\Admin\QuestionAdminController::class, 'update'])->name('questions.update');
    Route::delete('quizzes/questions/{question}', [\App\Http\Controllers\Admin\QuestionAdminController::class, 'destroy'])->name('questions.destroy');

    // ReadingSet management (CRUD for Sets)
    Route::get('quizzes/sets/create', [\App\Http\Controllers\Admin\ReadingSetController::class, 'create'])->name('sets.create');
    Route::post('quizzes/sets', [\App\Http\Controllers\Admin\ReadingSetController::class, 'store'])->name('sets.store');
    Route::get('quizzes/sets/{set}/edit', [\App\Http\Controllers\Admin\ReadingSetController::class, 'edit'])->name('sets.edit');
    Route::put('quizzes/sets/{set}', [\App\Http\Controllers\Admin\ReadingSetController::class, 'update'])->name('sets.update');
    Route::delete('quizzes/sets/{set}', [\App\Http\Controllers\Admin\ReadingSetController::class, 'destroy'])->name('sets.destroy');
});
