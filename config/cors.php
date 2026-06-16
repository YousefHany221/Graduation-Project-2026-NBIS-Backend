<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // 📄 حط هنا رابط موقع الفرونت إند الأونلاين بتاعك (بتاع Netlify أو Vercel) من غير / في الآخر
    // مثال: 'https://your-frontend-site.netlify.app'
    'allowed_origins' => [
        'http://127.0.0.1:5173',
        'http://localhost:5173',
        // 👇 ضيف رابط موقعك الأونلاين هنا:
        'https://vocal-crumble-72c0bb.netlify.app',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // ✅ سيب دي true زي ما هي عشان الـ Cookies والـ Tokens تشتغل
    'supports_credentials' => true,

];