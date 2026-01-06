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
            $table->unsignedBigInteger('feature_id'); // Reference to central features.id
            $table->string('name');
            $table->string('value')->index();
            $table->text('description')->nullable();
            $table->text('icon')->nullable();
            $table->timestamps();

            // Index untuk sync
            $table->unique('feature_id');
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
