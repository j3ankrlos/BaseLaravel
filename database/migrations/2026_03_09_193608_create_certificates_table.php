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
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            
            // Atributos de auditoría
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('fecha_registro');

            // Datos del Veterinario (históricos para el certificado en particular)
            $table->string('vet_cedula');
            $table->string('vet_nombre');
            $table->string('vet_apellido');
            $table->string('vet_colegio_medico_codigo')->nullable();
            $table->string('vet_ministerio_codigo')->nullable();
            $table->string('vet_area_reproduccion');

            // Datos del Animal
            $table->string('animal_id');
            $table->string('lote');
            $table->string('raza');
            $table->string('estatus');
            $table->decimal('peso', 8, 2);
            $table->string('sexo');
            $table->string('nave');
            $table->string('seccion');
            $table->string('corral');
            $table->string('tipo_muerte');
            $table->string('causa_muerte');
            $table->string('sistema_involucrado');
            $table->string('reportado_por');
            $table->date('fecha_muerte');
            $table->text('evaluacion_externa')->nullable();
            $table->text('evaluacion_interna')->nullable();

            // Evidencia fotográfica (rutas de archivos)
            $table->string('arete_photo_path')->nullable();
            $table->string('tatuaje_photo_path')->nullable();
            $table->string('otra_photo_path')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
