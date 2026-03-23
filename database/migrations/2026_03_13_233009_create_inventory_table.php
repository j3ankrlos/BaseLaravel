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
        Schema::create('inventory', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['INDIVIDUO', 'LOTE']);
            $table->string('identifier')->nullable(); // ID Arete o Nro Lote (Ej. YORK-123 o 800)
            $table->string('management_lot')->nullable(); // Lote Semanal (Ej. 800)
            $table->integer('quantity'); // 1 para individuo, >1 para lote
            $table->enum('status', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
            
            $table->foreignId('parent_inventory_id')->nullable()->constrained('inventory')->nullOnDelete(); // Trazabilidad
            
            $table->foreignId('barn_section_id')->nullable()->constrained('barn_sections')->nullOnDelete();
            $table->foreignId('pen_id')->nullable()->constrained('pens')->nullOnDelete();
            $table->foreignId('stage_id')->nullable()->constrained('stages')->nullOnDelete();
            
            // Atributos físicos
            $table->foreignId('genetic_id')->nullable()->constrained('genetics')->nullOnDelete();
            $table->enum('sex', ['Macho', 'Hembra', 'Mixto'])->nullable();
            $table->date('entry_date')->nullable();
            $table->integer('entry_pic_cycle')->nullable();
            $table->integer('entry_pic_day')->nullable();
            $table->decimal('current_weight', 8, 2)->nullable();
            
            $table->timestamps();
            
            // Índices para búsquedas rápidas
            $table->index(['type', 'status']);
            $table->index('identifier');
            $table->index('management_lot');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory');
    }
};
