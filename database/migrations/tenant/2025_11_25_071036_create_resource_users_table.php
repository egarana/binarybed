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
        Schema::create('resource_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('global_user_id')->constrained()->cascadeOnDelete();
            $table->morphs('resourceable'); // Creates resourceable_type and resourceable_id
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamps();

            // Unique constraint untuk prevent duplicate assignments
            $table->unique(['global_user_id', 'resourceable_type', 'resourceable_id'], 'unique_user_resource');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resource_users');
    }
};
