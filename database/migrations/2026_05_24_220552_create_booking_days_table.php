<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('space_workspace_id')->constrained()->cascadeOnDelete();
            $table->date('day');
            $table->timestamps();

            // Atomic double-booking guard: same workspace cannot have two bookings on same day
            $table->unique(['space_workspace_id', 'day']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_days');
    }
};
