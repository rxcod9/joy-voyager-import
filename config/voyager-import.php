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
    | Supported: "Xlsx", "Csv", "Csv", "Ods", "Xls",
    |   "Slk", "Xml", "Gnumeric", "Html", "Mpdf", "Dompdf", "Tcpdf"
    */
    'readerType' => env('VOYAGER_IMPORT_READER_TYPE', 'Xlsx'),

    /*
    | The default import writerType.
    | 
    | Supported: "Xlsx", "Csv", "Csv", "Ods", "Xls",
    |   "Slk", "Xml", "Gnumeric", "Html", "Mpdf", "Dompdf", "Tcpdf"
    */
    'writerType' => env('VOYAGER_IMPORT_WRITER_TYPE', 'Xlsx'),

    /*
    | Here you can specify which mimes are allowed to upload
    | 
    | Supported: "xlsx", "csv", "csv", "ods", "xls",
    |   "slk", "xml", "gnumeric", "html", "mpdf", "dompdf", "tcpdf"
    |
    */

    'allowed_mimes' => env('VOYAGER_IMPORT_ALLOWED_MIMES', 'xlsx,csv,csv,ods,xls,slk,xml,gnumeric,html,mpdf,dompdf,tcpdf'),

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
        'default' => null,
        // 'users' => 'email',
        // 'YOUR_DATATYPE_SLUG' => 'MODEL_UNIQUE_KEY',
    ],
];
