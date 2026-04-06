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
        Schema::create('quarantine_batches', function (Blueprint $table) {
            $table->id();
            $table->date('entry_date');
            $table->string('origin');
            $table->string('provider');
            $table->string('document_number')->unique();
            $table->integer('total_quantity')->default(0);
            $table->enum('status', ['ABIERTO', 'COMPLETADO'])->default('ABIERTO');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quarantine_batches');
    }
};
