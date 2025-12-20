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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained();
            $table->foreignId('patient_id')->constrained();
            $table->foreignId('doctor_id')->constrained('users');

            // "Control" Logic: Link to the previous appointment if this is a follow-up
            $table->foreignId('parent_appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->enum('type', ['consultation', 'control', 'urgency'])->default('consultation');

            // Status Workflow
            $table->enum('status', ['scheduled', 'waiting', 'in_consultation', 'finished', 'cancelled', 'no_show'])
                ->default('scheduled');

            // Timing
            $table->dateTime('scheduled_at');
            $table->dateTime('started_at')->nullable();
            $table->dateTime('finished_at')->nullable();

            // Financial Snapshot (Total of all services)
            $table->decimal('total_price', 10, 2)->default(0);
            $table->boolean('is_paid')->default(false);

            $table->text('notes')->nullable(); // Medical notes
            $table->timestamps();
            $table->softDeletes();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
        });


        Schema::create('appointment_service', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('medical_service_id')->constrained();

            // We save the price HERE too. 
            // Why? If you raise your prices next year, old appointment records shouldn't change.
            $table->decimal('price', 10, 2);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
