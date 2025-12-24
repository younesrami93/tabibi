<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('doccument_make_it_enum', function (Blueprint $table) {


            DB::table('documents')->whereNull('role')->orWhere('role', '')->update(['role' => 'general']);

            // 2. Modify Column: Change 'role' to ENUM
            DB::statement("
            ALTER TABLE documents 
            MODIFY COLUMN role 
            ENUM('general', 'prescription', 'invoice', 'medical_certificate', 'referral_letter', 'medical_report', 'consent_form') 
            NOT NULL DEFAULT 'general'
        ");

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('doccument_make_it_enum', function (Blueprint $table) {
            DB::statement("ALTER TABLE documents MODIFY COLUMN role VARCHAR(255) NULL");
        });
    }
};
