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
        Schema::create('user_bank_accounts', function (Blueprint $table) {
            $table->id();

            // Foreign key to users.global_id
            $table->string('global_id');
            $table->foreign('global_id')
                ->references('global_id')
                ->on('users')
                ->cascadeOnDelete();

            // Bank account details
            $table->string('bank_code', 20); // BCA, MANDIRI, BNI, etc.
            $table->string('account_number');
            $table->string('account_holder_name');

            // Primary account flag
            $table->boolean('is_primary')->default(false);

            // Verification status
            $table->boolean('is_verified')->default(false);

            // Xendit beneficiary reference (optional)
            $table->string('xendit_beneficiary_id')->nullable();

            $table->timestamps();

            // Index for lookup
            $table->index('global_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_bank_accounts');
    }
};
