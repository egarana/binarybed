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
        Schema::create('settlement_distributions', function (Blueprint $table) {
            $table->id();

            // Reservation reference
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reservation_item_id')->constrained()->cascadeOnDelete();

            // Recipient: merchant (tenant), partner, or platform
            $table->enum('recipient_type', ['merchant', 'partner', 'platform']);

            // Recipient ID: tenant_id for merchant, global_id for partner, null for platform
            $table->string('recipient_id')->nullable();

            // Amount to be distributed
            $table->unsignedBigInteger('amount');
            $table->string('currency', 3)->default('IDR');

            // Disbursement status
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');

            // Xendit disbursement reference
            $table->string('disbursement_id')->nullable();
            $table->timestamp('disbursed_at')->nullable();

            // Granular snapshot for audit trail (IFRS compliance)
            $table->json('snapshot')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['reservation_id', 'recipient_type']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settlement_distributions');
    }
};
