<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            // 'auto' defers to the platform default (and from there to real weather,
            // or the calendar-month fallback) — a hotel only needs this set when it
            // wants to override that chain with a fixed season of its own.
            $table->string('weather_theme_mode')->default('auto')->after('geofence_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropColumn('weather_theme_mode');
        });
    }
};
