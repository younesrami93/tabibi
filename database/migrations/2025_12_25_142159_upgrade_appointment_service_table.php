<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('appointment_service', function (Blueprint $table) {
            $table->foreignId('medical_service_id')->nullable()->change();
            $table->string('custom_name')->nullable()->after('medical_service_id');

            $table->softDeletes(); // Adds 'deleted_at'
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('deleted_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('appointment_service', function (Blueprint $table) {
            $table->foreignId('medical_service_id')->nullable(false)->change();
            $table->dropColumn('custom_name');

            $table->dropSoftDeletes();
            $table->dropForeign(['updated_by']);
            $table->dropForeign(['deleted_by']);
            $table->dropColumn(['updated_by', 'deleted_by']);
        });
    }
};