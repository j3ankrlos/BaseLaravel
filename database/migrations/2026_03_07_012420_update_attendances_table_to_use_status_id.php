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
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->foreignId('attendance_status_id')->nullable()->after('attendance_date')->constrained('attendance_statuses')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['attendance_status_id']);
            $table->dropColumn('attendance_status_id');
            $table->enum('status', ['Asistió', 'Faltó', 'Retardo', 'Permiso', 'Reposo'])->default('Asistió');
        });
    }
};
