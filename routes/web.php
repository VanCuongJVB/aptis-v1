<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\PasswordLoginController;
use App\Http\Controllers\Student\DashboardController as StudentDashboard;
use App\Http\Controllers\Student\AttemptController as StudentAttempt;
use App\Http\Controllers\Admin\QuizController as AdminQuiz;
use App\Http\Controllers\Admin\QuestionController as AdminQuestion;
use App\Http\Controllers\Admin\StudentController as AdminStudent;
use App\Http\Middleware\EnsureAccountActive;
use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\EnsureSessionAllowed;

// Auth
Route::get('/login', [PasswordLoginController::class, 'show'])->name('login');
Route::post('/login', [PasswordLoginController::class, 'login'])->name('login.post');
Route::post('/logout', [PasswordLoginController::class, 'logout'])->name('logout');

// Home
Route::get('/', fn() => redirect()->route('student.quizzes.index'));

// Student area
Route::middleware([\Illuminate\Auth\Middleware\Authenticate::class, EnsureAccountActive::class, EnsureSessionAllowed::class])->group(function () {
    Route::get('/quizzes', [StudentDashboard::class, 'index'])->name('student.quizzes.index');
    Route::get('/quizzes/{quiz}', [StudentDashboard::class, 'show'])->name('student.quizzes.show');
    Route::post('/quizzes/{quiz}/start', [StudentAttempt::class, 'start'])->name('student.quizzes.start');
    Route::post('/quizzes/{quiz}/submit', [StudentAttempt::class, 'submit'])->name('student.quizzes.submit');
    Route::get('/attempts/{attempt}', [StudentAttempt::class, 'result'])->name('student.attempts.result');
});

Route::get('/inactive', fn() => view('inactive'))->name('inactive');

// Admin area
Route::middleware([\Illuminate\Auth\Middleware\Authenticate::class, EnsureAdmin::class, EnsureSessionAllowed::class])
    ->prefix('admin')->as('admin.')->group(function () {
        Route::get('/', fn() => redirect()->route('admin.quizzes.index'))->name('home');

        Route::resource('quizzes', AdminQuiz::class)->except(['show']);
        Route::get('/quizzes/{quiz}/questions/create', [AdminQuestion::class, 'create'])->name('questions.create');
        Route::post('/quizzes/{quiz}/questions', [AdminQuestion::class, 'store'])->name('questions.store');
        Route::get('/questions/{question}/edit', [AdminQuestion::class, 'edit'])->name('questions.edit');
        Route::put('/questions/{question}', [AdminQuestion::class, 'update'])->name('questions.update');
        Route::delete('/questions/{question}', [AdminQuestion::class, 'destroy'])->name('questions.destroy');

        Route::resource('students', AdminStudent::class)->except(['show']);
        Route::post('/students/{student}/extend', [AdminStudent::class, 'extend'])->name('students.extend'); // ?days=30
        Route::get('/students-import', [AdminStudent::class, 'importForm'])->name('students.import.form');
        Route::post('/students-import', [AdminStudent::class, 'importStore'])->name('students.import.store');
    });
