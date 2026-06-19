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
    */

    // الحل السحري: تغطية جميع المسارات بلا استثناء لمنع أي CORS فجائي
    'paths' => ['*'],

    'allowed_methods' => ['*'],

    // رابط الـ Frontend المعتمد
    'allowed_origins' => [
        'https://frontend-railway-nine.vercel.app',
        'http://localhost:3000'
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // تفعيل الـ Credentials لنقل الكوكيز والـ Sessions
    'supports_credentials' => true,

];