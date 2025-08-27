<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('user_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('session_id', 100)->unique();
            $table->string('device_fingerprint', 64)->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->index(['user_id','device_fingerprint']);
        });
    }
    public function down(): void { Schema::dropIfExists('user_sessions'); }
};
