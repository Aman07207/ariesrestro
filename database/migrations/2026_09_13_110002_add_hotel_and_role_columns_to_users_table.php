<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('hotel_id')->nullable()->after('id')
                ->constrained('hotels')->cascadeOnDelete();
            $table->string('employee_id')->nullable()->unique()->after('hotel_id');
            $table->string('role')->after('employee_id');
            $table->string('phone')->nullable()->after('role');
            $table->string('section')->nullable()->after('phone');
            $table->string('shift')->nullable()->after('section');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('hotel_id');
            $table->dropColumn(['employee_id', 'role', 'phone', 'section', 'shift']);
        });
    }
};
