<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->integer('score_raw')->default(0);
            $table->decimal('score_percent', 5, 2)->default(0.00);
            $table->timestamps();
            $table->index(['user_id','quiz_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('attempts'); }
};
