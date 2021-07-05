<?php

return [

    /*
     * The config_key for voyager-import package.
     */
    'config_key' => env('VOYAGER_IMPORT_CONFIG_KEY', 'joy-voyager-import'),

    /*
     * The route_prefix for voyager-import package.
     */
    'route_prefix' => env('VOYAGER_IMPORT_ROUTE_PREFIX', 'joy-voyager-import'),

    /*
    |--------------------------------------------------------------------------
    | Controllers config
    |--------------------------------------------------------------------------
    |
    | Here you can specify voyager controller settings
    |
    */

    'controllers' => [
        'namespace' => 'Joy\\VoyagerImport\\Http\\Controllers',
    ],
];
