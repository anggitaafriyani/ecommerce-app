<?php

return [
    'shipping_cost' => (int) env('PAWMART_SHIPPING_COST', 15000),
    'reservation_minutes' => (int) env('PAWMART_RESERVATION_MINUTES', 60),
    'admin' => [
        'name' => env('PAWMART_ADMIN_NAME', 'Admin PawMart'),
        'email' => env('PAWMART_ADMIN_EMAIL', 'admin@petshop.com'),
        'password' => env('PAWMART_ADMIN_PASSWORD'),
    ],
];
