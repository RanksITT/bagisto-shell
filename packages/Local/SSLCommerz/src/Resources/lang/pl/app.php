<?php

return [
    'description' => 'Zapłać przez bKash, Nagad, Rocket, kartą lub bankowością internetową za pośrednictwem SSLCommerz.',
    'title' => 'SSLCommerz',

    'admin' => [
        'system' => [
            'info' => 'Przyjmuj płatności kartą, bankowością mobilną i internetową za pośrednictwem SSLCommerz.',
            'sandbox-info' => 'Wysyłaj płatności do środowiska testowego SSLCommerz. Sklep testowy ma własny identyfikator i hasło.',
            'store-id' => 'Identyfikator sklepu',
            'store-id-info' => 'Identyfikator sklepu wydany przez SSLCommerz dla tego sklepu.',
            'store-password' => 'Hasło sklepu',
            'store-password-info' => 'Hasło API wydane wraz z identyfikatorem sklepu, a nie hasło do logowania w panelu sprzedawcy.',
            'title' => 'SSLCommerz',
        ],
    ],

    'response' => [
        'amount-out-of-range' => 'SSLCommerz przyjmuje płatności od :min do :max. Wartość tego zamówienia to :amount.',
        'cart-not-found' => 'Nie znaleziono koszyka lub jest on nieprawidłowy.',
        'payment-cancelled' => 'Płatność została anulowana.',
        'payment-failed' => 'Płatność nie powiodła się. Spróbuj ponownie.',
        'payment-success' => 'Płatność zakończyła się pomyślnie.',
        'provide-credentials' => 'Podaj prawidłowe dane uwierzytelniające SSLCommerz.',
        'supported-currency-error' => 'Waluta :currency nie jest obsługiwana. Obsługiwane waluty: :supportedCurrencies.',
        'verification-failed' => 'Nie udało się potwierdzić płatności. Jeśli środki zostały pobrane, skontaktuj się z nami, podając numer transakcji :tranId.',
    ],
];
