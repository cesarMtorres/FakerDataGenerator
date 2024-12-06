<?php

namespace cesarmt\FakerDataGenerator\Providers;

use App\Console\GenerateFakeDataCommand;
use Illuminate\Support\ServiceProvider;

class FakeDataServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Registrar configuraciones
        $this->mergeConfigFrom(__DIR__ . '/../config/fake_data.php', 'fakedata');

        $this->commands([
            GenerateFakeDataCommand::class,
        ]);
    }

    public function boot()
    {
        // Publicar configuraciones
        $this->publishes([
            __DIR__ . '/../config/fake_data.php' => config_path('fake_data.php'),
        ], 'config');

        // Cargar rutas, migraciones, etc.
    }
}
