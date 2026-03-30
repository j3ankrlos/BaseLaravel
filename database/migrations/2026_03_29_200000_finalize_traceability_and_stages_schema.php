<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Re-add stage_id to animals table
        Schema::table('animals', function (Blueprint $table) {
            $table->foreignId('stage_id')->nullable()->after('genetic_id')->constrained('stages')->nullOnDelete();
        });

        // 2. Refactor movements table for new location logic
        Schema::table('movements', function (Blueprint $table) {
            // Drop Foreign Keys first
            $table->dropForeign(['from_barn_section_id']);
            $table->dropForeign(['to_barn_section_id']);
            $table->dropForeign(['from_pen_id']);
            $table->dropForeign(['to_pen_id']);

            // Rename Section columns
            $table->renameColumn('from_barn_section_id', 'from_seccion_id');
            $table->renameColumn('to_barn_section_id', 'to_seccion_id');

            // Drop Pen columns
            $table->dropColumn(['from_pen_id', 'to_pen_id']);

            // Drop PIC related if they exist (for consistency)
            if (Schema::hasColumn('movements', 'pic_cycle')) {
                $table->dropColumn(['pic_cycle', 'pic_day']);
            }

            // Add Nave and Corral columns (Level 1 and Level 3)
            $table->foreignId('from_nave_id')->nullable()->after('animal_id')->constrained('barns')->nullOnDelete();
            $table->foreignId('to_nave_id')->nullable()->after('from_seccion_id')->constrained('barns')->nullOnDelete();
            $table->integer('from_corral')->nullable()->after('from_nave_id');
            $table->integer('to_corral')->nullable()->after('to_nave_id');
        });

        // Re-establish foreign keys for renamed section columns
        Schema::table('movements', function (Blueprint $table) {
            $table->foreign('from_seccion_id')->references('id')->on('barn_sections')->nullOnDelete();
            $table->foreign('to_seccion_id')->references('id')->on('barn_sections')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('animals', function (Blueprint $table) {
            $table->dropForeign(['stage_id']);
            $table->dropColumn(['stage_id']);
        });

        // Reverting movements table is omitted for brevity as this is a cleanup/rebuild process
    }
};
