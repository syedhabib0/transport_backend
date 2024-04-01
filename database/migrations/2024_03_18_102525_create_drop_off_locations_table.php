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
        Schema::create('drop_off_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('load_id')->constrained('loads');
            $table->decimal('latitude', 16, 8);
            $table->decimal('longitude', 16, 8);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drop_off_locations');
    }
};
