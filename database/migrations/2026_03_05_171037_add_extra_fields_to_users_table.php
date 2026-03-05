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
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique()->nullable()->after('email'); // Nombre de Usuario
            $table->string('short_name')->nullable()->after('name'); // Nombre Corto
            $table->unsignedBigInteger('personal_id')->nullable()->after('id'); // Relación con Personal (legacy)
            $table->integer('status_id')->default(1)->after('personal_id'); // Estatus
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['username', 'short_name', 'personal_id', 'status_id']);
        });
    }
};
