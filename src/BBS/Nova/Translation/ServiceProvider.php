<?php

namespace BBS\Nova\Translation;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Package ID.
     *
     * @var string
     */
    const PACKAGE_ID = 'nova-translation';

    /**
     * Bootstrap Kernel.
     *
     * @return void
     */
    public function boot()
    {
        $this->bootPackage();
    }

    /**
     * Boot Laravel package.
     *
     * @return void
     */
    protected function bootPackage()
    {
        $this->mergeConfigFrom(__DIR__ . '/../../../config/config.php', static::PACKAGE_ID);
        $this->loadMigrationsFrom(__DIR__ . '/../../../database/migrations');

        $this->publishes([
            __DIR__ . '/../../../config/config.php' => base_path('config/' . static::PACKAGE_ID . '.php'),
        ]);
    }
}
