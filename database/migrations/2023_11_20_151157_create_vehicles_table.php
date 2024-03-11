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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained('drivers');
            $table->enum('vehicle_type', [
                'sprinter-vans',
                'box-trucks',
                'reefers',
                'hazmat',
                'straight-trucks',
                'dry-van',
                'flatbed',
                'conestoga',
                 ]);
            $table->string('unit_number');
            $table->string('make');
            $table->string('model');
            $table->integer('payload_weight');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
