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
        Schema::table('transfer_requests', function (Blueprint $table) {
            $table->foreignId('user_id_solicitante')->nullable()->constrained('users');
            $table->foreignId('user_id_aprobador')->nullable()->constrained('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transfer_requests', function (Blueprint $table) {
            $table->dropForeign(['user_id_solicitante']);
            $table->dropForeign(['user_id_aprobador']);
            $table->dropColumn(['user_id_solicitante', 'user_id_aprobador']);
        });
    }
};
