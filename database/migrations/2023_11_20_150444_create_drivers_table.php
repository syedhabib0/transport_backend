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
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('profile_id')->constrained('profiles');
            $table->foreignId('hired_by')->nullable()->constrained('users');
            $table->enum('status', [
                'available',
                'not-available',
                'will-be-available',
                'under-our-load',
                'under-our-bid',
                'suspended'
                ])->default('available');
            $table->text('note')->nullable();
            $table->text('location')->nullable();
            $table->text('area')->nullable();
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
