<?php

return [
    'title' => env('WEDDING_TITLE', 'Stefano & Valentina'),
    'date' => env('WEDDING_DATE'),
    'location' => env('WEDDING_LOCATION'),
    'site_pin' => env('WEDDING_SITE_PIN'),
    'access_lifetime' => (int) env('WEDDING_ACCESS_LIFETIME', 10080),
    'signed_url_lifetime' => (int) env('WEDDING_SIGNED_URL_LIFETIME', 20),
];
