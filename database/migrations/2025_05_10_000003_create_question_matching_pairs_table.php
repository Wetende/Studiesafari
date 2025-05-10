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
        Schema::create('question_matching_pairs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->string('matching_pair_key'); // Unique key for this pair within the question
            $table->string('prompt_text')->nullable(); // Text for the prompt side
            $table->string('prompt_image_url')->nullable(); // Image URL for the prompt side
            $table->string('answer_text')->nullable(); // Text for the answer side
            $table->string('answer_image_url')->nullable(); // Image URL for the answer side
            $table->integer('order')->default(0); // Display order of the pair
            $table->integer('points')->default(1); // Points for this match
            $table->timestamps();
            
            // Indexes
            $table->index('question_id');
            $table->unique(['question_id', 'matching_pair_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_matching_pairs');
    }
}; 