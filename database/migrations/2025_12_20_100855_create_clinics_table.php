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
        Schema::create('clinics', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Cabinet Dr. Younes"
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('logo')->nullable();

            // SUBSCRIPTION LOGIC (Simple for now)
            $table->string('plan_type')->default(value: 'free_trial'); // free_trial, basic, pro
            $table->date('subscription_expires_at')->nullable();
            $table->boolean('is_active')->default(true); // If false, nobody can login

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinics');
    }
};
