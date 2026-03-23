<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('animals', function (Blueprint $table) {
            $table->string('primera')->nullable()->after('id');
            $table->string('cola')->nullable()->after('primera');
            $table->string('saman')->nullable()->after('cola');
            // 'activo' ya existe como 'status' en la migración previa, 
            // pero el usuario lo pide como columna 'Activo'. 
            // Mantenemos 'status' para lógica y 'activo_excel' para el dato masivo si es necesario,
            // o simplemente renombramos la etiqueta en la vista.
            // Añadimos 'activo_excel' por si acaso pide llenar el texto.
            $table->string('activo_excel')->nullable()->after('lote_sap');
        });
    }

    public function down(): void
    {
        Schema::table('animals', function (Blueprint $table) {
            $table->dropColumn(['primera', 'cola', 'saman', 'activo_excel']);
        });
    }
};
