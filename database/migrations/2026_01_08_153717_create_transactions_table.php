<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            
            // 1. Context (Who and Where)
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained(); // The person handling the money
            $table->foreignId('patient_id')->nullable()->constrained()->nullOnDelete();

            // 2. Polymorphic Link (Links to Appointment, Expense, Salary, etc.)
            // This creates 'billable_id' and 'billable_type'
            $table->nullableMorphs('billable'); 

            // 3. Financials
            $table->enum('type', ['income', 'expense'])->index();
            $table->decimal('amount', 12, 2);
            $table->string('payment_method')->default('cash'); // cash, card, check
            
            // 4. Details
            $table->string('category')->default('consultation')->index(); 
            $table->date('transaction_date')->index();
            $table->text('notes')->nullable();


            // Traits
            $table->unsignedBigInteger('created_by')->nullable(); // Blameable
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};