<?php

return [ 
    'live' => [
        'url' => env('SHOPIFY_LIVE_URL'),
        'secret' => env('SHOPIFY_LIVE_SECRET'),
        'onlineLocation' => '',
    ],

    'staging' => [
        'url' => env('SHOPIFY_STAGING_URL'),
        'secret' => env('SHOPIFY_STAGING_SECRET'),
        'onlineLocation' => 'gid://shopify/Location/88834441537',
    ],
    
    "isLive" => env('SHOPIFY_LIVE', 0)

];
