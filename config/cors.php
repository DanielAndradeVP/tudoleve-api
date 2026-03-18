<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    /*
     * Comma-separated list of allowed origins comes from CORS_ALLOWED_ORIGINS.
     *
     * Production: set CORS_ALLOWED_ORIGINS=https://app.example.com,https://admin.example.com
     * Local:      set CORS_ALLOWED_ORIGINS=http://localhost:3000,http://localhost:3001
     *
     * If the variable is absent/empty AND APP_ENV=local, the four standard
     * localhost dev origins are used as a fallback so the stack works
     * out-of-the-box.  In any other environment the list is empty (deny all)
     * to force an explicit, intentional configuration.
     */
    'allowed_origins' => (function (): array {
        $origins = array_values(array_filter(
            array_map('trim', explode(',', env('CORS_ALLOWED_ORIGINS', '')))
        ));

        if (empty($origins) && env('APP_ENV') === 'local') {
            return [
                'http://localhost:3000',
                'http://localhost:3001',
                'http://127.0.0.1:3000',
                'http://127.0.0.1:3001',
            ];
        }

        return $origins;
    })(),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 7200,

    /*
     * Only set to true when this API must send cookies / Sanctum tokens via
     * credentials mode.  Must never be combined with allowed_origins = ['*'].
     */
    'supports_credentials' => env('CORS_SUPPORTS_CREDENTIALS', false),

];
