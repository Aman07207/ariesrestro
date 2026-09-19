<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('session_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('order_sessions')->cascadeOnDelete();
            $table->unsignedTinyInteger('member_no');
            $table->string('device_token')->nullable();
            $table->timestamps();

            $table->unique(['session_id', 'member_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_members');
    }
};
