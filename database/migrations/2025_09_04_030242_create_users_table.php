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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            
            // Role & Status
            $table->enum('role', ['admin', 'student'])->default('student');
            $table->boolean('is_active')->default(true);
            
            // Access Time Control
            $table->timestamp('access_expires_at')->nullable()->comment('Hết hạn truy cập');
            
            // Device Limit Control (stores device fingerprints as JSON)
            $table->json('active_devices')->nullable()->comment('Max 2 devices');
            
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['role', 'is_active']);
            $table->index('access_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
