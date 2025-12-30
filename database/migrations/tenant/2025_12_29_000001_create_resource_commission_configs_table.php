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
        Schema::create('resource_commission_configs', function (Blueprint $table) {
            $table->id();

            // Polymorphic: Unit or Activity
            $table->string('resourceable_type');
            $table->unsignedBigInteger('resourceable_id');
            $table->index(['resourceable_type', 'resourceable_id'], 'resource_commission_morph_index');

            // Commission Type: percentage or fixed
            $table->enum('commission_type', ['percentage', 'fixed'])->default('percentage');

            // Percentage type: e.g., 5.00 = 5%
            $table->decimal('commission_percentage', 5, 2)->nullable();

            // Fixed type: e.g., 100000 = Rp 100.000
            $table->unsignedBigInteger('commission_fixed')->nullable();

            // Fixed unit: night, person, session, booking, hour, qty
            $table->enum('fixed_unit', ['night', 'person', 'session', 'booking', 'hour', 'qty'])->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Unique constraint: one config per resource
            $table->unique(['resourceable_type', 'resourceable_id'], 'unique_resource_commission');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resource_commission_configs');
    }
};
