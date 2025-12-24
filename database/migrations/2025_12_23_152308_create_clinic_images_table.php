<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('clinic_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->onDelete('cascade');

            $table->string('path');      // The storage path (e.g., clinic_images/1/file.jpg)
            $table->string('filename');  // The original name (e.g., logo.png)
            $table->string('mime_type')->nullable();
            $table->integer('size')->nullable(); // In bytes

            // Traits
            $table->unsignedBigInteger('created_by')->nullable(); // Blameable
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();

            $table->softDeletes(); // Soft Deletes
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_images');
    }
};