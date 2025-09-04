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
        Schema::create('attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
            
            // Attempt status
            $table->enum('status', ['in_progress', 'submitted', 'abandoned'])->default('in_progress');
            
            // Timing
            $table->timestamp('started_at');
            $table->timestamp('submitted_at')->nullable();
            $table->integer('duration_seconds')->nullable(); // Actual time taken
            
            // Results
            $table->integer('total_questions')->default(0);
            $table->integer('correct_answers')->default(0);
            $table->decimal('score_percentage', 5, 2)->default(0); // 0.00 to 100.00
            $table->integer('score_points')->default(0);
            
            // Device info (for security)
            $table->string('device_fingerprint')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            
            // Additional data
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['quiz_id', 'status']);
            $table->index('started_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attempts');
    }
};
