<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            if (!Schema::hasColumn('questions', 'part')) {
                $table->unsignedTinyInteger('part')->default(1)->after('quiz_id');
            }
            if (!Schema::hasColumn('questions', 'type')) {
                $table->string('type', 20);
                // $table->string('type', 50)->default('mcq_single')->after('part');
            }
            if (!Schema::hasColumn('questions', 'explanation')) {
                $table->text('explanation')->nullable()->after('stem');
            }
            if (!Schema::hasColumn('questions', 'meta')) {
                $table->json('meta')->nullable()->after('explanation');
            }
            if (!Schema::hasColumn('questions', 'audio_url')) {
                $table->string('audio_url')->nullable()->after('meta');
            }
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            if (Schema::hasColumn('questions', 'audio_url')) $table->dropColumn('audio_url');
            if (Schema::hasColumn('questions', 'meta')) $table->dropColumn('meta');
            if (Schema::hasColumn('questions', 'type')) $table->dropColumn('type');
            if (Schema::hasColumn('questions', 'part')) $table->dropColumn('part');
        });
    }
};
