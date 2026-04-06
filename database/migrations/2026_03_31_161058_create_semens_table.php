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
        Schema::create('semens', function (Blueprint $table) {
            $table->id(); // idSemen
            $table->unsignedBigInteger('animal_id'); // IdAnimal
            $table->date('date'); // Fecha
            $table->string('semen_code')->unique(); // CodigoSemen
            $table->string('status')->default('Activo'); // estatus
            $table->timestamps();
            
            $table->foreign('animal_id')->references('id')->on('animals')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('semens');
    }
};
