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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('first_names'); // nombres
            $table->string('last_names'); // apellidos
            $table->string('national_id')->unique(); // cedila
            $table->string('phone_fixed')->nullable(); // teléfonoFijo
            $table->string('phone_mobile')->nullable(); // teléfonoMóvil
            $table->string('state')->nullable(); // Estado
            $table->string('municipality')->nullable(); // Municipio
            $table->string('parish')->nullable(); // Parroquia
            $table->string('city')->nullable(); // Ciudad
            $table->text('address')->nullable(); // Dirección
            $table->date('entry_date')->nullable(); // FechaIngreso
            $table->string('file_number')->nullable(); // NumFicha
            
            // Relaciones
            $table->string('cost_center_code')->nullable(); // IdCentroCosto
            $table->foreignId('area_id')->nullable()->constrained('areas')->onDelete('set null'); // IdAreaAsignada
            $table->foreignId('unit_id')->nullable()->constrained('units')->onDelete('set null'); // IdUnidad
            $table->foreignId('veterinarian_id')->nullable()->constrained('veterinarians')->onDelete('set null'); // IdVeterinario
            
            $table->string('status')->default('Activo'); // Estatus
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
