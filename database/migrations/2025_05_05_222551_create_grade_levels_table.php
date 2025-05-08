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
        Schema::create('grade_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->integer('level_order')->unique(); // For sorting
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Seed grade levels
        DB::table('grade_levels')->insert([
            ['name' => 'S1', 'level_order' => 1, 'description' => 'Senior 1', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'S2', 'level_order' => 2, 'description' => 'Senior 2', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'S3', 'level_order' => 3, 'description' => 'Senior 3', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'S4', 'level_order' => 4, 'description' => 'Senior 4', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'S5', 'level_order' => 5, 'description' => 'Senior 5', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'S6', 'level_order' => 6, 'description' => 'Senior 6', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grade_levels');
    }
};
