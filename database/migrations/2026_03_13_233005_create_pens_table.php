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
        Schema::create('pens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barn_section_id')->constrained('barn_sections')->onDelete('cascade');
            $table->string('name'); // Corral 1, Corral A, etc.
            $table->integer('capacity')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pens');
    }
};
