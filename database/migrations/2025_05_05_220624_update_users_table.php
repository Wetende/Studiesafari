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
        Schema::table('users', function (Blueprint $table) {
           
            $table->string('first_name')->after('id')->nullable(); // Add if not replacing 'name'
            $table->string('last_name')->after('first_name')->nullable(); // Add if not replacing 'name'
            $table->string('profile_picture_path')->nullable();
            $table->string('phone_number')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->softDeletes(); // Add soft deletes if not present
            
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn([
                'first_name',
                'last_name',
                'profile_picture_path',
                'phone_number',
                'phone_verified_at',
            ]);
            
          
        });
    }
};
