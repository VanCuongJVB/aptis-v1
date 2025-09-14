<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class MakeUsersNameNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $driver = Schema::getConnection()->getDriverName();

        // Prefer using change() if doctrine/dbal is available.
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->string('name', 191)->nullable()->change();
            });
            return;
        } catch (\Throwable $e) {
            // continue to driver-specific fallback
        }

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE `users` MODIFY `name` VARCHAR(191) NULL');
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE users ALTER COLUMN name DROP NOT NULL');
            return;
        }

        // SQLite: modifying column nullability requires table rebuild; skip and log.
        // Developers using SQLite should make the change manually or install doctrine/dbal.
        // No-op for sqlite to avoid dangerous automatic rebuilds.
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $driver = Schema::getConnection()->getDriverName();
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->string('name', 191)->nullable(false)->change();
            });
            return;
        } catch (\Throwable $e) {
            // fallback
        }

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE `users` MODIFY `name` VARCHAR(191) NOT NULL');
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE users ALTER COLUMN name SET NOT NULL');
            return;
        }

        // SQLite: skip revert
    }
}
