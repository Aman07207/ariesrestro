<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Replaces `amount` as the source of truth for what was actually charged —
            // `amount` stays for now (nothing has ever written to it; no real payment
            // gateway flow exists yet) but bill_id -> bills.grand_total is authoritative.
            $table->foreignId('bill_id')->nullable()->after('session_id')->constrained('bills')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bill_id');
        });
    }
};
