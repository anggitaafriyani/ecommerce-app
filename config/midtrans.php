<?php

return [
    'environment' => env('MIDTRANS_ENVIRONMENT', 'sandbox'),
    'server_key' => env('MIDTRANS_SERVER_KEY', ''),
    'signature_secret_key' => env('MIDTRANS_SIGNATURE_SECRET_KEY', ''),
    'client_key' => env('MIDTRANS_CLIENT_KEY', ''),
    'notification_url' => env('MIDTRANS_NOTIFICATION_URL'),
];
