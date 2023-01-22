<?php

declare(strict_types=1);

namespace Joy\VoyagerImport;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Joy\VoyagerImport\Console\Commands\AllDataTypesImport;
use Joy\VoyagerImport\Console\Commands\AllDataTypesTemplateExport;
use Joy\VoyagerImport\Console\Commands\DataTypeImport;
use Joy\VoyagerImport\Console\Commands\DataTypeTemplateExport;
use TCG\Voyager\Facades\Voyager;

/**
 * Class VoyagerImportServiceProvider
 *
 * @category  Package
 * @package   JoyVoyagerImport
 * @author    Ramakant Gangwar <gangwar.ramakant@gmail.com>
 * @copyright 2021 Copyright (c) Ramakant Gangwar (https://github.com/rxcod9)
 * @license   http://github.com/rxcod9/joy-voyager-import/blob/main/LICENSE New BSD License
 * @link      https://github.com/rxcod9/joy-voyager-import
 */
class VoyagerImportServiceProvider extends ServiceProvider
{
    /**
     * Boot
     *
     * @return void
     */
    public function boot()
    {
        Voyager::addAction(\Joy\VoyagerImport\Actions\ImportAction::class);

        $this->registerPublishables();

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'joy-voyager-import');

        $this->mapApiRoutes();

        $this->mapWebRoutes();

        if (config('joy-voyager-import.database.autoload_migrations', true)) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'joy-voyager-import');
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     */
    protected function mapWebRoutes(): void
    {
        Route::middleware('web')
            ->group(__DIR__ . '/../routes/web.php');
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     */
    protected function mapApiRoutes(): void
    {
        Route::prefix(config('joy-voyager-import.route_prefix', 'api'))
            ->middleware('api')
            ->group(__DIR__ . '/../routes/api.php');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/voyager-import.php', 'joy-voyager-import');

        if ($this->app->runningInConsole()) {
            $this->registerCommands();
        }
    }

    /**
     * Register publishables.
     *
     * @return void
     */
    protected function registerPublishables(): void
    {
        $this->publishes([
            __DIR__ . '/../config/voyager-import.php' => config_path('joy-voyager-import.php'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/../resources/views'                => resource_path('views/vendor/joy-voyager-import'),
            __DIR__ . '/../resources/views/bread/partials' => resource_path('views/vendor/voyager/bread/partials'),
        ], 'views');

        $this->publishes([
            __DIR__ . '/../resources/views/bread/partials' => resource_path('views/vendor/voyager/bread/partials'),
        ], 'voyager-actions-views');

        $this->publishes([
            __DIR__ . '/../resources/lang' => resource_path('lang/vendor/joy-voyager-import'),
        ], 'translations');
    }

    protected function registerCommands(): void
    {
        $this->app->singleton('command.joy-voyager.import-template', function () {
            return new DataTypeTemplateExport();
        });

        $this->app->singleton('command.joy-voyager.import-all-template', function () {
            return new AllDataTypesTemplateExport();
        });

        $this->app->singleton('command.joy-voyager.import', function () {
            return new DataTypeImport();
        });

        $this->app->singleton('command.joy-voyager.import-all', function () {
            return new AllDataTypesImport();
        });

        $this->commands([
            'command.joy-voyager.import-template',
            'command.joy-voyager.import-all-template',
            'command.joy-voyager.import',
            'command.joy-voyager.import-all'
        ]);
    }
}
