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

        Schema::table('medical_services', function (Blueprint $table) {
            // The Insurance Code (e.g. "K20")
            $table->string('code')->nullable()->after('name');

            // A small description or instructions
            $table->text('description')->nullable()->after('price');

            // Bonus: Duration is critical for the calendar (default 30 mins)
            $table->integer('duration_minutes')->default(30)->after('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['code', 'description', 'duration_minutes']);
        });
    }
};
