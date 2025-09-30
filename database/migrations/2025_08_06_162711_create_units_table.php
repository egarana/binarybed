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
            $table->foreignId('property_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->integer('qty')->default(0);
            $table->string('type')->default('unit');
            $table->integer('size')->default(0);
            $table->string('bed_size')->nullable();
            $table->string('view')->nullable();
            $table->integer('occupancy')->default(1);
            $table->boolean('free_breakfast')->default(false);
            $table->json('features')->default(DB::raw('(JSON_ARRAY())'))->nullable();
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
