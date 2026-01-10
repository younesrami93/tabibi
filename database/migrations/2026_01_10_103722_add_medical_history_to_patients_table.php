<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up()
    {
        Schema::table('patients', function (Blueprint $table) {
            // Adding the column after 'address' to keep structure clean
            $table->text('medical_history')->nullable()->after('address');
        });
    }

    public function down()
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn('medical_history');
        });
    }
};
