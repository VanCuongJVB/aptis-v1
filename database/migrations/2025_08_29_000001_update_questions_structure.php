<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // Drop cột type cũ
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        // Thêm các cột mới
        Schema::table('questions', function (Blueprint $table) {
            if (!Schema::hasColumn('questions', 'part')) {
                $table->unsignedTinyInteger('part')->default(1)->after('quiz_id');
            }
            
            // Thêm lại cột type với đầy đủ các loại câu hỏi
            $table->enum('type', [
                'multiple_choice',               // Câu hỏi trắc nghiệm thông thường
                'reading_sentence_completion',   // Part 1: Sentence completion with 3 choices
                'reading_text_completion',      // Part 2: Short text with blanks (3 choices each)
                'reading_matching',             // Part 3: Match questions with passages/people
                'reading_reordering'            // Part 4: Reorder sentences/paragraphs
            ])->after('part');

            if (!Schema::hasColumn('questions', 'explanation')) {
                $table->text('explanation')->nullable()->after('stem');
            }
            if (!Schema::hasColumn('questions', 'metadata')) {
                $table->json('metadata')->nullable()->after('explanation');
            }
        });
    }

    public function down(): void {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn(['part', 'type', 'explanation', 'metadata']);
            $table->enum('type', ['single', 'multi'])->default('single');
        });
    }
};
