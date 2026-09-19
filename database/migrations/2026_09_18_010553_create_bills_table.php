<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('order_sessions')->cascadeOnDelete();
            $table->foreignId('hotel_id')->constrained('hotels')->cascadeOnDelete();
            $table->decimal('food_base_amount', 10, 2)->default(0);
            $table->decimal('alcohol_base_amount', 10, 2)->default(0);
            $table->decimal('service_charge_percent', 4, 2)->default(0);
            $table->decimal('service_charge_amount', 10, 2)->default(0);
            // Did the customer actually agree — never assumed, never defaulted true.
            $table->boolean('service_charge_consent')->default(false);
            $table->decimal('gst_rate', 4, 2)->default(0);
            $table->decimal('cgst_amount', 10, 2)->default(0);
            $table->decimal('sgst_amount', 10, 2)->default(0);
            $table->decimal('vat_rate', 4, 2)->nullable();
            $table->decimal('vat_amount', 10, 2)->default(0);
            $table->decimal('grand_total', 10, 2)->default(0);
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
