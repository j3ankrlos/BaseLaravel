<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('animals', function (Blueprint $table) {
            // Genealogy Relationships
            $table->foreignId('mother_id')->nullable()->after('genetic_id')->constrained('animals')->nullOnDelete();
            $table->foreignId('father_id')->nullable()->after('mother_id')->constrained('animals')->nullOnDelete();
            
            // Raw Tags from Excel/External Systems
            $table->string('mother_tag')->nullable()->after('father_id');
            $table->string('father_tag')->nullable()->after('mother_tag');
            
            // Genetic & Genealogy Progress
            $table->decimal('inbreeding', 10, 6)->nullable()->after('father_tag'); // Consanguinidad
            $table->string('breed_composition')->nullable()->after('inbreeding'); // Composición Racial
            
            // Census & Process fields
            $table->string('act_curso')->nullable()->after('breed_composition'); // ACT. CURSO
            $table->string('evento')->nullable()->after('act_curso'); // Muerte, Activa, Descarte
            $table->date('birth_date')->nullable()->after('evento'); // Fecha Nacimiento
        });
    }

    public function down(): void
    {
        Schema::table('animals', function (Blueprint $table) {
            $table->dropForeign(['mother_id']);
            $table->dropForeign(['father_id']);
            $table->dropColumn([
                'mother_id', 'father_id', 'mother_tag', 'father_tag', 
                'inbreeding', 'breed_composition', 'act_curso', 'evento', 'birth_date'
            ]);
        });
    }
};
