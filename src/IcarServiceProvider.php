<?php

namespace Citadelle\Icar;


use Illuminate\Support\ServiceProvider;

class IcarServiceProvider extends ServiceProvider
{

    protected $commands = [
    ];

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;


    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->addCustomConfigurationValues();
    }

    public function addCustomConfigurationValues()
    {
        // add filesystems.disks for the log viewer
        config([
            'logging.channels.icar' => [
                'driver' => 'single',
                'path' => storage_path('logs/icar.log'),
                'level' => 'debug',
            ]
        ]);

    }


    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/icar.php', 'citadelle.icar'
        );

        // register the artisan commands
        //$this->commands($this->commands);
    }
}
