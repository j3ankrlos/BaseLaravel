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
            $table->tinyInteger('estado')->default(2)->after('maternity_lot')->comment('1: destetada, 2: activa');
        });

        // Update existing records: if has lot -> destetada (1), else -> activa (2)
        \Illuminate\Support\Facades\DB::table('births')
            ->whereNotNull('maternity_lot')
            ->where('maternity_lot', '!=', '')
            ->update(['estado' => 1]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('births', function (Blueprint $table) {
            $table->dropColumn('estado');
        });
    }
};
