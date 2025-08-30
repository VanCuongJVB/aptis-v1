<?php

use App\Http\Controllers\Admin\ListeningSetController;
use App\Http\Controllers\Listening\Part1Controller;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])->group(function () {
    Route::prefix('admin/listening')->name('admin.listening.')->group(function () {
        // Sets Management
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

        // Part 1 - Word List
        Route::prefix('part1')->name('part1.')->controller(Part1Controller::class)->group(function () {
            Route::get('create/{quiz}', 'create')->name('create');
            Route::post('store/{quiz}', 'store')->name('store'); 
            Route::get('edit/{question}', 'edit')->name('edit');
            Route::put('update/{question}', 'update')->name('update');
            Route::delete('questions/{question}', 'destroy')->name('destroy');
        });

        // Part 2-7 routes can be added here following the same pattern
    });
});
