<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_payment_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->unique()->constrained('hotels')->cascadeOnDelete();
            $table->string('razorpay_linked_account_id')->nullable();
            // Stored via the model's `encrypted` cast — never plain text, even at rest in our own DB.
            $table->text('razorpay_key_id')->nullable();
            $table->text('razorpay_key_secret')->nullable();
            $table->decimal('commission_percent', 5, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_payment_settings');
    }
};
