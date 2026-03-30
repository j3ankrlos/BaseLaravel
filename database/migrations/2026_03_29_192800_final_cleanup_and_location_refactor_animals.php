<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('animals', function (Blueprint $table) {
            // 1. Drop Foreign Keys first to avoid integrity errors
            $table->dropForeign(['barn_id']);
            $table->dropForeign(['barn_section_id']);
            $table->dropForeign(['pen_id']);
            $table->dropForeign(['stage_id']);
            $table->dropForeign(['parent_animal_id']);

            // 2. Drop unnecessary columns
            $table->dropColumn([
                'type', 'primera', 'cola', 'saman', 
                'pic_cycle', 'pic_day', 
                'identifier',
                'mother_tag', 'father_tag',
                'inbreeding', 'breed_composition',
                'stage_id', 'farm',
                'parent_animal_id',
                'barn_id', 'barn_section_id', 'pen_id',
                'activo_excel'
            ]);

            // 3. Create fresh location relations as Nave, Seccion and Corral (Integer)
            $table->foreignId('nave_id')->nullable()->after('weight')->constrained('barns')->nullOnDelete();
            $table->foreignId('seccion_id')->nullable()->after('nave_id')->constrained('barn_sections')->nullOnDelete();
            $table->integer('corral')->nullable()->after('seccion_id'); // Just an integer as requested
        });
    }

    public function down(): void
    {
        Schema::table('animals', function (Blueprint $table) {
            // Revert changes if needed (not strictly required for this cleanup but good practice)
        });
    }
};
