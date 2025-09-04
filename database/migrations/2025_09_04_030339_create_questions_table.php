<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
            
            // Question content
            $table->text('stem'); // Question text
            $table->text('explanation')->nullable(); // Answer explanation
            
            // Question classification (same as quiz for easy filtering)
            $table->enum('skill', ['reading', 'listening']);
            $table->tinyInteger('part');
            $table->string('type', 50)->default('single_choice'); // single_choice, multiple_choice, etc.
            
            // Order within quiz
            $table->integer('order')->default(1);
            
            // Media files
            $table->string('audio_path')->nullable();
            $table->string('image_path')->nullable();
            
            // Additional metadata
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['quiz_id', 'order']);
            $table->index(['skill', 'part']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
