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
        Schema::create('vehicle_other_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles');
            $table->integer('length');
            $table->integer('height');
            $table->integer('width');
            $table->string('dimension_in');
            $table->boolean('is_available')->default(true);
            $table->boolean('lift_gate')->default(false);
            $table->boolean('hazmat')->default(false);
            $table->boolean('icc_bar')->default(false);
            $table->boolean('tsa')->default(false);
            $table->boolean('twic')->default(false);
            $table->boolean('pallet_jack')->default(false);
            $table->boolean('true_dock_high')->default(false);
            $table->boolean('tanker_endorsement')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_other_details');
    }
};
