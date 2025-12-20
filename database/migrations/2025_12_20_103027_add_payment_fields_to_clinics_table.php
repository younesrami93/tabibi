<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */

    public function up()
    {
        Schema::table('clinics', function (Blueprint $table) {
            // Financial tracking for the SaaS Subscription
            $table->decimal('subscription_price', 10, 2)->default(0)->after('plan_type'); // e.g., 2000.00
            $table->decimal('total_paid', 10, 2)->default(0)->after('subscription_price'); // e.g., 1500.00

            // Optional: Notes to track "He promised to pay next week"
            $table->text('admin_notes')->nullable()->after('is_active');
        });
    }

    public function down()
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->dropColumn(['subscription_price', 'total_paid', 'admin_notes']);
        });
    }
};
