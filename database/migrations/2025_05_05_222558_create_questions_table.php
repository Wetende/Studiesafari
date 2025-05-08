<?php

declare(strict_types=1);

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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->onDelete('cascade');
            $table->text('text')->comment('The question prompt/stem');
            $table->enum('type', [
                'single_choice', 
                'multiple_response', 
                'true_false', 
                'matching', 
                'image_matching', 
                'keywords', 
                'fill_in_the_gap', 
                'text_input'
            ]);
            $table->json('options')->comment('Structure varies by question type');
            $table->json('correct_answer')->comment('Structure varies by question type');
            $table->integer('points')->default(1);
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
