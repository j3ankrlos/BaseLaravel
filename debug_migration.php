<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

try {
    echo "Checking columns...\n";
    print_r(Schema::getColumnListing('animals'));

    echo "Attempting to create animal_details...\n";
    Schema::create('animal_details', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('animal_id');
        $table->foreign('animal_id')->references('id')->on('animals')->onDelete('cascade');
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
        $table->timestamps();
    });
    echo "Done!\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
