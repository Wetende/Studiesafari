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
        // Skip migration if past_papers table already exists
        if (Schema::hasTable('past_papers')) {
            return;
        }
        
        // Ensure subjects table exists before creating foreign key
        if (!Schema::hasTable('subjects')) {
            Schema::create('subjects', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        Schema::create('past_papers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->nullable();
            $table->foreignId('user_id')->comment('Uploaded by')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->integer('exam_year');
            $table->string('exam_level');
            $table->integer('download_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            // Add foreign key constraint
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('past_papers');
        // Do not drop subjects table here as it might be used by other migrations
    }
};
