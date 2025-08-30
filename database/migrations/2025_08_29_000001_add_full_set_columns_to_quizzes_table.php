<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->boolean('is_full_set')->default(false)->after('is_published');
            // Make part nullable since full sets don't have a specific part
            $table->integer('part')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn('is_full_set');
            // Revert part to non-nullable
            $table->integer('part')->nullable(false)->change();
        });
    }
};
