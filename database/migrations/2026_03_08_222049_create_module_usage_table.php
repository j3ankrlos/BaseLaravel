<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_usage', function (Blueprint $table) {
            $table->id();
            $table->string('module_key')->unique();
            $table->string('display_name');
            $table->string('url');
            $table->string('icon');
            $table->string('color_class')->default('text-primary');
            $table->unsignedInteger('hits')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_usage');
    }
};
