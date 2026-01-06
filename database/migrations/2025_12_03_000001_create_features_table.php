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
        Schema::create('features', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Display name: 'Free WiFi', 'Breakfast Included'
            $table->string('value')->unique(); // Unique identifier: 'wifi', 'breakfast', 'guide'
            $table->text('description')->nullable(); // Optional description
            $table->text('icon')->nullable(); // Icon class atau SVG
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('features');
    }
};
