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
        Schema::table('movements', function (Blueprint $table) {
            $table->string('movement_type')->change(); // Cambiamos a string para mayor flexibilidad
            
            $table->string('boar_identifier')->nullable()->after('weight');
            $table->text('note')->nullable()->after('boar_identifier');
            $table->foreignId('death_cause_id')->nullable()->after('note')->constrained('death_causes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('movements', function (Blueprint $table) {
            $table->dropForeign(['death_cause_id']);
            $table->dropColumn(['boar_identifier', 'note', 'death_cause_id']);
            // Nota: No revertimos el cambio de enum a string fácilmente sin conocer el estado previo exacto en el revert
        });
    }
};
