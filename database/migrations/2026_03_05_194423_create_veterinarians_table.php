<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('veterinarians', function (Blueprint $table) {
            $table->id();
            $table->string('medical_college_code')->nullable(); // CodigoColegioMedico
            $table->string('ministry_code')->nullable(); // CodigoMinisterio
            $table->string('registration_status')->nullable(); // EstadoRegistro
            $table->foreignId('unit_id')->nullable()->constrained('units')->onDelete('set null'); // Unidad
            $table->string('initials')->nullable(); // Siglas
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade'); // Relación con Empleado
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('veterinarians');
    }
};
