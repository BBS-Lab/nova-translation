<?php

namespace BBSLab\NovaTranslation;

use BBSLab\NovaTranslation\Http\Middleware\Authorize;
use BBSLab\NovaTranslation\Models\Locale;
use BBSLab\NovaTranslation\Models\Observers\LocaleObserver;
use Illuminate\Contracts\Routing\UrlGenerator as UrlGeneratorContract;
use Illuminate\Routing\Route as RouterRoute;
use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class NovaTranslationServiceProvider extends BaseServiceProvider
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

        if ($this->isNovaInstalled()) {
            $this->app->booted(function () {
                $this->bootRoutes();
            });

            $this->loadNovaTranslations();
        }

        Locale::observe(LocaleObserver::class);
    }

    /**
     * Boot Laravel package.
     *
     * @return void
     */
    protected function bootPackage()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', static::PACKAGE_ID);
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', static::PACKAGE_ID);

        $this->publishes([
            __DIR__.'/../../../../config/config.php' => base_path('config/'.static::PACKAGE_ID.'.php'),
        ]);
    }

    /**
     * Check if Laravel Nova is installed.
     *
     * @return bool
     */
    protected function isNovaInstalled()
    {
        return class_exists('Laravel\Nova\Nova');
    }

    /**
     * Register the tool's routes.
     *
     * @return void
     */
    protected function bootRoutes()
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        $this->setOverridenRoutes();

        Route::middleware(['nova', Authorize::class])
            ->prefix('nova-vendor/'.static::PACKAGE_ID)
            ->group(__DIR__.'/../routes/api.php');
    }

    /**
     * Load prefixed Nova translations.
     *
     * @return void
     */
    protected function loadNovaTranslations()
    {
        $file = __DIR__.'/../resources/lang/'.app()->getLocale().'.json';
        if (! file_exists($file)) {
            $file = __DIR__.'/../resources/lang/en.json';
        }

        $translations = json_decode(file_get_contents($file), true);
        $translations = collect($translations)->mapWithKeys(function ($value, $key) {
            return [static::PACKAGE_ID.'::'.$key => $value];
        })->toArray();

        \Laravel\Nova\Nova::translations($translations);
    }

    /**
     * Remove overriden routes.
     *
     * @return void
     */
    protected function setOverridenRoutes()
    {
        $overiddenRoutes = new RouteCollection;

        $routes = $this->app->router->getRoutes();
        foreach ($routes as $route) {
            /** @var \Illuminate\Routing\Route $route */
            if (in_array('POST', $route->methods()) && ($route->uri() === 'nova-api/{resource}')) {
                $route = new RouterRoute(['POST'], 'nova-api/{resource}', [Http\Controllers\TranslatableResource\StoreController::class, 'handle']);
                $route->name('nova.api.')->middleware('nova');
            } elseif (in_array('PUT', $route->methods()) && ($route->uri() === 'nova-api/{resource}/{resourceId}')) {
                $route = new RouterRoute(['PUT'], 'nova-api/{resource}/{resourceId}', [Http\Controllers\TranslatableResource\UpdateController::class, 'handle']);
                $route->name('nova.api.')->middleware('nova');
            } elseif (in_array('DELETE', $route->methods()) && ($route->uri() === 'nova-api/{resource}')) {
                $route = new RouterRoute(['DELETE'], 'nova-api/{resource}', [Http\Controllers\TranslatableResource\DestroyController::class, 'handle']);
                $route->name('nova.api.')->middleware('nova');
            } else {
                //
            }

            $overiddenRoutes->add($route);
        }

        $this->app->router->setRoutes($overiddenRoutes);
    }
}
