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
        Schema::create('births', function (Blueprint $table) {
            $table->id();
            $table->date('calendar_date'); // Fecha calendario real
            $table->integer('pic_cycle'); // Vuelta (Ej: 19)
            $table->integer('pic_day');   // Fecha PIC (Ej: 887)
            $table->string('room'); // Sala
            $table->string('cage'); // Jaula
            $table->string('mother_tag'); // Madre
            $table->integer('parity'); // Paridad
            $table->string('father_tag'); // Padre
            $table->integer('lnv'); // Lechones Nacidos Vivos
            $table->integer('quantity'); // Cantidad de identificados
            $table->foreignId('genetic_id')->constrained('genetics');
            $table->foreignId('responsible_id')->constrained('employees');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('births');
    }
};
