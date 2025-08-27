<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->enum('skill', ['reading','listening']);
            $table->text('description')->nullable();
            $table->boolean('is_published')->default(false);
            $table->smallInteger('duration_minutes')->default(45);
            $table->boolean('allow_seek')->default(false);
            $table->tinyInteger('listens_allowed')->default(1);
            $table->timestamps();
            $table->index(['skill','is_published'], 'quizzes_skill_published_idx');
        });
    }
    public function down(): void { Schema::dropIfExists('quizzes'); }
};
