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
        Schema::create('quarantine_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quarantine_batch_id')->constrained()->onDelete('cascade');
            
            // Animal Data
            $table->string('internal_id')->nullable(); // ID Number
            $table->string('official_id')->nullable(); // ID
            $table->foreignId('genetic_id')->nullable()->constrained('genetics'); // Raza
            $table->string('sex')->nullable(); // Sex
            $table->string('extra_status')->nullable(); // Estatus
            $table->date('birth_date')->nullable(); // Fecha Nacimiento
            $table->integer('lote')->nullable(); 

            // Location
            $table->foreignId('barn_id')->nullable()->constrained('barns'); // Nave
            $table->foreignId('barn_section_id')->nullable()->constrained('barn_sections'); // Seccion
            $table->foreignId('pen_id')->nullable()->constrained('pens'); // Corral/Jaula
            
            // Pedigree - Generation 1 (Parents)
            $table->string('f_tag')->nullable(); // PAPA
            $table->foreignId('f_genetic_id')->nullable()->constrained('genetics'); // Raza Papa
            $table->string('f_sex')->nullable(); // Sex Papa
            
            $table->string('m_tag')->nullable(); // MAMA
            $table->foreignId('m_genetic_id')->nullable()->constrained('genetics'); // Raza Mama
            $table->string('m_sex')->nullable(); // Sex Mama

            // Pedigree - Generation 2 (Paternal Grandparents)
            $table->string('ff_tag')->nullable(); // ABUELO_P
            $table->foreignId('ff_genetic_id')->nullable()->constrained('genetics'); // Raza Abuelo P
            $table->string('ff_sex')->nullable(); 

            $table->string('fm_tag')->nullable(); // ABUELA_P
            $table->foreignId('fm_genetic_id')->nullable()->constrained('genetics'); // Raza Abuela P
            $table->string('fm_sex')->nullable(); 

            // Pedigree - Generation 2 (Maternal Grandparents)
            $table->string('mf_tag')->nullable(); // ABUELO_M
            $table->foreignId('mf_genetic_id')->nullable()->constrained('genetics'); // Raza Abuelo M
            $table->string('mf_sex')->nullable(); 

            $table->string('mm_tag')->nullable(); // ABUELA_M
            $table->foreignId('mm_genetic_id')->nullable()->constrained('genetics'); // Raza Abuela M
            $table->string('mm_sex')->nullable(); 

            // Pedigree - Generation 3 (Great Grandparents)
            $table->string('fff_tag')->nullable(); // BISABUELO (P-P)
            $table->string('ffm_tag')->nullable(); // BISABUELA (P-P)
            $table->string('fmf_tag')->nullable(); // BISABUELO (P-M)
            $table->string('fmm_tag')->nullable(); // BISABUELA (P-M)
            $table->string('mff_tag')->nullable(); // BISABUELO (M-P)
            $table->string('mfm_tag')->nullable(); // BISABUELA (M-P)
            $table->string('mmf_tag')->nullable(); // BISABUELO (M-M)
            $table->string('mmm_tag')->nullable(); // BISABUELA (M-M)

            // Status and Linking
            $table->string('status')->default('PENDIENTE'); // PENDIENTE, INCORPORADO, BAJA
            $table->foreignId('animal_id')->nullable()->constrained()->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quarantine_items');
    }
};
