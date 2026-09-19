<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            // Drives the billing engine directly — GST (food/non-alcoholic) vs state
            // VAT (alcohol). Independent of menu_categories.type, which is a display/
            // organization concept ("Beverages" includes Cold Coffee, non-alcoholic).
            $table->string('tax_track')->default('gst')->after('veg_type');
        });
    }

    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropColumn('tax_track');
        });
    }
};
