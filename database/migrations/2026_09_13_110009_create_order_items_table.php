<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            // restrictOnDelete: a menu item must never be deletable out from under historical orders.
            $table->foreignId('menu_item_id')->constrained('menu_items')->restrictOnDelete();
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('quantity')->default(1);
            $table->string('status')->default('pending');
            $table->text('note')->nullable();
            $table->string('cancel_reason')->nullable();
            // nullOnDelete (not cascade): removing a staff account must never silently delete order history.
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
