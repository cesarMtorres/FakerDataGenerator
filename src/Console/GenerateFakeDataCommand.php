<?php

namespace App\Console;

use App\Services\FakeDataGenerator;
use Illuminate\Console\Command;

class GenerateFakeDataCommand extends Command
{
    protected $signature = 'generate:fake-data {table} {count=1}';
    protected $description = 'Genera datos falsos automáticamente para una tabla';

    protected $fakeDataGenerator;

    public function __construct(FakeDataGenerator $fakeDataGenerator)
    {
        parent::__construct();
        $this->fakeDataGenerator = $fakeDataGenerator;
    }

    public function handle()
    {
        $table = $this->argument('table');
        $count = $this->argument('count'); // Obtiene la cantidad de registros a generar

        $previewData = $this->fakeDataGenerator->generateFakeDataPreview($table, $count);

        $this->info('Vista previa de los datos generados:');
        foreach ($previewData as $index => $data) {
            $this->line("Registro " . ($index + 1) . ":");
            foreach ($data as $column => $value) {
                $this->line("  Columna: $column, Valor: $value");
            }
        }

        $confirmation = $this->confirm("¿Deseas insertar estos $count registros en la base de datos?", true);

        if ($confirmation) {
            $this->fakeDataGenerator->generateFakeData($table, $count);
            $this->info("$count datos falsos insertados en la tabla $table.");
        } else {
            $this->info('No se insertaron datos.');
        }
    }
}
