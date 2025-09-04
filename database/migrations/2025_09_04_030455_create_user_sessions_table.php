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
        Schema::create('user_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            
            // Device identification
            $table->string('device_fingerprint')->unique();
            $table->string('device_name')->nullable(); // Browser name, OS, etc.
            
            // Session info
            $table->ipAddress('ip_address');
            $table->text('user_agent');
            
            // Status
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_active_at');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'is_active']);
            $table->index('device_fingerprint');
            $table->index('last_active_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_sessions');
    }
};
