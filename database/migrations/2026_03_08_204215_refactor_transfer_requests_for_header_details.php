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
            $table->dropForeign(['user_id_solicitante']);
            $table->dropForeign(['user_id_aprobador']);
        });
        Schema::dropIfExists('transfer_requests');

        Schema::create('transfer_requests', function (Blueprint $table) {
            $table->id();
            $table->string('folio')->unique()->nullable();
            $table->string('estado')->default('pendiente');
            $table->text('comentarios')->nullable();
            $table->foreignId('user_id_solicitante')->nullable()->constrained('users');
            $table->foreignId('user_id_aprobador')->nullable()->constrained('users');
            $table->timestamps();
        });

        Schema::create('transfer_request_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transfer_request_id')->constrained('transfer_requests')->onDelete('cascade');
            $table->string('IdCodigo')->nullable();
            $table->string('Codigo');
            $table->string('Producto');
            $table->string('UMB')->nullable();
            $table->decimal('cantidad_solicitada', 15, 2);
            $table->decimal('cantidad_aprobada', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfer_request_details');
        Schema::dropIfExists('transfer_requests');
    }
};
