<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Drop existing tables to start fresh as requested
        Schema::dropIfExists('movements');
        Schema::table('birth_details', function (Blueprint $table) {
            // Check if inventory_id column exists before dropping
            if (Schema::hasColumn('birth_details', 'inventory_id')) {
                $table->dropColumn('inventory_id');
            }
        });
        Schema::dropIfExists('inventory');

        // 2. Create the new 'animals' table with the requested fields
        Schema::create('animals', function (Blueprint $table) {
            $table->id();
            
            // Core fields from user's list and image
            $table->integer('quantity')->default(1); // INV in image
            $table->date('entry_date')->nullable(); // Fecha Ingreso / F. INICIO
            $table->integer('pic_cycle')->nullable(); // Vuelta
            $table->integer('pic_day')->nullable(); // FechaPic
            $table->string('source')->nullable(); // Origen (Recria, etc.)
            $table->string('management_lot')->nullable(); // Lote
            $table->string('internal_id')->nullable(); // Id / I-D (YT01636, etc.)
            
            // Physical and Genetic
            $table->foreignId('genetic_id')->nullable()->constrained('genetics')->nullOnDelete(); // Raza
            $table->string('sex')->nullable(); // Sexo
            $table->decimal('weight', 8, 2)->nullable(); // Peso
            
            // Locations
            $table->foreignId('barn_id')->nullable()->constrained('barns')->nullOnDelete(); // Nave / Area
            $table->foreignId('barn_section_id')->nullable()->constrained('barn_sections')->nullOnDelete(); // Seccion / Sala
            $table->foreignId('pen_id')->nullable()->constrained('pens')->nullOnDelete(); // Corral
            
            // SAP and Status
            $table->string('lote_sap')->nullable(); // LoteSap
            $table->string('status')->default('Activo'); // Activo / ACT. CURSO
            $table->integer('order_number')->nullable(); // Orden
            $table->string('feed_type')->nullable(); // TipoAlimento
            
            // App logic essential fields
            $table->foreignId('stage_id')->nullable()->constrained('stages')->nullOnDelete(); // Current Stage
            $table->string('farm')->nullable(); // Granja in image
            $table->integer('age_days')->nullable(); // EDAD in image
            
            $table->timestamps();

            // Indexes for performance
            $table->index('management_lot');
            $table->index('internal_id');
            $table->index('status');
        });

        // 3. Recreate 'movements' table pointing to 'animals'
        Schema::create('movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('animal_id')->constrained('animals')->onDelete('cascade');
            
            $table->datetime('movement_date');
            $table->integer('pic_cycle')->nullable();
            $table->integer('pic_day')->nullable();
            
            $table->enum('movement_type', [
                'INGRESO_RECRIA',
                'ASIGNACION_LOTE',
                'TRASLADO',
                'SELECCION',
                'CREACION_ID',
                'MUERTE',
                'VENTA'
            ]);
            
            $table->integer('quantity'); 
            $table->decimal('weight', 8, 2)->nullable();
            
            $table->foreignId('from_barn_section_id')->nullable()->constrained('barn_sections')->nullOnDelete();
            $table->foreignId('from_pen_id')->nullable()->constrained('pens')->nullOnDelete();
            $table->foreignId('to_barn_section_id')->nullable()->constrained('barn_sections')->nullOnDelete();
            $table->foreignId('to_pen_id')->nullable()->constrained('pens')->nullOnDelete();
            
            $table->foreignId('from_stage_id')->nullable()->constrained('stages')->nullOnDelete();
            $table->foreignId('to_stage_id')->nullable()->constrained('stages')->nullOnDelete();
            
            $table->foreignId('reference_id')->nullable()->constrained('animals')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            
            $table->string('note')->nullable();
            $table->foreignId('death_cause_id')->nullable()->constrained('death_causes')->nullOnDelete();
            
            $table->timestamps();
            
            $table->index(['animal_id', 'movement_date']);
        });

        // 4. Update 'birth_details' to point to 'animals'
        Schema::table('birth_details', function (Blueprint $table) {
            $table->foreignId('animal_id')->nullable()->constrained('animals')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('animals', function (Blueprint $table) {
            //
        });
    }
};
