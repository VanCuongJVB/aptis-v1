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
        Schema::table('questions', function (Blueprint $table) {
            // Add new fields
            $table->text('explanation')->nullable()->after('stem');
            $table->json('meta')->nullable()->after('explanation');
            $table->text('context_text')->nullable()->after('meta');
            $table->string('passage')->nullable()->after('context_text');
            $table->integer('part')->nullable()->after('quiz_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn(['explanation', 'meta', 'context_text', 'passage', 'part']);
        });
    }
};
