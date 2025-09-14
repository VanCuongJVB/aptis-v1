<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('user_sessions', 'session_id')) {
                $table->string('session_id')->nullable()->after('user_id')->index();
            }
            if (!Schema::hasColumn('user_sessions', 'revoked_at')) {
                $table->timestamp('revoked_at')->nullable()->after('last_active_at');
            }
            // Remove unique index on device_fingerprint if exists (MySQL-compatible check)
            // Use a direct SHOW INDEX query instead of Doctrine's schema manager, which
            // may not be available on all connection classes.
            try {
                $index = DB::select("SHOW INDEX FROM `user_sessions` WHERE Key_name = ?", ['device_fingerprint']);
                if (!empty($index)) {
                    $table->dropUnique('device_fingerprint');
                }
            } catch (\Exception $e) {
                // If the database driver doesn't support SHOW INDEX or another error
                // occurs, skip dropping the index — it's a safe non-fatal operation.
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_sessions', function (Blueprint $table) {
            if (Schema::hasColumn('user_sessions', 'session_id')) {
                $table->dropColumn('session_id');
            }
            if (Schema::hasColumn('user_sessions', 'revoked_at')) {
                $table->dropColumn('revoked_at');
            }
            // Re-create unique if needed
            // Note: if existing duplicate device_fingerprint values exist, this may fail.
            // So we avoid re-adding automatically.
        });
    }
};
