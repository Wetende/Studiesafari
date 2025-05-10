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
        Schema::create('question_gap_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->string('gap_identifier'); // e.g., 'gap1', 'gap2', etc.
            $table->string('correct_text'); // The correct answer for this gap
            $table->boolean('case_sensitive')->default(false);
            $table->integer('points')->default(1); // Points for this specific gap
            $table->timestamps();
            
            // Index for quick lookups by question
            $table->index('question_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_gap_answers');
    }
}; 