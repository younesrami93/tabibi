<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('catalog_items', function (Blueprint $table) {
            $table->id();

            // Scope: NULL = Global (System), ID = Private (Doctor's custom item)
            $table->foreignId('clinic_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            // Type
            $table->enum('type', ['medicine', 'test']);

            // Core Details
            $table->string('name');             // e.g., "Doliprane" or "CBC Test"
            $table->string('form')->nullable(); // e.g., "Tablet", "Syrup" (Medicines only)
            $table->string('strength')->nullable(); // e.g., "1000mg", "500mg"

            // THE SMART DEFAULTS (Pre-fill the prescription inputs)
            // Example: "Take [1] tablet, [3] times a day, for [5] days"
            $table->integer('default_quantity')->nullable(); // e.g., 1
            $table->integer('default_frequency')->nullable(); // e.g., 3
            $table->integer('default_duration')->nullable(); // e.g., 5

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('catalog_items');
    }
};