<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('appointment_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            // The status that was applied
            $table->string('status');
            // Who did it?
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            // created_at will serve as the timestamp of the event
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_histories');
    }
};