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
        Schema::create('reservation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();

            // Polymorphic: Unit or Activity
            $table->nullableMorphs('reservable');

            // Rate Reference (for live lookup if needed)
            $table->foreignId('rate_id')->nullable()->constrained()->nullOnDelete();

            // === DATES & TIMES ===
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            // === QUANTITY ===
            $table->integer('quantity')->default(1);           // Rooms/persons

            // === GRANULAR SNAPSHOTTING ===
            // Resource Snapshot (captured at booking time)
            $table->string('resource_name')->nullable();       // "Deluxe Room A"
            $table->string('resource_type_label')->nullable(); // "Room" / "Activity"
            $table->text('resource_description')->nullable();

            // Rate Snapshot (captured at booking time)
            $table->string('rate_name')->nullable();           // "Standard Rate"
            $table->text('rate_description')->nullable();
            $table->string('price_type')->nullable();         // "night", "person", "hourly", "flat"

            // Price Snapshot (CRITICAL for accounting!)
            $table->unsignedBigInteger('rate_price');          // Harga satuan saat booking
            $table->string('currency', 3)->default('IDR');
            $table->unsignedBigInteger('line_total');          // Total kalkulasi

            // Item Status
            $table->enum('status', ['ACTIVE', 'CANCELLED'])->default('ACTIVE');

            $table->timestamps();

            // Indexes (reservation_id foreign key and morphs already indexed automatically)
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservation_items');
    }
};
