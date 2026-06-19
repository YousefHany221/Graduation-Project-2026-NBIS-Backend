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

    // رابط الـ Frontend المعتمد
    'allowed_origins' => [
        'http://localhost:5173', // الـ Local الصح بتاع Vite
        'http://127.0.0.1:5173', // لضمان تشغيل اللوكال من أي متصفح
        'https://frontend-2-3nqd4phw9-graduation-project-2026-nbis.vercel.app', // دومين فيرسيل الجديد
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // تفعيل الـ Credentials لنقل الكوكيز والـ Sessions
    'supports_credentials' => true,

];