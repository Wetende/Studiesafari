<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Added for seeding

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if the table already exists
        if (Schema::hasTable('subjects')) {
            return;
        }
        
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            // Add columns based on your database plan
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Seed subjects
        DB::table('subjects')->insert([
            ['name' => 'Mathematics', 'slug' => 'mathematics', 'description' => 'Study of numbers, quantities, and shapes', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Physics', 'slug' => 'physics', 'description' => 'Study of matter, energy, and the interaction between them', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Chemistry', 'slug' => 'chemistry', 'description' => 'Study of substances, their properties, and reactions', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Biology', 'slug' => 'biology', 'description' => 'Study of living organisms', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'English', 'slug' => 'english', 'description' => 'Study of the English language', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'History', 'slug' => 'history', 'description' => 'Study of past events', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Geography', 'slug' => 'geography', 'description' => 'Study of places and relationships between people and their environments', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
