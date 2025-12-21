<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('prescription_templates', function (Blueprint $table) {
            $table->id();

            // Belongs to a specific clinic
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            // Name of the protocol (e.g., "Flu Treatment - Adult")
            $table->string('name');

            $table->enum('type', ['medicine', 'test', 'mixed']);

            // The Recipe: List of Catalog IDs + Dosages
            // Structure: [ {"catalog_item_id": 5, "note": "2 tabs per day"}, ... ]
            $table->json('items');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('prescription_templates');
    }
};