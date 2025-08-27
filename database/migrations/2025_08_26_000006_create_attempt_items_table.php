<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('attempt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained('attempts')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->json('selected_option_ids')->nullable();
            $table->boolean('is_correct')->default(false);
            $table->integer('time_spent_sec')->default(0);
            $table->timestamps();
            $table->index(['attempt_id','question_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('attempt_items'); }
};
