<?php

namespace BBSLab\NovaTranslation;

use BBSLab\NovaTranslation\Http\Middleware\Authorize;
use BBSLab\NovaTranslation\Http\View\Composers\LocaleComposer;
use BBSLab\NovaTranslation\Models\Observers\TranslatablePivotObserver;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;

class NovaTranslationServiceProvider extends ServiceProvider
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
        $this->publishes([
            __DIR__.'/../config/config.php' => $this->app->configPath('nova-translation.php'),
        ], ['nova-translation', 'nova-translation-config']);

        $this->publishes([
            __DIR__.'/../resources/lang' => $this->app->resourcePath('lang/vendor/nova-translation'),
        ], ['nova-translation', 'nova-translation-lang']);

        $this->publishes([
            __DIR__.'/../resources/views' => $this->app->resourcePath('views/vendor/nova-translation'),
        ], ['nova-translation', 'nova-translation-views']);

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'nova-translation');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'nova-translation');

        View::composer('nova-translation::locale-dropdown', LocaleComposer::class);

        Pivot::observe(TranslatablePivotObserver::class);

        if ($this->isNovaInstalled()) {
            $this->app->booted(function () {
                $this->loadRoutes();
            });

            Nova::serving(function (ServingNova $event) {
                $this->loadNovaTranslations();

                Nova::provideToScript([
                    'locale' => app()->getLocale(),
                ]);
            });
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/config.php', 'nova-translation'
        );

        $this->app->singleton(NovaTranslation::class, function ($app) {
            return new NovaTranslation($app['config']['nova-translation']);
        });
    }

    protected function isNovaInstalled(): bool
    {
        return class_exists('Laravel\Nova\Nova');
    }

    protected function loadRoutes(): void
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware(['nova', Authorize::class])
            ->prefix('nova-vendor/nova-translation')
            ->group(__DIR__.'/../routes/api.php');
    }

    protected function loadNovaTranslations(): void
    {
        $file = __DIR__.'/../resources/lang/'.app()->getLocale().'.json';
        if (!file_exists($file)) {
            $file = __DIR__.'/../resources/lang/en.json';
        }

        $translations = json_decode(file_get_contents($file), true);
        $translations = collect($translations)->mapWithKeys(function ($value, $key) {
            return ["nova-translation::{$key}" => $value];
        })->toArray();

        Nova::translations($translations);
    }
}
