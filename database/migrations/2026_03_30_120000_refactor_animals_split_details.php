<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create the animal_details table with safety
        if (!Schema::hasTable('animal_details')) {
            Schema::create('animal_details', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('animal_id');
                $table->foreign('animal_id')->references('id')->on('animals')->onDelete('cascade');
                
                // Characteristics & Events
                $table->string('source')->nullable(); 
                $table->string('management_lot')->nullable(); 
                $table->string('lote_sap')->nullable(); 
                $table->string('act_curso')->nullable(); 
                $table->integer('order_number')->nullable(); 
                $table->string('evento')->nullable(); 
                $table->decimal('weight', 10, 2)->nullable(); 
                $table->string('feed_type')->nullable(); 
                
                // Genealogy Details
                $table->decimal('inbreeding', 10, 6)->nullable(); 
                $table->string('breed_composition')->nullable(); 
                
                $table->timestamps();
            });
        }

        // 2. Refactor the existing animals table to keep only Identity and Location
        Schema::table('animals', function (Blueprint $table) {
            $columnsToDrop = [
                'source', 'management_lot', 'lote_sap', 'act_curso', 'order_number', 
                'evento', 'weight', 'feed_type', 'inbreeding', 'breed_composition', 'age_days'
            ];
            
            foreach ($columnsToDrop as $col) {
                if (Schema::hasColumn('animals', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('animals', function (Blueprint $table) {
            $table->string('source')->nullable();
            $table->string('management_lot')->nullable();
            $table->string('lote_sap')->nullable();
            $table->string('act_curso')->nullable();
            $table->integer('order_number')->nullable();
            $table->string('evento')->nullable();
            $table->decimal('weight', 10, 2)->nullable();
            $table->string('feed_type')->nullable();
            $table->decimal('inbreeding', 10, 6)->nullable();
            $table->string('breed_composition')->nullable();
            $table->integer('age_days')->nullable();
        });

        Schema::dropIfExists('animal_details');
    }
};
