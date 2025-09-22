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
    Route::resource('students', StudentController::class)->except(['show']);
    Route::get('students/import', [StudentController::class, 'importForm'])->name('students.import.form');
    Route::post('students/import', [StudentController::class, 'importStore'])->name('students.import');
    Route::get('students/{student}/extend', [StudentController::class, 'extend'])->name('students.extend');
    Route::post('students/{student}/toggle-active', [StudentController::class, 'toggleActive'])->name('students.toggleActive');

    // Admin reading/listening/question routes removed

    // Quizzes CRUD
    Route::resource('quizzes', \App\Http\Controllers\Admin\QuizController::class)->except(['show']);
    // (Nếu cần giữ các route sets/questions tổng quan thì thêm lại dưới đây)
    Route::get('quizzes/sets', [\App\Http\Controllers\Admin\QuizAdminController::class, 'sets'])->name('quizzes.sets');
    Route::get('quizzes/questions', [\App\Http\Controllers\Admin\QuizAdminController::class, 'questions'])->name('quizzes.questions');

    // Coming soon page for quizzes features
    Route::get('quizzes/coming-soon', function () {
        return view('admin.quizzes.coming_soon');
    })->name('quizzes.coming');

    // Import endpoint for quizzes JSON
    Route::post('quizzes/import', [\App\Http\Controllers\Admin\ImportController::class, 'store'])->name('quizzes.import');
    Route::post('quizzes/import/dry-run', [\App\Http\Controllers\Admin\ImportController::class, 'dryRun'])->name('quizzes.import.dryrun');
    // Export current quizzes as a JSON template for users to download and edit
    Route::get('quizzes/import/template', [\App\Http\Controllers\Admin\ImportController::class, 'exportTemplate'])->name('quizzes.import.template');

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

    Route::get('ajax/sets/{set}/questions', [\App\Http\Controllers\Admin\ReadingSetController::class, 'questions'])->name('sets.questions');

    // CRUD cho Part 1 (Reading Gap Filling) - gom về 1 controller đa part
    Route::get('quizzes/questions/part1/create', [\App\Http\Controllers\Admin\QuestionPartController::class, 'createReadingPart1'])->name('questions.part1.create');
    Route::post('quizzes/questions/part1', [\App\Http\Controllers\Admin\QuestionPartController::class, 'storeReadingPart1'])->name('questions.part1.store');
    Route::get('quizzes/questions/part1/{question}/edit', [\App\Http\Controllers\Admin\QuestionPartController::class, 'editReadingPart1'])->name('questions.part1.edit');
    Route::put('quizzes/questions/part1/{question}', [\App\Http\Controllers\Admin\QuestionPartController::class, 'updateReadingPart1'])->name('questions.part1.update');
    Route::delete('quizzes/questions/part1/{question}', [\App\Http\Controllers\Admin\QuestionPartController::class, 'destroyReadingPart1'])->name('questions.part1.destroy');

    // CRUD cho Part 2 (Reading Matching)
    Route::get('quizzes/questions/part2/create', [\App\Http\Controllers\Admin\QuestionPartController::class, 'createReadingPart2'])->name('questions.part2.create');
    Route::post('quizzes/questions/part2', [\App\Http\Controllers\Admin\QuestionPartController::class, 'storeReadingPart2'])->name('questions.part2.store');
    Route::get('quizzes/questions/part2/{question}/edit', [\App\Http\Controllers\Admin\QuestionPartController::class, 'editReadingPart2'])->name('questions.part2.edit');
    Route::put('quizzes/questions/part2/{question}', [\App\Http\Controllers\Admin\QuestionPartController::class, 'updateReadingPart2'])->name('questions.part2.update');
    Route::delete('quizzes/questions/part2/{question}', [\App\Http\Controllers\Admin\QuestionPartController::class, 'destroyReadingPart2'])->name('questions.part2.destroy');

    // CRUD cho Part 3 (Reading Matching - Paragraph to Option)
    Route::get('quizzes/questions/part3/create', [\App\Http\Controllers\Admin\QuestionPartController::class, 'createReadingPart3'])->name('questions.part3.create');
    Route::post('quizzes/questions/part3', [\App\Http\Controllers\Admin\QuestionPartController::class, 'storeReadingPart3'])->name('questions.part3.store');
    Route::get('quizzes/questions/part3/{question}/edit', [\App\Http\Controllers\Admin\QuestionPartController::class, 'editReadingPart3'])->name('questions.part3.edit');
    Route::put('quizzes/questions/part3/{question}', [\App\Http\Controllers\Admin\QuestionPartController::class, 'updateReadingPart3'])->name('questions.part3.update');
    Route::delete('quizzes/questions/part3/{question}', [\App\Http\Controllers\Admin\QuestionPartController::class, 'destroyReadingPart3'])->name('questions.part3.destroy');

    // CRUD cho Part 4 (Reading Heading Matching)
    Route::get('quizzes/questions/part4/create', [\App\Http\Controllers\Admin\QuestionPartController::class, 'createReadingPart4'])->name('questions.part4.create');
    Route::post('quizzes/questions/part4', [\App\Http\Controllers\Admin\QuestionPartController::class, 'storeReadingPart4'])->name('questions.part4.store');
    Route::get('quizzes/questions/part4/{question}/edit', [\App\Http\Controllers\Admin\QuestionPartController::class, 'editReadingPart4'])->name('questions.part4.edit');
    Route::put('quizzes/questions/part4/{question}', [\App\Http\Controllers\Admin\QuestionPartController::class, 'updateReadingPart4'])->name('questions.part4.update');
    Route::delete('quizzes/questions/part4/{question}', [\App\Http\Controllers\Admin\QuestionPartController::class, 'destroyReadingPart4'])->name('questions.part4.destroy');
});
