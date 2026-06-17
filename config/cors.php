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

    // المسارات التي سيطبق عليها الـ CORS (تشمل ملف الكوكيز والـ API والـ Login)
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout'],

    'allowed_methods' => ['*'],

    // رابط الـ Frontend الجديد بتاعك على Vercel عشان المتصفح يوافق يكلمه
    'allowed_origins' => [
        'https://frontend-railway-a5x29giaz-graduation-project-2026-nbis.vercel.app',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // ⚠️ دي أهم واحدة.. لازم تكون true عشان الـ Cookies والـ Session بتاعت الـ Auth تشتغل
    'supports_credentials' => true,

];