<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            
            // Link to the Tenant (Clinic) - Vital for security
            $table->foreignId('clinic_id')->constrained()->onDelete('cascade');
            
            // Identity
            $table->string('first_name');
            $table->string('last_name');
            $table->string('cin')->nullable()->index(); // Indexed for fast search
            $table->string('phone')->nullable()->index();
            $table->date('birth_date')->nullable();
            $table->enum('gender', ['male', 'female']);
            $table->text('address')->nullable();
            
            // Insurance (Mutuelle)
            $table->string('mutuelle_provider')->nullable(); // e.g. CNSS, CNOPS
            $table->string('mutuelle_number')->nullable();
            
            // Financial Cache
            $table->decimal('current_balance', 10, 2)->default(0); // If > 0, they owe money
            
            // Standard timestamps + Soft Deletes
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('patients');
    }
};