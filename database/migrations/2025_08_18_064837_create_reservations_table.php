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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();

            // Referensi
            $table->string('reservation_code')->unique(); // e.g. "RSV-20250922-0001"

            // Relasi
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rate_id')->nullable()->constrained()->nullOnDelete(); 

            // Guest info
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->json('phone')->default(DB::raw('(JSON_ARRAY())'))->nullable();

            // Stay info
            $table->date('check_in');
            $table->date('check_out');
            $table->unsignedInteger('guests')->default(1);
            $table->unsignedInteger('qty')->default(1);

            // Pricing
            $table->decimal('total_price', 15, 2)->default(0);
            $table->string('currency', 3)->default('IDR'); // ISO 4217

            // Statuses
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'checked_in', 'checked_out'])->default('pending');
            $table->enum('payment_status', ['unpaid', 'paid', 'refunded'])->default('unpaid');

            // Meta
            $table->dateTime('booked_on')->nullable();
            $table->unsignedInteger('sort_order')->default(1);
            $table->text('notes')->nullable();
            $table->string('source')->default('direct');

            $table->timestamps();

            // Indexes
            $table->index(['check_in', 'check_out']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
