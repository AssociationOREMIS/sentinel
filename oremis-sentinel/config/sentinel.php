<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Identity Provider base URL
    |--------------------------------------------------------------------------
    |
    | Base URL of the OREMIS Identity Provider (data.oremis.fr).
    | You can override this per environment in your main app .env:
    | SENTINEL_BASE_URL=https://data.oremis.fr
    |
    */

    'base_url' => env('SENTINEL_BASE_URL', 'https://data.oremis.fr'),

    /*
    |--------------------------------------------------------------------------
    | Token validation endpoint
    |--------------------------------------------------------------------------
    |
    | Relative path to validate a token on the Identity Provider.
    |
    */

    'validate_endpoint' => env('SENTINEL_VALIDATE_ENDPOINT', '/api/validate-token'),

    /*
    |--------------------------------------------------------------------------
    | Cache TTL (seconds)
    |--------------------------------------------------------------------------
    |
    | Number of seconds token validation results should be cached
    | in the consumer application.
    |
    */

    'cache_ttl' => env('SENTINEL_CACHE_TTL', 15),
];
