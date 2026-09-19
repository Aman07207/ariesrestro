<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            // The business's actual GST registration number — distinct from gst_rate
            // (the rate charged on bills). Nullable: not every hotel has one on file yet.
            $table->string('gstin', 15)->nullable()->after('slug');
            $table->string('logo')->nullable()->after('gstin');
            $table->string('google_review_link')->nullable()->after('logo');
        });
    }

    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropColumn(['gstin', 'logo', 'google_review_link']);
        });
    }
};
