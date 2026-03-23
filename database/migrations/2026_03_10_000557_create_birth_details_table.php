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
        Schema::create('birth_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('birth_id')->constrained()->onDelete('cascade');
            $table->string('generated_id'); // ID Arete
            $table->string('ear_id')->nullable(); // ID Oreja
            $table->decimal('weight', 5, 2)->nullable(); // Peso
            $table->integer('teats_total')->nullable(); // N Pesones
            $table->integer('teats_left')->nullable(); // IZQ
            $table->integer('teats_behind_shoulder_left')->nullable(); // DTRZ OMB IZQ
            $table->integer('teats_behind_shoulder_right')->nullable(); // DTRZ OMB DER
            $table->string('sex')->nullable(); // SEXO
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('birth_details');
    }
};
