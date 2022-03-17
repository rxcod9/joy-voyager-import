<?php

declare(strict_types=1);

namespace Joy\VoyagerBulkUpdate;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Joy\VoyagerBulkUpdate\Exports\AllDataTypesTemplateExport;
use Joy\VoyagerBulkUpdate\Exports\DataTypeTemplateExport;
use Joy\VoyagerBulkUpdate\Imports\AllDataTypesImport;
use Joy\VoyagerBulkUpdate\Imports\DataTypeImport;

/**
 * Class ImportServiceProvider
 *
 * @category  Package
 * @package   JoyVoyagerBulkUpdate
 * @author    Ramakant Gangwar <gangwar.ramakant@gmail.com>
 * @copyright 2021 Copyright (c) Ramakant Gangwar (https://github.com/rxcod9)
 * @license   http://github.com/rxcod9/joy-voyager-bulk-update/blob/main/LICENSE New BSD License
 * @link      https://github.com/rxcod9/joy-voyager-bulk-update
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
        $this->app->bind('joy-voyager-bulk-update.import', function ($app) {
            return new DataTypeImport();
        });
        $this->app->bind('joy-voyager-bulk-update.import-template', function ($app) {
            return new DataTypeTemplateExport();
        });

        $this->app->bind('joy-voyager-bulk-update.import-all', function ($app) {
            return new AllDataTypesImport();
        });
        $this->app->bind('joy-voyager-bulk-update.import-all-template', function ($app) {
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
            'joy-voyager-bulk-update.import',
            'joy-voyager-bulk-update.import-template',
            'joy-voyager-bulk-update.import-all',
            'joy-voyager-bulk-update.import-all-template'
        ];
    }
}
