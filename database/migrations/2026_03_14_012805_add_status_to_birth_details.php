<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('birth_details', function (Blueprint $table) {
            $table->string('status')->default('MATERNIDAD')->after('sex'); // MATERNIDAD, RECRIA, FIN
            // inventory_id se agrega como campo simple aquí; la FK a 'animals' se maneja en la migración de refactorización
            $table->unsignedBigInteger('inventory_id')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('birth_details', function (Blueprint $table) {
            $table->dropColumn(['status', 'inventory_id']);
        });
    }
};
