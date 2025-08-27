<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 191)->nullable();
            $table->string('email', 191)->unique();
            $table->string('password', 255);
            $table->boolean('is_admin')->default(false)->comment('Admin = quản trị');
            $table->boolean('is_active')->default(true)->comment('Bật/tắt tài khoản');
            $table->timestamp('access_starts_at')->nullable()->comment('Bắt đầu truy cập');
            $table->timestamp('access_ends_at')->nullable()->comment('Hết hạn truy cập');
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->index(['is_active','access_starts_at','access_ends_at'], 'users_access_window_idx');
        });
    }
    public function down(): void { Schema::dropIfExists('users'); }
};
