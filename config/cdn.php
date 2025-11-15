<?php

return [
    /*
    |--------------------------------------------------------------------------
    | CDN Configuration
    |--------------------------------------------------------------------------
    |
    | Configure CDN settings for media files. CDN can significantly improve
    | performance by serving media files from edge locations closer to users.
    |
    */

    'enabled' => env('CDN_ENABLED', false),

    'url' => env('CDN_URL', ''), // e.g., https://cdn.example.com

    /*
    |--------------------------------------------------------------------------
    | CDN Path Prefix
    |--------------------------------------------------------------------------
    |
    | Prefix to add to media paths when using CDN. Leave empty if CDN URL
    | already includes the full path.
    |
    */

    'path_prefix' => env('CDN_PATH_PREFIX', '/storage'),

    /*
    |--------------------------------------------------------------------------
    | Image Optimization
    |--------------------------------------------------------------------------
    |
    | Configure image optimization settings for CDN.
    |
    */

    'image' => [
        'quality' => env('CDN_IMAGE_QUALITY', 85),
        'max_width' => env('CDN_IMAGE_MAX_WIDTH', 1920),
        'max_height' => env('CDN_IMAGE_MAX_HEIGHT', 1080),
        'format' => env('CDN_IMAGE_FORMAT', 'auto'), // auto, webp, jpg, png
    ],

    /*
    |--------------------------------------------------------------------------
    | CDN Domains
    |--------------------------------------------------------------------------
    |
    | List of CDN domains for different regions or purposes.
    |
    */

    'domains' => [
        'default' => env('CDN_URL', ''),
        // Add more domains as needed
        // 'us' => env('CDN_URL_US', ''),
        // 'eu' => env('CDN_URL_EU', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Control
    |--------------------------------------------------------------------------
    |
    | Cache control headers for CDN.
    |
    */

    'cache_control' => [
        'images' => 'public, max-age=31536000', // 1 year
        'documents' => 'public, max-age=2592000', // 30 days
        'default' => 'public, max-age=86400', // 1 day
    ],

];
