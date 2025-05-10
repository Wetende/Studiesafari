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
        Schema::create('assignment_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->text('content')->nullable(); // Student's text submission
            $table->json('attachments')->nullable(); // Array of cloud file paths
            $table->decimal('grade', 8, 2)->nullable(); // Can be points or percentage
            $table->timestamp('graded_at')->nullable();
            $table->text('teacher_feedback')->nullable();
            $table->foreignId('grading_teacher_id')->nullable()->constrained('users'); // Teacher who graded
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for common queries
            $table->index('assignment_id');
            $table->index('user_id');
            $table->index('grading_teacher_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_submissions');
    }
}; 