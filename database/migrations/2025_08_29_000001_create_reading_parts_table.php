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
        Schema::create('reading_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('part_number');
            $table->string('title');
            $table->text('instructions');
            $table->unsignedInteger('question_count')->default(0);
            $table->unsignedInteger('time_limit')->default(0);
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->unique(['quiz_id', 'part_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reading_parts');
    }
};
