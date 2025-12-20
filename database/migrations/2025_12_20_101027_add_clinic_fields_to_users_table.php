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


        Schema::table('users', function (Blueprint $table) {
            // Link User to a Clinic
            // 'nullable' because the Super Admin (You) might not belong to a clinic
            $table->foreignId('clinic_id')->nullable()->after('id')->constrained()->onDelete('cascade');

            // Role: 'super_admin', 'doctor', 'secretary'
            $table->string('role')->default('doctor')->after('email');

            // Extra profile fields
            $table->string('phone')->nullable()->after('role');
            $table->boolean('is_active')->default(true)->after('phone');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['clinic_id']);
            $table->dropColumn(['clinic_id', 'role', 'phone', 'is_active']);
        });
    }
};
