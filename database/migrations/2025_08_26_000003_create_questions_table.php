<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
            $table->text('stem');
            $table->enum('type', ['single','multi'])->default('single');
            $table->integer('order')->default(1);
            $table->string('audio_path', 255)->nullable();
            $table->timestamps();
            $table->index(['quiz_id','order']);
        });
    }
    public function down(): void { Schema::dropIfExists('questions'); }
};
