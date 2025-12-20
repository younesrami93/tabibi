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
        // 1. Update CLINICS Table
        if (Schema::hasTable('clinics') && !Schema::hasColumn('clinics', 'created_by')) {
            Schema::table('clinics', function (Blueprint $table) {
                // We use 'nullable' because existing clinics didn't have a creator recorded
                $table->foreignId('created_by')->nullable()->after('id')->constrained('users')->nullOnDelete();
            });
        }

        // 2. Update USERS Table
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'created_by')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('created_by')->nullable()->after('id')->constrained('users')->nullOnDelete();
            });
        }
        if (Schema::hasTable('patients') && !Schema::hasColumn('patients', 'created_by')) {
            Schema::table('patients', function (Blueprint $table) {
                $table->foreignId('created_by')->nullable()->after('id')->constrained('users')->nullOnDelete();
            });
        }
        if (Schema::hasTable('medical_services') && !Schema::hasColumn('medical_services', 'created_by')) {
            Schema::table('medical_services', function (Blueprint $table) {
                $table->foreignId('created_by')->nullable()->after('id')->constrained('users')->nullOnDelete();
            });
        }
    }


    /**
     * Reverse the migrations.
     */

    public function down()
    {
        // Reverse the changes if needed
        if (Schema::hasTable('clinics')) {
            Schema::table('clinics', function (Blueprint $table) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            });
        }
        if (Schema::hasTable('patients')) {
            Schema::table('patients', function (Blueprint $table) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            });
        }

        if (Schema::hasTable('medical_services')) {
            Schema::table('medical_services', function (Blueprint $table) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            });
        }
    }

};
