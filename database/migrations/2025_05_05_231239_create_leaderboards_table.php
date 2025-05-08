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
        Schema::create('leaderboards', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // e.g., 'course', 'subject', 'global'
            $table->foreignId('course_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->nullable(); // FK added dynamically
            $table->timestamps();

            // Add foreign key constraint if subjects table exists
            if (Schema::hasTable('subjects')) {
                $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('set null');
            }
        });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('leaderboards');
    }
};
