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
            $table->foreignId('unit_no')->nullable()->constrained('vehicles');
            $table->string('bill_id')->nullable();
            $table->string('load_type')->nullable();
            $table->string('weight')->nullable();
            $table->string('destination')->nullable();
            $table->string('pickup_location');
            $table->string('dropoff_location');
            $table->string('pickup_time');
            $table->string('dropoff_time');
            $table->date('pickup_date')->nullable();
            $table->string('delivery_date')->nullable();
            $table->enum('status', ['available', 'active', 'on-going', 'cancelled', 'delivered'])->default('available');
            $table->decimal('total_fare', 8, 2)->nullable(); // Add this line
            $table->decimal('driver_fare', 8, 2)->nullable(); // Add this line
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
