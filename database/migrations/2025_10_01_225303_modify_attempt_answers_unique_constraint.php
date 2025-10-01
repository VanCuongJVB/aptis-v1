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
        Schema::table('attempt_answers', function (Blueprint $table) {
            // Drop the existing unique constraint
            $table->dropUnique(['attempt_id', 'question_id']);
            
            // Add sub_index column for tracking sub-answers in multi-part questions
            $table->integer('sub_index')->default(0)->after('question_id');
            
            // Add new unique constraint that includes sub_index
            $table->unique(['attempt_id', 'question_id', 'sub_index']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attempt_answers', function (Blueprint $table) {
            // Drop the new unique constraint
            $table->dropUnique(['attempt_id', 'question_id', 'sub_index']);
            
            // Drop the sub_index column
            $table->dropColumn('sub_index');
            
            // Restore the original unique constraint
            $table->unique(['attempt_id', 'question_id']);
        });
    }
};
