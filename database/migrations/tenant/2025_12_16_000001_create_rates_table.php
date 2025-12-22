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
        Schema::create('rates', function (Blueprint $table) {
            $table->id();

            // Polymorphic relation to Unit, Activity, etc.
            $table->nullableMorphs('rateable');

            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('price')->default(0);
            $table->string('currency', 3)->default('IDR');
            $table->string('price_type')->default('flat'); // e.g. flat, nightly, person, hourly, daily, session, pax_night
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            // Unique slug per resource (or globally if no resource)
            $table->unique(['rateable_type', 'rateable_id', 'slug'], 'unique_rate_slug_per_resource');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rates');
    }
};
