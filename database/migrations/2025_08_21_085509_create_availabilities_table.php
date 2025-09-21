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
        Schema::create('availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            // $table->date('start_date');
            // $table->date('end_date');
            $table->unsignedInteger('qty')->nullable();
            $table->boolean('is_open')->nullable(); 
            $table->foreignId('rate_id')->nullable()->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('price')->nullable();
            $table->timestamps();

            // Prevent duplicates
            $table->unique(['unit_id', 'date', 'rate_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('availabilities');
    }
};
