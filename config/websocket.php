<?php

return [
    /*
    |--------------------------------------------------------------------------
    | WebSocket Server Configuration
    |--------------------------------------------------------------------------
    |
    | Here you can configure the WebSocket server settings.
    |
    */

    'port' => env('WEBSOCKET_PORT', 6001),

    /*
    |--------------------------------------------------------------------------
    | WebSocket Server Host
    |--------------------------------------------------------------------------
    |
    | This is the host where the WebSocket server will be running.
    | By default, it will use 0.0.0.0 to accept connections from any IP.
    |
    */

    'host' => env('WEBSOCKET_HOST', '0.0.0.0'),

    /*
    |--------------------------------------------------------------------------
    | Maximum Send Buffer Size
    |--------------------------------------------------------------------------
    |
    | This is the maximum size of the send buffer in bytes.
    | Default is 50MB.
    |
    */

    'max_send_buffer' => env('WEBSOCKET_MAX_SEND_BUFFER', 50 * 1024 * 1024),

    /*
    |--------------------------------------------------------------------------
    | Maximum Package Size
    |--------------------------------------------------------------------------
    |
    | This is the maximum size of a single package in bytes.
    | Default is 50MB.
    |
    */

    'max_package_size' => env('WEBSOCKET_MAX_PACKAGE_SIZE', 50 * 1024 * 1024),
]; 