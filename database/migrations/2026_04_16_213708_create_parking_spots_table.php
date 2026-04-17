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
        Schema::create('parking_spots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained('parking_locations')->cascadeOnDelete();
            $table->string('spot_number');
            $table->string('status')->default('Available'); // Available / Occupied / Maintenance
            $table->string('status_source')->default('admin'); // sensor / reservation / admin
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parking_spots');
    }
};
