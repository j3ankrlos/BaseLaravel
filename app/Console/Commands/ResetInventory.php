<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ResetInventory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:reset-inventory {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Vacía las tablas de inventario (animales, movimientos) y maternidad (partos, detalles).';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force') && !$this->confirm('¿Estás seguro de que deseas vaciar todas las tablas de inventario y partos? Esta acción no se puede deshacer.')) {
            $this->info('Operación cancelada.');
            return;
        }

        $this->warn('Vaciando tablas...');

        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        \App\Models\Animal::truncate();
        \App\Models\Movement::truncate();
        \App\Models\Birth::truncate();
        \App\Models\BirthDetail::truncate();
        \App\Models\ModuleUsage::truncate();

        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->info('Tablas vaciadas correctamente.');
    }
}
