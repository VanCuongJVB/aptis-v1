<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        // Only modify for MySQL (safe path for current dev environment)
        try {
            $driver = DB::getPdo()->getAttribute(PDO::ATTR_DRIVER_NAME);
        } catch (\Throwable $e) {
            $driver = null;
        }

        if ($driver === 'mysql') {
            // Make columns nullable to accommodate imports that omit them
            DB::statement("ALTER TABLE `quizzes` MODIFY `duration_minutes` INT NULL");
            DB::statement("ALTER TABLE `quizzes` MODIFY `part` TINYINT NULL");
            DB::statement("ALTER TABLE `quizzes` MODIFY `skill` ENUM('reading','listening') NULL");
            DB::statement("ALTER TABLE `quizzes` MODIFY `show_explanation` TINYINT(1) NULL");
        } else {
            // Fallback: attempt schema change (requires doctrine/dbal for some drivers)
            Schema::table('quizzes', function (Blueprint $table) {
                $table->integer('duration_minutes')->nullable()->change();
                $table->tinyInteger('part')->nullable()->change();
                // enum change may not work without dbal; leave as-is for non-mysql
                $table->boolean('show_explanation')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        try {
            $driver = DB::getPdo()->getAttribute(PDO::ATTR_DRIVER_NAME);
        } catch (\Throwable $e) {
            $driver = null;
        }

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `quizzes` MODIFY `duration_minutes` INT NOT NULL DEFAULT 45");
            DB::statement("ALTER TABLE `quizzes` MODIFY `part` TINYINT NOT NULL");
            DB::statement("ALTER TABLE `quizzes` MODIFY `skill` ENUM('reading','listening') NOT NULL");
            DB::statement("ALTER TABLE `quizzes` MODIFY `show_explanation` TINYINT(1) NOT NULL DEFAULT 1");
        } else {
            Schema::table('quizzes', function (Blueprint $table) {
                $table->integer('duration_minutes')->nullable(false)->default(45)->change();
                $table->tinyInteger('part')->nullable(false)->change();
                $table->boolean('show_explanation')->nullable(false)->default(true)->change();
            });
        }
    }
};
