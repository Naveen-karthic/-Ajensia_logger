<?php

namespace Carifer\Ajensia;

use Illuminate\Support\ServiceProvider;

class AjensiaLoggerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Publish the configuration file
        $this->publishes([
            __DIR__.'/../config/ajensia_logger.php' => config_path('ajensia_logger.php'),
        ], 'ajensia-logger-config');
    }

    public function register()
    {

    }
}
