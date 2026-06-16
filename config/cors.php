<?php

return [
    'paths' => ['api/*'],  // أو '*' لو عايز كل الـ routes

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'https://vocal-crumble-72c0bb.netlify.app/',  // ← حط domain الـ Netlify بتاعك هنا
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,  // حولها لـ true لو بتبعت cookies
];