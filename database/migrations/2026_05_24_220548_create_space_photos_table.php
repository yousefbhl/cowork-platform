<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('space_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('space_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->unsignedInteger('order')->default(0);
            $table->boolean('is_cover')->default(false);
            $table->timestamps();

            $table->index(['space_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('space_photos');
    }
};
