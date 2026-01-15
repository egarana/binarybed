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
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('subtitle')->nullable(); // e.g. 'Entire cabin'
            $table->text('description')->nullable();

            // Capacity & Features
            $table->integer('max_guests')->default(2);
            $table->integer('bedroom_count')->default(1);
            $table->integer('bathroom_count')->default(1);
            $table->string('view'); // e.g. 'Lake View'
            $table->json('selling_points')->nullable();
            $table->json('location')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
