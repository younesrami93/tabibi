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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            // Relationship to Clinic (All docs belong to a clinic)
            $table->foreignId('clinic_id')->constrained()->onDelete('cascade');

            // Relationship to Doctor (The specific owner of the file)
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Document Metadata
            $table->string('name');
            $table->string('role')->nullable(); // As requested, text for now
            $table->json('content')->nullable(); // The JSON canvas data

            // Blameable Trait Requirement
            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamps();
            $table->softDeletes(); // Soft Delete support

            // Foreign key for created_by (optional, but good practice)
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
