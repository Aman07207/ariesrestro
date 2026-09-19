<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained('hotels')->cascadeOnDelete();
            $table->unsignedInteger('table_number');
            $table->uuid('table_uuid')->unique();
            $table->unsignedTinyInteger('seating_capacity')->nullable();
            $table->string('status')->default('available');
            $table->timestamps();

            $table->unique(['hotel_id', 'table_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tables');
    }
};
