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
        Schema::table('transfer_request_details', function (Blueprint $table) {
            $table->dropColumn('IdCodigo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transfer_request_details', function (Blueprint $table) {
            $table->string('IdCodigo')->nullable()->after('transfer_request_id');
        });
    }
};
