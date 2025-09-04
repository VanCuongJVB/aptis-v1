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
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            
            // Skill and Part classification
            $table->enum('skill', ['reading', 'listening']);
            $table->tinyInteger('part')->comment('Part 1, 2, 3, 4');
            
            // Publishing control
            $table->boolean('is_published')->default(false);
            
            // Quiz settings
            $table->integer('duration_minutes')->default(45);
            $table->boolean('show_explanation')->default(true);
            
            // Metadata for additional settings
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['skill', 'part', 'is_published']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
