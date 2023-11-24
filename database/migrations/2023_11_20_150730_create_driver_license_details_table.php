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
        Schema::create('driver_license_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained('drivers');
            $table->string('license_number');
            $table->date('license_expiry_date');
            $table->boolean('is_expirable')->default(true);
            $table->string('license_issuance_country');
            $table->string('license_issuance_state');
            $table->string('license_photo_front')->nullable();
            $table->string('license_photo_back')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_license_details');
    }
};
