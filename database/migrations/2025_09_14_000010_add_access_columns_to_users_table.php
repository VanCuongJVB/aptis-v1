<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'access_starts_at')) {
                $table->timestamp('access_starts_at')->nullable()->after('is_active');
            }
            if (!Schema::hasColumn('users', 'access_ends_at')) {
                $table->timestamp('access_ends_at')->nullable()->after('access_starts_at');
            }
            if (!Schema::hasColumn('users', 'last_access_at')) {
                $table->timestamp('last_access_at')->nullable()->after('access_ends_at');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'last_access_at')) {
                $table->dropColumn('last_access_at');
            }
            if (Schema::hasColumn('users', 'access_ends_at')) {
                $table->dropColumn('access_ends_at');
            }
            if (Schema::hasColumn('users', 'access_starts_at')) {
                $table->dropColumn('access_starts_at');
            }
        });
    }
};
