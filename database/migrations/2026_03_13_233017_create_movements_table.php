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
        Schema::create('movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->constrained('inventory')->onDelete('cascade');
            
            $table->datetime('movement_date');
            $table->integer('pic_cycle');
            $table->integer('pic_day');
            
            $table->enum('movement_type', [
                'INGRESO_RECRIA',
                'ASIGNACION_LOTE',
                'TRASLADO',
                'SELECCION',
                'CREACION_ID',
                'MUERTE',
                'VENTA'
            ]);
            
            $table->integer('quantity'); // Positivo (ingresa a la sala) o negativo (resta al lote)
            $table->decimal('weight', 8, 2)->nullable();
            
            // Origen y Destino de Ubicaciones
            $table->foreignId('from_barn_section_id')->nullable()->constrained('barn_sections')->nullOnDelete();
            $table->foreignId('from_pen_id')->nullable()->constrained('pens')->nullOnDelete();
            $table->foreignId('to_barn_section_id')->nullable()->constrained('barn_sections')->nullOnDelete();
            $table->foreignId('to_pen_id')->nullable()->constrained('pens')->nullOnDelete();
            
            // Transiciones de Etapa (Ej. de Recría a Levante)
            $table->foreignId('from_stage_id')->nullable()->constrained('stages')->nullOnDelete();
            $table->foreignId('to_stage_id')->nullable()->constrained('stages')->nullOnDelete();
            
            // Enlace con el hijo/padre al hacer "CREACION_ID"
            $table->foreignId('reference_id')->nullable()->constrained('inventory')->nullOnDelete();
            
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
            
            // Índice recomendado para historial cronológico de un animal
            $table->index(['inventory_id', 'movement_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movements');
    }
};
