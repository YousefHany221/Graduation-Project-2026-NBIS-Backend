<?php

return [
    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'https://agent-6a3166dc6898fe1a2ec9--vocal-crumble-72c0bb.netlify.app',
    ],

    'allowed_origins_patterns' => [
        '#^https://.*\.netlify\.app$#',  // يسمح لأي netlify domain
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
];