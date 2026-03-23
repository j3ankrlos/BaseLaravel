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
        Schema::table('births', function (Blueprint $table) {
            $table->integer('pic_destete')->nullable()->after('estado')->comment('Día PIC asignado al ingreso a recría');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('births', function (Blueprint $table) {
            $table->dropColumn('pic_destete');
        });
    }
};
