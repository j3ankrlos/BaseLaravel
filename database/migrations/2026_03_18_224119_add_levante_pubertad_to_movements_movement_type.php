<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Agrega INGRESO_LEVANTE e INGRESO_PUBERTAD al ENUM movement_type de la tabla movements.
     */
    public function up(): void
    {
        // En MySQL, para expandir un ENUM hay que hacer ALTER TABLE directamente.
        DB::statement("
            ALTER TABLE `movements`
            MODIFY COLUMN `movement_type` ENUM(
                'INGRESO_RECRIA',
                'INGRESO_LEVANTE',
                'INGRESO_PUBERTAD',
                'ASIGNACION_LOTE',
                'TRASLADO',
                'SELECCION',
                'CREACION_ID',
                'MUERTE',
                'VENTA'
            ) NOT NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
            ALTER TABLE `movements`
            MODIFY COLUMN `movement_type` ENUM(
                'INGRESO_RECRIA',
                'ASIGNACION_LOTE',
                'TRASLADO',
                'SELECCION',
                'CREACION_ID',
                'MUERTE',
                'VENTA'
            ) NOT NULL
        ");
    }
};
