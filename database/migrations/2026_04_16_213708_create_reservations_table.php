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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('spot_id')->constrained('parking_spots');
            $table->foreignId('vehicle_id')->constrained('vehicles');
            $table->timestamp('start_at');
            $table->timestamp('actual_start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->timestamp('actual_exit_at')->nullable();
            $table->timestamp('reminder_sent_at')->nullable();
            $table->decimal('total_price', 8, 2)->default(0);
            $table->string('status')->default('Pending'); // Pending / Active / Completed / Cancelled
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
