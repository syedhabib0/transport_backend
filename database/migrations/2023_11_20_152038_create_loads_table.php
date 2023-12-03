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
        Schema::create('loads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('driver_id')->nullable()->constrained('drivers');
            $table->string('load_type');
            $table->string('weight');
            $table->string('destination');
            $table->string('pickup_location');
            $table->string('dropoff_location');
            $table->date('pickup_date');
            $table->string('delivery_date');
            $table->string('status')->default('available');
            $table->decimal('driver_earnings', 8, 2)->nullable(); // Add this line
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loads');
    }
};
