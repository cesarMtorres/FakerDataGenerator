<?php

namespace App\Services;

use Illuminate\Support\Facades\Schema;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;

class FakeDataGenerator
{
    protected $faker;

    public function __construct()
    {
        $this->faker = Faker::create();
    }

    public function generateFakeDataPreview(string $table, int $count): array
    {
        $columns = $this->getTableColumns($table);
        $data = [];

        for ($i = 0; $i < $count; $i++) {
            $record = [];
            foreach ($columns as $column => $type) {
                $record[$column] = $this->generateFakeValue($type);
            }
            $data[] = $record;
        }

        return $data;
    }

    public function generateFakeData(string $table, int $count): void
    {
        $columns = $this->getTableColumns($table);
        $insertData = [];

        for ($i = 0; $i < $count; $i++) {
            $record = [];
            foreach ($columns as $column => $type) {
                $record[$column] = $this->generateFakeValue($type);
            }
            $insertData[] = $record;
        }

        // todo - agregar los regustros en un csv para poder tener un historial eliminarlos mas tarde
        $stored = DB::transaction(function () use ($table, $insertData) {
            DB::table($table)->insert($insertData);
        });
    }

    private function generateFakeValue(string $type)
    {
        switch ($type) {
            case 'string':
                return $this->faker->text(255);
            case 'integer':
                return $this->faker->randomNumber();
            case 'date':
                return $this->faker->date();
            case 'boolean':
                return $this->faker->boolean();
            default:
                return $this->faker->word();
        }
    }

    private function getTableColumns(string $table): array
    {
        // Obtiene las columnas de la tabla desde el esquema
        $columns = Schema::getColumnListing($table);
        $columnTypes = [];

        foreach ($columns as $column) {
            $columnType = Schema::getColumnType($table, $column);
            $columnTypes[$column] = $this->mapColumnType($columnType);
        }

        return $columnTypes;
    }

    private function mapColumnType($columnType): string
    {
        // Mapea el tipo de columna a uno más comprensible para Faker
        switch ($columnType) {
            case 'string':
                return 'string';
            case 'integer':
            case 'bigint':
                return 'integer';
            case 'date':
            case 'datetime':
                return 'date';
            case 'boolean':
                return 'boolean';
            default:
                return 'string';
        }
    }

    // todo mejorar esto
    private function storeHistory(string $table, array $insertData): void
    {
        // Agrega un identificador único y la marca de tiempo
        $timestamp = now();
        $historyData = array_map(function ($data) use ($timestamp) {
            $data['created_at'] = $timestamp;
            return $data;
        }, $insertData);

        // Convierte los datos al formato CSV
        $csvContent = '';
        foreach ($historyData as $row) {
            $csvContent .= implode(',', $row) . "\n";
        }

        // Almacena en un archivo CSV en el almacenamiento local
        $filePath = storage_path("faker-history/history_{$table}.csv");

        Storage::append($filePath, $csvContent);
    }

    // todo mejorar esto
    public function getHistory(string $table): array
    {
        return storage_path("faker-history/history_{$table}.csv");
        // return $rows;
    }

    // todo mejorar esto y eliminar el archivo
    public function deleteHistory(string $table): void
    {
        Storage::delete("faker-history/history_{$table}.csv");
    }

    public function deleteDataById(string $table, int $id): void
    {
        DB::table($table)->where('id', $id)->delete();
    }
}
