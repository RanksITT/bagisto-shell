<?php

return [
    'description' => 'Plătește cu bKash, Nagad, Rocket, card sau internet banking prin SSLCommerz.',
    'title' => 'SSLCommerz',

    'admin' => [
        'system' => [
            'info' => 'Acceptă plăți cu cardul, prin mobile banking și internet banking prin SSLCommerz.',
            'sandbox-info' => 'Trimite plățile către mediul de test SSLCommerz. Un magazin de test are propriul ID și propria parolă.',
            'store-id' => 'ID magazin',
            'store-id-info' => 'ID-ul de magazin emis de SSLCommerz pentru acest magazin.',
            'store-password' => 'Parolă magazin',
            'store-password-info' => 'Parola API emisă odată cu ID-ul de magazin, nu parola cu care te autentifici în panoul de comerciant.',
            'title' => 'SSLCommerz',
        ],
    ],

    'response' => [
        'amount-out-of-range' => 'SSLCommerz acceptă plăți între :min și :max. Această comandă totalizează :amount.',
        'cart-not-found' => 'Coșul nu a fost găsit sau este invalid.',
        'payment-cancelled' => 'Plata a fost anulată.',
        'payment-failed' => 'Plata a eșuat. Te rugăm să încerci din nou.',
        'payment-success' => 'Plata a fost finalizată cu succes.',
        'provide-credentials' => 'Te rugăm să furnizezi date de autentificare SSLCommerz valide.',
        'supported-currency-error' => 'Moneda :currency nu este acceptată. Monede acceptate: :supportedCurrencies.',
        'verification-failed' => 'Plata ta nu a putut fi confirmată. Dacă ai fost taxat, contactează-ne menționând tranzacția :tranId.',
    ],
];
