<?php

use App\Http\Controllers\Reading\ReadingManagerController;
use App\Http\Controllers\Reading\QuestionController;
use App\Http\Controllers\Reading\PracticeController;
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
    // Trang chủ Reading (practice index kept for compatibility)
    Route::get('/', [PracticeController::class, 'index'])->name('practice.index');

    // Student Reading dashboard (quizzes + recent attempts filtered)
    Route::get('dashboard', function () {
        $quizzes = \App\Models\Quiz::published()->where('skill', 'reading')->orderBy('id', 'desc')->get();
        $recentAttempts = \App\Models\Attempt::where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->whereHas('quiz', function($q) { $q->where('skill','reading'); })
            ->with('quiz')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('student.reading.dashboard', compact('quizzes', 'recentAttempts'));
    })->name('dashboard');
    
    // Trang chi tiết phần
    Route::get('part/{part}', [PracticeController::class, 'partDetail'])->name('practice.part');
    
    // Luyện tập bài đọc
    Route::prefix('practice')->name('practice.')->group(function () {
        Route::get('quiz/{quiz}/start', [PracticeController::class, 'startQuiz'])->name('start');
        Route::get('attempt/{attempt}/question/{position}', [PracticeController::class, 'showQuestion'])->name('question');
            // Return full-part question metadata as JSON for practice mode (FE can fetch once and self-grade)
            Route::get('attempt/{attempt}/part-questions', [PracticeController::class, 'partQuestions'])->name('partQuestions');
        Route::post('attempt/{attempt}/question/{question}', [PracticeController::class, 'submitAnswer'])->name('answer');
        Route::get('attempt/{attempt}/finish', [PracticeController::class, 'finishAttempt'])->name('finish');
        Route::get('attempt/{attempt}/result', [PracticeController::class, 'showResult'])->name('result');
    });
    
    // Thống kê và lịch sử
    Route::get('history', [PracticeController::class, 'history'])->name('history');
    Route::get('progress', [PracticeController::class, 'progress'])->name('progress');

    // Student-facing ReadingSet listing and detail (choose a set before starting)
    Route::get('sets', function (\Illuminate\Http\Request $request) {
        $query = \App\Models\ReadingSet::whereHas('quiz', function($q){ $q->where('skill','reading')->published(); })->where('is_public', true);

        if ($request->query('quiz')) {
            $query->where('quiz_id', $request->query('quiz'));
        }

        $sets = $query->orderBy('quiz_id')->orderBy('order')->get();

        return view('student.reading.sets.index', compact('sets'));
    })->name('sets.index');

    Route::get('sets/{set}', function (\App\Models\ReadingSet $set) {
        // ensure set is public and belongs to a published reading quiz
        // quizzes use the `is_published` boolean column, make sure we check that
        if (! $set->is_public || ! ($set->quiz && ($set->quiz->is_published ?? false))) {
            abort(404);
        }

        return view('student.reading.sets.show', compact('set'));
    })->name('sets.show');
});
