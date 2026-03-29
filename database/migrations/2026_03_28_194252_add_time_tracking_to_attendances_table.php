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
            $table->time('check_in')->nullable()->after('attendance_date');
            $table->time('lunch_break_start')->nullable()->after('check_in');
            $table->time('lunch_break_end')->nullable()->after('lunch_break_start');
            $table->time('check_out')->nullable()->after('lunch_break_end');
            $table->decimal('total_hours', 5, 2)->default(0)->after('check_out');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['check_in', 'lunch_break_start', 'lunch_break_end', 'check_out', 'total_hours']);
        });
    }
};
