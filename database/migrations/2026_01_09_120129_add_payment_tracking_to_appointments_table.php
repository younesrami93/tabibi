<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Amount actually received specifically for this appointment
            $table->decimal('paid_amount', 12, 2)->default(0)->after('total_price');
            // Amount still owed (Total - Paid). 
            // If > 0, the appointment has "Credit" (Debt).
            $table->decimal('due_amount', 12, 2)->default(0)->after('paid_amount');
        });
    }

    public function down()
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['paid_amount', 'due_amount']);
        });
    }
};