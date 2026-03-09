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
            $table->foreignId('state_id')->nullable()->constrained('states')->onDelete('set null'); // Estado
            $table->foreignId('municipality_id')->nullable()->constrained('municipalities')->onDelete('set null'); // Municipio
            $table->foreignId('parish_id')->nullable()->constrained('parishes')->onDelete('set null'); // Parroquia
            $table->string('city')->nullable(); // Ciudad
            $table->text('address')->nullable(); // Dirección
            $table->date('entry_date')->nullable(); // FechaIngreso
            $table->string('file_number')->nullable(); // NumFicha
            
            // Relaciones
            $table->foreignId('position_id')->nullable()->constrained('positions')->onDelete('set null'); // Cargo
            $table->foreignId('area_id')->nullable()->constrained('areas')->onDelete('set null'); // Area de Centro de Costo
            $table->foreignId('assigned_post_id')->nullable()->constrained('assigned_posts')->onDelete('set null'); // Área Asignada (Puesto)
            $table->foreignId('unit_id')->nullable()->constrained('units')->onDelete('set null'); // IdUnidad
            $table->foreignId('payroll_type_id')->nullable()->constrained('payroll_types')->onDelete('set null'); // Tipo Nómina
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->onDelete('set null'); // Turno / Horas
            
            $table->string('cost_center_code')->nullable(); // Centro de Costo (automático según área)
            
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
