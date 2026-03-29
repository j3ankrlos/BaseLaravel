<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('estatus')->nullable()->after('file_number'); // Fijo / Contratado
            $table->string('estadonomina')->nullable()->after('estatus'); // Activo / Inactivo
        });

        // Migrar datos existentes del campo 'status' al nuevo 'estadonomina'
        DB::table('employees')->whereNotNull('status')->update([
            'estadonomina' => DB::raw('status')
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['estatus', 'estadonomina']);
        });
    }
};
