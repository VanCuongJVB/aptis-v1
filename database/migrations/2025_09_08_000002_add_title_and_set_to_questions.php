<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            if (!Schema::hasColumn('questions', 'title')) {
                $table->string('title')->nullable()->after('id');
            }
            if (!Schema::hasColumn('questions', 'reading_set_id')) {
                $table->foreignId('reading_set_id')->nullable()->after('quiz_id')->constrained('sets')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            if (Schema::hasColumn('questions', 'reading_set_id')) {
                $table->dropForeign(['reading_set_id']);
                $table->dropColumn('reading_set_id');
            }
            if (Schema::hasColumn('questions', 'title')) {
                $table->dropColumn('title');
            }
        });
    }
};
