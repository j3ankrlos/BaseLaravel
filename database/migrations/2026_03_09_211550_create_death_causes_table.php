<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('death_causes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('death_system_id')->constrained('death_systems')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('death_causes');
    }
};
