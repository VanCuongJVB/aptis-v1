<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('questions', 'point')) {
            Schema::table('questions', function (Blueprint $table) {
                $table->integer('point')->default(1)->after('order');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('questions', 'point')) {
            Schema::table('questions', function (Blueprint $table) {
                $table->dropColumn('point');
            });
        }
    }
};
