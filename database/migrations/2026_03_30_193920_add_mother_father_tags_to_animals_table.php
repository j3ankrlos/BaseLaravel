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
        Schema::table('animals', function (Blueprint $table) {
            if (!Schema::hasColumn('animals', 'mother_tag')) {
                $table->string('mother_tag')->nullable()->after('status');
            }
            if (!Schema::hasColumn('animals', 'father_tag')) {
                $table->string('father_tag')->nullable()->after('mother_tag');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('animals', function (Blueprint $table) {
            $table->dropColumn(['mother_tag', 'father_tag']);
        });
    }
};
