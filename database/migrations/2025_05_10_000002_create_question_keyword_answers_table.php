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
        Schema::create('question_keyword_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->string('acceptable_keyword'); // The accepted keyword
            $table->boolean('case_sensitive')->default(false);
            $table->integer('points_per_keyword')->default(1); // Points for finding this keyword
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
        Schema::dropIfExists('question_keyword_answers');
    }
}; 