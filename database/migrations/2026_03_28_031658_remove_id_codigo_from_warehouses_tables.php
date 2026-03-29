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
        Schema::table('warehouse_a002_s', function (Blueprint $table) {
            $table->dropColumn('IdCodigo');
        });

        Schema::table('warehouse_a006_s', function (Blueprint $table) {
            $table->dropColumn('IdCodigo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouse_a002_s', function (Blueprint $table) {
            $table->string('IdCodigo')->unique()->after('id');
        });

        Schema::table('warehouse_a006_s', function (Blueprint $table) {
            $table->string('IdCodigo')->unique()->after('id');
        });
    }
};
