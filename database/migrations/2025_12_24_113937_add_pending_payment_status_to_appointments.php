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
        Schema::table('appointments', function (Blueprint $table) {
            DB::statement("ALTER TABLE appointments MODIFY COLUMN status ENUM('scheduled', 'waiting', 'preparing', 'in_consultation', 'pending_payment', 'finished', 'cancelled', 'no_show') DEFAULT 'scheduled'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            DB::statement("ALTER TABLE appointments MODIFY COLUMN status ENUM('scheduled', 'waiting', 'preparing', 'in_consultation', 'finished', 'cancelled', 'no_show') DEFAULT 'scheduled'");
        });
    }
};
