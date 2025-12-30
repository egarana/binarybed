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

            // Foreign key ke users.global_id (konsisten dengan nama kolom di tabel users)
            $table->string('global_id');
            $table->foreign('global_id')
                ->references('global_id')
                ->on('users')
                ->cascadeOnDelete();

            // Polymorphic columns
            $table->string('resourceable_type');
            $table->unsignedBigInteger('resourceable_id');
            $table->index(['resourceable_type', 'resourceable_id']);

            // Role column: 'partner' or 'referrer'
            $table->string('role')->nullable()->comment('partner, referrer');

            // Commission split percentage for this user on this resource
            $table->decimal('commission_split', 5, 2)->default(70.00)->comment('Partner commission percentage');

            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamps();

            // Unique constraint untuk prevent duplicate assignments
            $table->unique(['global_id', 'resourceable_type', 'resourceable_id'], 'unique_user_resource');
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
