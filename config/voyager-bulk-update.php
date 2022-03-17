<?php

return [

    /*
     * If enabled for voyager-bulk-update package.
     */
    'enabled' => env('VOYAGER_BULK_UPDATE_ENABLED', true),

    /*
     * If validation enabled for voyager-bulk-update package.
     */
    'validation' => env('VOYAGER_BULK_UPDATE_VALIDATION_ENABLED', false),

    /*
    | Here you can specify for which data type slugs import is enabled
    | 
    | Supported: "*", or data type slugs "users", "roles"
    |
    */

    'allowed_slugs' => array_filter(explode(',', env('VOYAGER_BULK_UPDATE_ALLOWED_SLUGS', '*'))),

    /*
    | Here you can specify for which data type slugs import is not allowed
    | 
    | Supported: "*", or data type slugs "users", "roles"
    |
    */

    'not_allowed_slugs' => array_filter(explode(',', env('VOYAGER_BULK_UPDATE_NOT_ALLOWED_SLUGS', ''))),

    /*
     * The config_key for voyager-bulk-update package.
     */
    'config_key' => env('VOYAGER_BULK_UPDATE_CONFIG_KEY', 'joy-voyager-bulk-update'),

    /*
     * The route_prefix for voyager-bulk-update package.
     */
    'route_prefix' => env('VOYAGER_BULK_UPDATE_ROUTE_PREFIX', 'joy-voyager-bulk-update'),

    /*
    |--------------------------------------------------------------------------
    | Controllers config
    |--------------------------------------------------------------------------
    |
    | Here you can specify voyager controller settings
    |
    */

    'controllers' => [
        'namespace' => 'Joy\\VoyagerBulkUpdate\\Http\\Controllers',
    ],

    /*
    | The default import disk.
    */
    'disk' => env('VOYAGER_BULK_UPDATE_DISK', null),

    /*
    | The default import readerType.
    | 
    | Supported: "Xlsx", "Csv", "Ods", "Xls",
    |   "Slk", "Xml", "Gnumeric", "Html"
    */
    'readerType' => env('VOYAGER_BULK_UPDATE_READER_TYPE', 'Xlsx'),

    /*
    | The default import writerType.
    | 
    | Supported: "Xlsx", "Csv", "Csv", "Ods", "Xls",
    |   "Slk", "Xml", "Gnumeric", "Html"
    */
    'writerType' => env('VOYAGER_BULK_UPDATE_WRITER_TYPE', 'Xlsx'),

    /*
    | Here you can specify which mimes are allowed to upload
    | 
    | Supported: "xlsx","csv","tsv","ods","xls","slk","xml","gnumeric","html"
    |
    */

    'allowed_mimes' => env('VOYAGER_BULK_UPDATE_ALLOWED_MIMES', 'xlsx,txt,csv,tsv,ods,xls,slk,xml,gnumeric,html'),

    /*
    |--------------------------------------------------------------------------
    | Unique column config
    |--------------------------------------------------------------------------
    |
    | Here you can specify unique column settings
    | Make sure db also has unique index or primary index on that column
    | Leave null for primary key
    |
    */

    'unique_column' => [
        // 'users' => 'email',
        // 'YOUR_DATATYPE_SLUG' => 'MODEL_UNIQUE_KEY',
    ],
];
