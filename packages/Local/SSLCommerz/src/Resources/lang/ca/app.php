<?php

return [
    'description' => 'Paga amb bKash, Nagad, Rocket, targetes o banca en línia a través de SSLCommerz.',
    'title' => 'SSLCommerz',

    'admin' => [
        'system' => [
            'info' => 'Accepta pagaments amb targeta, banca mòbil i banca en línia a través de SSLCommerz.',
            'sandbox-info' => 'Envia els pagaments a l\'entorn de proves de SSLCommerz. Una botiga de proves té el seu propi ID de botiga i contrasenya.',
            'store-id' => 'ID de la botiga',
            'store-id-info' => 'L\'ID de botiga que SSLCommerz ha emès per a aquesta botiga.',
            'store-password' => 'Contrasenya de la botiga',
            'store-password-info' => 'La contrasenya de l\'API emesa amb l\'ID de botiga, no la contrasenya amb què inicieu sessió al panell de comerciant.',
            'title' => 'SSLCommerz',
        ],
    ],

    'response' => [
        'amount-out-of-range' => 'SSLCommerz accepta pagaments entre :min i :max. Aquesta comanda suma :amount.',
        'cart-not-found' => 'No s\'ha trobat la cistella o no és vàlida.',
        'payment-cancelled' => 'S\'ha cancel·lat el pagament.',
        'payment-failed' => 'El pagament ha fallat. Torneu-ho a provar.',
        'payment-success' => 'El pagament s\'ha completat correctament.',
        'provide-credentials' => 'Proporcioneu credencials de SSLCommerz vàlides.',
        'session-refused' => 'SSLCommerz no ha pogut iniciar el pagament: :reason',
        'supported-currency-error' => 'La moneda :currency no és compatible. Monedes compatibles: :supportedCurrencies.',
        'verification-failed' => 'No s\'ha pogut confirmar el pagament. Si se us ha cobrat, contacteu amb nosaltres indicant la transacció :tranId.',
    ],
];
