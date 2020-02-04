<?php

namespace BBSLab\NovaTranslation;

use BBSLab\NovaTranslation\Http\Middleware\Authorize;
use BBSLab\NovaTranslation\Http\View\Composers\LocaleComposer;
use BBSLab\NovaTranslation\Models\Locale;
use BBSLab\NovaTranslation\Models\Observers\LocaleObserver;
use BBSLab\NovaTranslation\Resources\Locale as LocaleResource;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;

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

        Locale::observe(LocaleObserver::class);

        if ($this->isNovaInstalled()) {
            $this->app->booted(function () {
                $this->bootRoutes();
            });

            if (config('nova-translation.use_default_locale_resource', false) === true) {
                LocaleResource::$group = config('nova-translation.default_locale_resource_group');
                Nova::resources([LocaleResource::class]);
            }

            $this->serveNova();
        }
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
        $this->loadViewsFrom(__DIR__.'/../resources/views', static::PACKAGE_ID);

        View::composer(static::PACKAGE_ID.'::locale-dropdown', LocaleComposer::class);

        $this->publishes([
            __DIR__.'/../config/config.php' => config_path(static::PACKAGE_ID.'.php'),
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

        Route::middleware(['nova', Authorize::class])
            ->prefix('nova-vendor/'.static::PACKAGE_ID)
            ->group(__DIR__.'/../routes/api.php');
    }

    /**
     * Serve Laravel Nova.
     *
     * @return void
     */
    protected function serveNova()
    {
        Nova::serving(function (ServingNova $event) {
            $this->loadNovaTranslations();

            Nova::provideToScript([
                'locale' => app()->getLocale(),
            ]);
        });
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
}
