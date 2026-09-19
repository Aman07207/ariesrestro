<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            // 5.00 for a standalone restaurant, 18.00 if the venue is inside a luxury
            // hotel with room tariffs over ₹7,500/night — is_luxury_hotel is UI guidance
            // for which value to set here, the billing engine always reads this column
            // directly rather than deriving the rate from that flag.
            $table->decimal('gst_rate', 4, 2)->default(5.00)->after('subscription_plan');
            // State-specific (roughly 20-25%), no sane global default — null until the
            // hotel actually serves alcohol and configures it.
            $table->decimal('vat_rate', 4, 2)->nullable()->after('gst_rate');
            $table->boolean('is_luxury_hotel')->default(false)->after('vat_rate');
            // Null, not zero-by-default: this is "what to pre-fill the % as if the
            // customer opts in," not a rate that's ever applied without consent.
            $table->decimal('default_service_charge_percent', 4, 2)->nullable()->after('is_luxury_hotel');
        });
    }

    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropColumn(['gst_rate', 'vat_rate', 'is_luxury_hotel', 'default_service_charge_percent']);
        });
    }
};
