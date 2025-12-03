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
        Schema::create('resource_features', function (Blueprint $table) {
            $table->id();

            // Foreign key ke features.id dari central database
            $table->unsignedBigInteger('feature_id');
            // Note: Foreign key constraint tidak bisa dibuat karena cross-database
            // Constraint akan dienforce di application level

            // Polymorphic columns untuk attach ke berbagai resources (Unit, Activity, etc)
            $table->string('featureable_type');
            $table->unsignedBigInteger('featureable_id');
            $table->index(['featureable_type', 'featureable_id']);

            // Order column untuk sorting features
            $table->unsignedInteger('order')->default(0);

            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamps();

            // Unique constraint untuk prevent duplicate assignments
            $table->unique(['feature_id', 'featureable_type', 'featureable_id'], 'unique_feature_resource');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resource_features');
    }
};
