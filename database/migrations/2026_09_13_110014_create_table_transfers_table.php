<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('table_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('order_sessions')->cascadeOnDelete();
            $table->foreignId('old_table_id')->constrained('tables')->cascadeOnDelete();
            $table->foreignId('new_table_id')->constrained('tables')->cascadeOnDelete();
            // nullOnDelete: removing a waiter account must not delete the transfer history.
            $table->foreignId('transferred_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('table_transfers');
    }
};
