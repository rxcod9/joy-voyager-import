<?php

return [

    /*
     * If enabled for voyager-import package.
     */
    'enabled' => env('VOYAGER_IMPORT_ENABLED', true),

    /*
     * If validation enabled for voyager-import package.
     */
    'validation' => env('VOYAGER_IMPORT_VALIDATION_ENABLED', false),

    /*
    | Here you can specify for which data type slugs import is enabled
    | 
    | Supported: "*", or data type slugs "users", "roles"
    |
    */

    'allowed_slugs' => array_filter(explode(',', env('VOYAGER_IMPORT_ALLOWED_SLUGS', '*'))),

    /*
    | Here you can specify for which data type slugs import is not allowed
    | 
    | Supported: "*", or data type slugs "users", "roles"
    |
    */

    'not_allowed_slugs' => array_filter(explode(',', env('VOYAGER_IMPORT_NOT_ALLOWED_SLUGS', ''))),

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

    /*
    | The default import disk.
    */
    'disk' => env('VOYAGER_IMPORT_DISK', null),

    /*
    | The default import readerType.
    | 
    | Supported: "Xlsx", "Csv", "Ods", "Xls",
    |   "Slk", "Xml", "Gnumeric", "Html"
    */
    'readerType' => env('VOYAGER_IMPORT_READER_TYPE', 'Xlsx'),

    /*
    | The default import writerType.
    | 
    | Supported: "Xlsx", "Csv", "Csv", "Ods", "Xls",
    |   "Slk", "Xml", "Gnumeric", "Html"
    */
    'writerType' => env('VOYAGER_IMPORT_WRITER_TYPE', 'Xlsx'),

    /*
    | Here you can specify which mimes are allowed to upload
    | 
    | Supported: "xlsx","csv","tsv","ods","xls","slk","xml","gnumeric","html"
    |
    */

    'allowed_mimes' => env('VOYAGER_IMPORT_ALLOWED_MIMES', 'xlsx,txt,csv,tsv,ods,xls,slk,xml,gnumeric,html'),

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

    /*
     * If you want to import asynchronously through queue.
     */
    'async' => env('VOYAGER_IMPORT_ASYNC', false),

    /*
     * If you want to import all asynchronously through queue.
     */
    'all_async' => env('VOYAGER_IMPORT_ALL_ASYNC', true),

    /*
     * Configure Notification via options.
     */
    'notification_via' => array_filter(explode(',', env('VOYAGER_IMPORT_NOTIFICATION_VIA', 'mail,database'))),
];
