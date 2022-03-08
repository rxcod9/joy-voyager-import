<?php

declare(strict_types=1);

namespace Joy\VoyagerImport;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Joy\VoyagerImport\Exports\AllDataTypesTemplateExport;
use Joy\VoyagerImport\Exports\DataTypeTemplateExport;
use Joy\VoyagerImport\Imports\AllDataTypesImport;
use Joy\VoyagerImport\Imports\DataTypeImport;

/**
 * Class ImportServiceProvider
 *
 * @category  Package
 * @package   JoyVoyagerImport
 * @author    Ramakant Gangwar <gangwar.ramakant@gmail.com>
 * @copyright 2021 Copyright (c) Ramakant Gangwar (https://github.com/rxcod9)
 * @license   http://github.com/rxcod9/joy-voyager-import/blob/main/LICENSE New BSD License
 * @link      https://github.com/rxcod9/joy-voyager-import
 */
class ImportServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('joy-voyager-import.import', function ($app) {
            return new DataTypeImport();
        });
        $this->app->bind('joy-voyager-import.import-template', function ($app) {
            return new DataTypeTemplateExport();
        });

        $this->app->bind('joy-voyager-import.import-all', function ($app) {
            return new AllDataTypesImport();
        });
        $this->app->bind('joy-voyager-import.import-all-template', function ($app) {
            return new AllDataTypesTemplateExport();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'joy-voyager-import.import',
            'joy-voyager-import.import-template',
            'joy-voyager-import.import-all',
            'joy-voyager-import.import-all-template'
        ];
    }
}
