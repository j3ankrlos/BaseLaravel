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
        Schema::table('quarantine_batches', function (Blueprint $table) {
            $table->enum('batch_type', ['IMPORTACION', 'CRECIMIENTO'])->default('IMPORTACION')->after('id');
            $table->foreignId('genetic_id')->nullable()->constrained('genetics')->after('batch_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quarantine_batches', function (Blueprint $table) {
            $table->dropForeign(['genetic_id']);
            $table->dropColumn(['batch_type', 'genetic_id']);
        });
    }
};
