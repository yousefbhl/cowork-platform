<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('space_workspaces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('space_id')->constrained()->cascadeOnDelete();
            $table->foreignId('workspace_type_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('capacity')->default(1);
            $table->decimal('price_hourly', 10, 2)->nullable();
            $table->decimal('price_daily', 10, 2)->nullable();
            $table->decimal('price_monthly', 10, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('space_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('space_workspaces');
    }
};
