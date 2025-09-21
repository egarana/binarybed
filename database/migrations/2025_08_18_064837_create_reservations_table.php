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
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rate_id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->json('phone')->default(DB::raw('(JSON_ARRAY())'))->nullable();
            $table->date('check_in');
            $table->date('check_out');
            $table->dateTime('booked_on');
            $table->unsignedInteger('sort_order')->default(1);
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
