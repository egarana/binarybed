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
        Schema::create('reservation_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();

            $table->date('date'); // tanggal menginap
            $table->foreignId('rate_id')->nullable()->constrained()->nullOnDelete();

            $table->unsignedInteger('qty')->default(1);
            $table->decimal('base_price', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('service_fee', 15, 2)->default(0);
            $table->decimal('extra_charge', 15, 2)->default(0);
            $table->decimal('total_price', 15, 2)->default(0);
            $table->string('currency', 3)->default('IDR');

            $table->timestamps();

            $table->unique(['reservation_id', 'date', 'rate_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservation_details');
    }
};
