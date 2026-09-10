<?php

use Local\SSLCommerz\Payment\SSLCommerz;

return [
    'sslcommerz' => [
        'code' => 'sslcommerz',
        'title' => 'SSLCommerz',
        'description' => 'SSLCommerz',
        'class' => SSLCommerz::class,
        'active' => false,
        'sandbox' => true,
        'sort' => 10,
    ],
];
