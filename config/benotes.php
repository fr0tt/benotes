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

];
