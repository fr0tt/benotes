<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Filesystem usage
    |--------------------------------------------------------------------------
    |
    | This option controls if any filesystem will be used or not.
    |
    */

    'use_filesystem' => env('USE_FILESYSTEM', true),

    /*
    |--------------------------------------------------------------------------
    | Curl Timeout
    |--------------------------------------------------------------------------
    |
    | This option controls the maximum number of seconds to allow cURL functions to execute.
    |
    */

    'curl_timeout' => env('CURL_TIMEOUT', 10),

    'run_backup' => env('RUN_BACKUP', false),

    /*
    |--------------------------------------------------------------------------
    | Backup Disk
    |--------------------------------------------------------------------------
    |
    | This option controls which filesystem disk to use.
    |
    */

    'backup_disk' => env('BACKUP_DISK', 'backup'),

    /*
    |--------------------------------------------------------------------------
    | Backup include env
    |--------------------------------------------------------------------------
    |
    | This option controls if backups should include your .env file
    |
    */

    'backup_include_env' => env('BACKUP_INCLUDE_ENV', false),

    /*
    |--------------------------------------------------------------------------
    | Backup Interval
    |--------------------------------------------------------------------------
    |
    | This option controls how often a backup should be created.
    |
    */

    'backup_interval' => env('BACKUP_INTERVAL', '0 1 * * *'),

    'temporary_directory' => storage_path('tmp'),

    'generate_missing_thumbnails' => env('GENERATE_MISSING_THUMBNAILS', false),

    /*
    |--------------------------------------------------------------------------
    | Missing Thumbnail Generation Interval
    |--------------------------------------------------------------------------
    |
    | This option controls how often missing thumbnails should be created.
    | Default is set to every hour at minute 30.
    |
    */

    'thumbnail_filler_interval' => env('THUMBNAIL_FILLER_INTERVAL', '30 * * * *'),

];
